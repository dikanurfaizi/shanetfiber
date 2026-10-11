<?php

namespace App\Services\Billings;

use App\Enums\WhatsAppAction;
use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Http\Resources\Pppoe\SecretResource;
use App\Jobs\AddPaymentAfterDueDateJob;
use App\Jobs\AddPaymentBeforeDueDateJob;
use App\Jobs\AddPaymentIpStaticAfterDueDateJob;
use App\Models\AutoIsolir;
use App\Models\Billings\Billing;
use App\Models\Customers\Customer;
use App\Models\Mikrotik;
use App\Services\Routerboard\IpStaticService;
use App\Services\Routerboard\Ppp\SecretService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PayLaterService
{
    private SecretService $secretService;
    private IpStaticService $ipStaticService;

    public function __construct(SecretService $secretService, IpStaticService $ipStaticService)
    {
        $this->secretService = $secretService;
        $this->ipStaticService = $ipStaticService;
    }

    public function processPayLater(Request $request, Billing $billing, string $traceId): array
    {
        $timer = new ExecutionTimer();

        $context = [
            'billing_id' => $billing->id,
            'customer_id' => $billing->customer->id ?? null,
            'pay_later_input' => $request->pay_later,
        ];

        LogHelper::logInfo(null, $context, 100, 'START processPayLater', $traceId, $timer->lap());

        try {
            // === Step 1: Validate ===
            $request->validate([
                'pay_later' => ['required', 'date', 'after_or_equal:' . now()->format('Y-m-d')],
            ]);

            // === Step 2: Update Billing ===
            $billing->update([
                'paylater_until' => Carbon::parse($request->pay_later)->endOfDay(),
                'status' => 'PL',
                'updated_by' => auth()->id(),
            ]);

            // === Step 3: Mikrotik Actions (Optional) ===
            if (config('services.shanetfiber.allow_mikrotik_actions')) {
                $this->processMikrotikActions($billing->customer, $request->pay_later, $traceId);
            }

            LogHelper::logInfo(null, $context, 200, 'Pay-later processed successfully', $traceId, $timer->lap());

            return [
                'success' => true,
                'message' => 'Pay later successfully.',
                'trace_id' => $traceId,
            ];
        } catch (\Throwable $e) {
            LogHelper::logError(null, array_merge($context, [
                'exception' => get_class($e),
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]), 500, 'ERROR processing pay-later', $traceId, $timer->lap());

            return [
                'success' => false,
                'message' => 'Failed to process pay later: ' . $e->getMessage(),
                'trace_id' => $traceId,
            ];
        }
    }

    private function processMikrotikActions(Customer $customer, string $payLaterDate, string $traceId): void
    {
        $timer = new ExecutionTimer();

        $context = [
            'customer_id' => $customer->id ?? null,
            'username' => $customer->username ?? null,
            'mikrotik_id' => $customer->mikrotik_id ?? null,
            'internet_service' => $customer->internet_service ?? null,
            'pay_later_date' => $payLaterDate,
            'action' => WhatsAppAction::PAYLATER->value,
        ];

        try {
            $router = Mikrotik::find($customer->mikrotik_id);
            $autoIsolir = AutoIsolir::where('mikrotik_id', $customer->mikrotik_id)->first();

            if (! $router || $router->disabled === 'true' || ! $autoIsolir || $autoIsolir->disabled === 'true') {
                LogHelper::logWarning(null, array_merge($context, [
                    'reason' => 'Router or AutoIsolir not found or disabled',
                    'router_exists' => (bool) $router,
                    'router_disabled' => $router->disabled ?? null,
                    'autoisolir_exists' => (bool) $autoIsolir,
                    'autoisolir_disabled' => $autoIsolir->disabled ?? null,
                ]), 200, 'processMikrotikActions skipped', $traceId, $timer->lap());

                return;
            }

            $paylaterDateFormatted = Carbon::parse($payLaterDate);
            $commentPayment = $this->getCommentPayment($autoIsolir, $paylaterDateFormatted);

            if ($customer->internet_service === 'ppp') {
                $this->processPppPayment($router, $customer, $commentPayment, $traceId);
            } elseif ($customer->internet_service === 'ip_static') {
                $this->processIpStaticPayment($router, $customer, $autoIsolir, $commentPayment, $traceId);
            }

            LogHelper::logInfo(null, array_merge($context, [
                'comment_payment' => $commentPayment,
            ]), 200, 'processMikrotikActions completed successfully', $traceId, $timer->lap());
        } catch (\Throwable $e) {
            LogHelper::logError(null, array_merge($context, [
                'exception' => get_class($e),
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage(),
            ]), 500, 'processMikrotikActions failed', $traceId, $timer->lap());

            throw $e;
        }
    }

    private function getCommentPayment(AutoIsolir $autoIsolir, Carbon $payLaterDate): string
    {
        $formattedDate = $payLaterDate->format('M/d/Y');

        return $autoIsolir->activation_date === 'false'
            ? $formattedDate . '/Paylater'
            : $autoIsolir->comment_unpayment . '_' . $formattedDate . '/Paylater';
    }

    private function processPppPayment(Mikrotik $router, Customer $customer, string $commentPayment, string $traceId): void
    {
        $timer = new ExecutionTimer();

        $context = [
            'router_id' => $router->id ?? null,
            'router_name' => $router->name ?? null,
            'customer_id' => $customer->id ?? null,
            'secret_id' => $customer->secret_id ?? null,
            'username' => $customer->username ?? null,
            'comment_payment' => $commentPayment,
            'action' => WhatsAppAction::PAYLATER->value,
        ];

        try {
            $userSecretId = $this->secretService->getSecretById($router, $customer->secret_id, $traceId);

            if ($userSecretId === 'error') {
                throw new \Exception("Secret ID {$customer->secret_id} not found for user '{$customer->username}' on router {$router->name}.");
            }

            $secretProfiles = SecretResource::collection($userSecretId)->value('profile');
            $subscription = $customer->activeSubscription;
            $customerProfile = $subscription?->package?->profile;

            if (! $customerProfile) {
                throw new \DomainException('Active subscription or package profile not found');
            }

            $context['customer_profile'] = $customerProfile;
            $context['secret_profile'] = $secretProfiles;

            if ($secretProfiles == $customerProfile) {
                dispatch(new AddPaymentBeforeDueDateJob($router, $commentPayment, $customer->secret_id, $traceId))
                    ->onQueue('default');
                $message = 'Dispatched PPP payment (before due date)';
            } else {
                dispatch(new AddPaymentAfterDueDateJob(
                    $router,
                    $customerProfile,
                    $commentPayment,
                    $customer->secret_id,
                    $customer->username,
                    $traceId
                ))->onQueue('default');
                $message = 'Dispatched PPP payment (after due date)';
            }

            LogHelper::logInfo(null, $context, 200, $message, $traceId, $timer->lap());
        } catch (\Throwable $e) {
            LogHelper::logError(null, array_merge($context, [
                'exception' => get_class($e),
                'error_message' => $e->getMessage(),
            ]), 500, 'Process PPP Payment Failed', $traceId, $timer->lap());

            throw new \Exception('Server ' . $router->name . ' offline or error: ' . $e->getMessage());
        }
    }

    private function processIpStaticPayment(
        Mikrotik $router,
        Customer $customer,
        AutoIsolir $autoIsolir,
        string $commentPayment,
        string $traceId
    ): void {
        $timer = new ExecutionTimer();

        $context = [
            'router_id' => $router->id ?? null,
            'router_name' => $router->name ?? null,
            'customer_id' => $customer->id ?? null,
            'username' => $customer->username ?? null,
            'ip_static' => $customer->ip_static ?? null,
            'simple_queue_id' => $customer->simpleque_id ?? null,
            'address_list_isolir' => $autoIsolir->address_list_isolir ?? null,
            'comment_payment' => $commentPayment,
            'action' => WhatsAppAction::PAYLATER->value,
        ];

        try {
            $ipAddress = $customer->ip_static;
            $simplequeId = $customer->simpleque_id;
            $addressListIsolir = $autoIsolir->address_list_isolir;
            $comment = $customer->username . '_' . $commentPayment;

            $addressListCustomer = $this->ipStaticService->getAddressListByIpAddress(
                $router,
                $ipAddress,
                $traceId
            );

            if (isset($addressListCustomer[0]['list']) && $addressListCustomer[0]['list'] == $addressListIsolir) {
                dispatch(new AddPaymentIpStaticAfterDueDateJob(
                    $router,
                    $ipAddress,
                    $simplequeId,
                    $addressListIsolir,
                    $commentPayment,
                    $comment,
                    $traceId
                ))->onQueue('default');

                $message = 'Dispatched IP static payment (after due date)';
            } else {
                dispatch(new AddPaymentBeforeDueDateJob(
                    $router,
                    $simplequeId,
                    $commentPayment,
                    $traceId
                ))->onQueue('default');

                $message = 'Dispatched IP static payment (before due date)';
            }

            LogHelper::logInfo(null, $context, 200, $message, $traceId, $timer->lap());
        } catch (\Throwable $e) {
            LogHelper::logError(null, array_merge($context, [
                'exception' => get_class($e),
                'error_message' => $e->getMessage(),
            ]), 500, 'Process IP Static Payment Failed', $traceId, $timer->lap());

            throw new \Exception('Cannot connect to Server ' . ($router->name ?? '') . '. ' . $e->getMessage());
        }
    }
}
