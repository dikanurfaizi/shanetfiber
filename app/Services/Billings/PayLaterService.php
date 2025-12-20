<?php

namespace App\Services\Billings;

use App\Enums\WhatsAppAction;
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
        $start = microtime(true);

        $context = [
            'billing_id' => $billing->id,
            'customer_id' => $billing->customer->id,
            'pay_later_input' => $request->pay_later,
        ];

        LogHelper::logInfo('PayLater', $context, 100, 'START pay-later process', $traceId);

        try {
            // === Step 1: Validate ===
            LogHelper::logInfo('PayLater', [
                'billing_id' => $billing->id,
                'step' => 'validation'
            ], 200, 'Step 1 — Validating input', $traceId);

            $request->validate([
                'pay_later' => ['required', 'date', 'after_or_equal:' . now()->format('Y-m-d')],
            ]);

            // === Step 2: Update Billing ===
            LogHelper::logInfo('PayLater', [
                'billing_id' => $billing->id,
                'step' => 'update_billing',
            ], 200, 'Step 2 — Update billing pay_later', $traceId);

            $billing->update([
                'paylater_until' => Carbon::parse($request->pay_later),
                'status' => 'PL',
                'updated_by' => auth()->id()
            ]);

            // === Step 3: Mikrotik Actions (Optional) ===
            if (config('services.shanetfiber.allow_mikrotik_actions')) {
                LogHelper::logInfo('PayLater', [
                    'billing_id' => $billing->id,
                    'step' => 'mikrotik_actions'
                ], 200, 'Step 3 — Processing Mikrotik actions', $traceId);

                $this->processMikrotikActions($billing->customer, $request->pay_later, $traceId);
            }

            // === FINISH ===
            $execution = round(microtime(true) - $start, 3);

            LogHelper::logInfo('PayLater', array_merge($context, [
                'execution_seconds' => $execution,
            ]), 300, 'Pay-later processed successfully', $traceId);

            return [
                'success' => true,
                'message' => 'Pay later successfully.',
                'trace_id' => $traceId,
            ];
        } catch (\Throwable $e) {

            $execution = round(microtime(true) - $start, 3);

            LogHelper::logError('PayLater', array_merge($context, [
                'execution_seconds' => $execution,
                'exception' => get_class($e),
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]), 500, 'ERROR processing pay-later', $traceId);

            return [
                'success' => false,
                'message' => 'Failed to process pay later: ' . $e->getMessage(),
                'trace_id' => $traceId,
            ];
        }
    }

    private function processMikrotikActions(Customer $customer, string $payLaterDate, string $traceId): void
    {
        $startTime = microtime(true);

        // Siapkan context untuk semua log
        $context = [
            'customer_id' => $customer->id ?? null,
            'username' => $customer->username ?? null,
            'mikrotik_id' => $customer->mikrotik_id ?? null,
            'internet_service' => $customer->internet_service ?? null,
            'pay_later_date' => $payLaterDate,
            'action' => WhatsAppAction::PAYLATER->value,
        ];

        try {
            LogHelper::logInfo(null, $context, 200, 'Starting processMikrotikActions', $traceId);

            $router = Mikrotik::find($customer->mikrotik_id);
            $autoIsolir = AutoIsolir::where('mikrotik_id', $customer->mikrotik_id)->first();

            if (! $router || $router->disabled === 'true' || ! $autoIsolir || $autoIsolir->disabled === 'true') {
                $executionTime = round(microtime(true) - $startTime, 3);
                LogHelper::logWarning(null, array_merge($context, [
                    'execution_seconds' => $executionTime,
                    'reason' => 'Router or AutoIsolir not found or disabled',
                ]), 200, 'processMikrotikActions skipped', $traceId);

                return;
            }

            $paylaterDate = Carbon::parse($payLaterDate);
            $commentPayment = $this->getCommentPayment($autoIsolir, $paylaterDate);

            if ($customer->internet_service === 'ppp') {
                $this->processPppPayment($router, $customer, $commentPayment, $traceId);
            } elseif ($customer->internet_service === 'ip_static') {
                $this->processIpStaticPayment($router, $customer, $autoIsolir, $commentPayment, $traceId);
            }

            $executionTime = round(microtime(true) - $startTime, 3);
            LogHelper::logInfo(null, array_merge($context, [
                'execution_seconds' => $executionTime,
                'comment_payment' => $commentPayment,
            ]), 200, 'processMikrotikActions completed successfully', $traceId);
        } catch (\Throwable $e) {
            $executionTime = round(microtime(true) - $startTime, 3);
            LogHelper::logError(null, array_merge($context, [
                'execution_seconds' => $executionTime,
                'exception' => get_class($e),
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage(),
            ]), 500, 'processMikrotikActions failed', $traceId);

            throw $e;
        }
    }

    private function getCommentPayment(AutoIsolir $autoIsolir, string $payLaterDate): string
    {
        $formattedDate = Carbon::parse($payLaterDate)->format('M/d/Y');

        return $autoIsolir->activation_date === 'false'
            ? $formattedDate . '/Paylater'
            : $autoIsolir->comment_unpayment . '_' . $formattedDate . '/Paylater';
    }

    private function processPppPayment(Mikrotik $router, Customer $customer, string $commentPayment, $traceId): void
    {
        $startTime = microtime(true);
        LogHelper::logInfo(null, [
            'router_id' => $router->id ?? null,
            'customer_id' => $customer->id ?? null,
            'action' => WhatsAppAction::PAYLATER->value,
        ], 200, 'Starting PPP payment process', $traceId);

        try {
            $userSecretId = $this->secretService->getSecretById($router, $customer->secret_id, $traceId);

            if ($userSecretId === 'error') {
                throw new \Exception($customer->secret_id . '! Cannot find user "' . $customer->username . '" on server ' . $router->name . '.');
            }

            $secretProfiles = SecretResource::collection($userSecretId)->value('profile');
            $customerProfile = $customer->package->profile;

            if ($secretProfiles == $customerProfile) {
                dispatch(new AddPaymentBeforeDueDateJob($router, $commentPayment, $customer->secret_id, $traceId))
                    ->onQueue('default');
                $message = 'Successfully dispatched PPP payment (before due date)';
            } else {
                dispatch(new AddPaymentAfterDueDateJob(
                    $router,
                    $customerProfile,
                    $commentPayment,
                    $customer->secret_id,
                    $customer->username,
                    $traceId
                ))->onQueue('default');
                $message = 'Successfully dispatched PPP payment (after due date)';
            }

            $executionTime = round(microtime(true) - $startTime, 3);
            $context = [
                'router_name' => $router->name,
                'secret_id' => $customer->secret_id,
                'username' => $customer->username,
                'customer_profile' => $customerProfile ?? null,
                'comment_payment' => $commentPayment,
                'execution_seconds' => $executionTime,
                'action' => WhatsAppAction::PAYLATER->value,
            ];
            LogHelper::logInfo(null, $context, 200, $message, $traceId);
        } catch (\Throwable $e) {
            $executionTime = round(microtime(true) - $startTime, 3);
            $context = [
                'router_id' => $router->id ?? null,
                'router_name' => $router->name ?? null,
                'customer_id' => $customer->id ?? null,
                'secret_id' => $customer->secret_id ?? null,
                'username' => $customer->username ?? null,
                'comment_payment' => $commentPayment,
                'execution_seconds' => $executionTime,
                'exception' => get_class($e),
                'error_message' => $e->getMessage(),
            ];
            LogHelper::logError(null, $context, 500, 'Process PPP Payment Failed', $traceId);

            throw new \Exception('Server ' . $router->name . ' offline, please check your router. ' . $e->getMessage());
        }
    }

    private function processIpStaticPayment(
        Mikrotik $router,
        Customer $customer,
        AutoIsolir $autoIsolir,
        string $commentPayment,
        $traceId
    ): void {
        $startTime = microtime(true);

        // Log awal: context minimal, hanya info identitas & action
        LogHelper::logInfo(null, [
            'router_id' => $router->id ?? null,
            'customer_id' => $customer->id ?? null,
            'action' => WhatsAppAction::PAYLATER->value,
        ], 200, 'Starting IP Static payment process', $traceId);

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

                $message = 'Successfully dispatched IP static payment (after due date)';
            } else {
                dispatch(new AddPaymentBeforeDueDateJob(
                    $router,
                    $simplequeId,
                    $commentPayment,
                    $traceId
                ))->onQueue('default');

                $message = 'Successfully dispatched IP static payment (before due date)';
            }

            // Context lengkap dibangun di akhir proses
            $executionTime = round(microtime(true) - $startTime, 3);
            $context = [
                'router_id' => $router->id ?? null,
                'router_name' => $router->name ?? null,
                'customer_id' => $customer->id ?? null,
                'username' => $customer->username ?? null,
                'ip_static' => $ipAddress ?? null,
                'simple_queue_id' => $simplequeId ?? null,
                'address_list_isolir' => $addressListIsolir ?? null,
                'comment_payment' => $commentPayment,
                'execution_seconds' => $executionTime,
                'action' => WhatsAppAction::PAYLATER->value,
            ];
            LogHelper::logInfo(null, $context, 200, $message, $traceId);
        } catch (\Throwable $e) {
            // Context lengkap saat error
            $executionTime = round(microtime(true) - $startTime, 3);
            $context = [
                'router_id' => $router->id ?? null,
                'router_name' => $router->name ?? null,
                'customer_id' => $customer->id ?? null,
                'username' => $customer->username ?? null,
                'ip_static' => $customer->ip_static ?? null,
                'simple_queue_id' => $customer->simpleque_id ?? null,
                'address_list_isolir' => $autoIsolir->address_list_isolir ?? null,
                'comment_payment' => $commentPayment,
                'execution_seconds' => $executionTime,
                'exception' => get_class($e),
                'error_message' => $e->getMessage(),
            ];
            LogHelper::logError(null, $context, 500, 'Process IP Static Payment Failed', $traceId);

            throw new \Exception('Cannot connect to Server ' . ($router->name ?? '') . '. ' . $e->getMessage());
        }
    }
}
