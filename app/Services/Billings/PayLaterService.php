<?php

namespace App\Services\Billings;

use App\Models\Billing\Billing;
use App\Models\Customer;
use App\Models\Mikrotik;
use App\Models\AutoIsolir;
use App\Jobs\AddPaymentBeforeDueDateJob;
use App\Jobs\AddPaymentAfterDueDateJob;
use App\Jobs\AddPaymentIpStaticAfterDueDateJob;
use App\Services\Routerboard\Ppp\SecretService;
use App\Services\Routerboard\IpStaticService;
use App\Http\Resources\Pppoe\SecretResource;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Helpers\LogHelper;
use App\Jobs\WhatsApp\ProcessWhatsappMessageJob;
use Illuminate\Http\Request;
use App\DataTransferObjects\WhatsappJobDto;
use App\Enums\WhatsAppAction;

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
        $startTime = microtime(true);

        // Context awal biar konsisten di semua log
        $context = [
            'billing_id' => $billing->id ?? null,
            'customer_id' => $billing->customer->id ?? null,
            'pay_later_input' => $request->pay_later ?? null,
            'action' => 'post_pay_later'
        ];

        try {
            LogHelper::logInfo(null, $context, 200, 'Starting processPayLater', $traceId);

            // Validasi input
            $request->validate([
                'pay_later' => ['required', 'date', 'after_or_equal:' . now()->format('Y-m-d')],
            ]);

            $customer = $billing->customer;

            // Proses Mikrotik actions
            $this->processMikrotikActions($customer, $request->pay_later, $traceId);

            // Update billing data
            $billing->update([
                'pay_later' => Carbon::parse($request->pay_later),
                'teller_name' => auth()->user()->name ?? ($customer->user->name ?? null),
                'status' => 'PL'
            ]);

            // Kirim notifikasi WhatsApp
            $this->sendWhatsappNotification($billing, $customer, $traceId);

            $executionTime = round(microtime(true) - $startTime, 3);
            LogHelper::logInfo(null, array_merge($context, [
                'execution_seconds' => $executionTime
            ]), 200, 'Pay later successfully processed', $traceId);

            return [
                'success' => true,
                'message' => 'Pay later successfully.',
                'trace_id' => $traceId
            ];
        } catch (\Throwable $e) {
            $executionTime = round(microtime(true) - $startTime, 3);

            LogHelper::logError(null, array_merge($context, [
                'execution_seconds' => $executionTime,
                'exception' => get_class($e),
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage()
            ]), 500, 'Error processing pay later', $traceId);

            return [
                'success' => false,
                'message' => 'Failed to process pay later: ' . $e->getMessage(),
                'trace_id' => $traceId
            ];
        }
    }

    private function processMikrotikActions(Customer $customer, string $payLaterDate, string $traceId): void
    {
        $startTime = microtime(true);

        // Siapkan context untuk semua log
        $context = [
            'customer_id'       => $customer->id ?? null,
            'username'          => $customer->username ?? null,
            'mikrotik_id'       => $customer->mikrotik_id ?? null,
            'internet_service'  => $customer->internet_service ?? null,
            'pay_later_date'    => $payLaterDate,
            'action'            => WhatsAppAction::PAYLATER->value
        ];

        try {
            LogHelper::logInfo(null, $context, 200, 'Starting processMikrotikActions', $traceId);

            $router = Mikrotik::find($customer->mikrotik_id);
            $autoIsolir = AutoIsolir::where('mikrotik_id', $customer->mikrotik_id)->first();

            if (!$router || $router->disabled === 'true' || !$autoIsolir || $autoIsolir->disabled === 'true') {
                $executionTime = round(microtime(true) - $startTime, 3);
                LogHelper::logWarning(null, array_merge($context, [
                    'execution_seconds' => $executionTime,
                    'reason' => 'Router or AutoIsolir not found or disabled'
                ]), 200, 'processMikrotikActions skipped', $traceId);
                return;
            }

            $paylaterDate  = Carbon::parse($payLaterDate);
            $commentPayment = $this->getCommentPayment($autoIsolir, $paylaterDate);

            if ($customer->internet_service === 'ppp') {
                $this->processPppPayment($router, $customer, $commentPayment, $traceId);
            } elseif ($customer->internet_service === 'ip_static') {
                $this->processIpStaticPayment($router, $customer, $autoIsolir, $commentPayment, $traceId);
            }

            $executionTime = round(microtime(true) - $startTime, 3);
            LogHelper::logInfo(null, array_merge($context, [
                'execution_seconds' => $executionTime,
                'comment_payment'   => $commentPayment
            ]), 200, 'processMikrotikActions completed successfully', $traceId);
        } catch (\Throwable $e) {
            $executionTime = round(microtime(true) - $startTime, 3);
            LogHelper::logError(null, array_merge($context, [
                'execution_seconds' => $executionTime,
                'exception'         => get_class($e),
                'error_code'        => $e->getCode(),
                'error_message'     => $e->getMessage()
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
                'error_message' => $e->getMessage()
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

    private function sendWhatsappNotification(Billing $billing, Customer $customer, string $traceId): void
    {
        $context = [
            'customerId' => $billing->customer_id ?? null,
            'billingId'  => $billing->id ?? null,
            'action'     => WhatsAppAction::PAYLATER->value
        ];

        try {
            if ($customer->contact) {
                $jobData = new WhatsappJobDto(
                    customerId: (int)$billing->customer_id,
                    action: WhatsAppAction::PAYLATER->value,
                    traceId: (string)$traceId,
                    billingId: (int)$billing->id,
                    receiptId: null,
                    receiptData: null,
                    startDate: null,
                    endDate: null,
                    orderId: null
                );

                ProcessWhatsappMessageJob::dispatch($jobData)->onQueue('default');
                $message = 'The delivery of the paylater payment information message has been successfully scheduled';

                LogHelper::logInfo(null, $context, 200, $message, $traceId);
            } else {
                $message = 'Customer contact not found';
                LogHelper::logWarning(null, $context, 404, $message, $traceId);
            }
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                array_merge($context, [
                    'errorCode'    => $e->getCode(),
                    'errorMessage' => $e->getMessage()
                ]),
                500,
                'Failed to send WhatsApp notification',
                $traceId
            );
            throw $e;
        }
    }
}
