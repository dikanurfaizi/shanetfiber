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

    public function processPayLater(Request $request, Billing $billing): array
    {
        $traceId = (string) Str::uuid();

        try {
            $request->validate([
                'pay_later' => ['required', 'date', 'after_or_equal:' . now()->format('Y-m-d')],
            ]);

            $customer = $billing->customer;
            $this->processMikrotikActions($customer, $request->pay_later, $traceId);

            $billing->update([
                'pay_later' => Carbon::parse($request->pay_later),
                'teller_name' => auth()->user()->name,
                'status' => 'PL'
            ]);

            $this->sendWhatsappNotification($billing, $customer, $traceId);

            return [
                'success' => true,
                'message' => 'Pay later successfully.',
                'trace_id' => $traceId
            ];
        } catch (\Exception $e) {
            LogHelper::logError(null, ['errorCode' => $e->getCode(), 'errorMessage' => $e->getMessage()], "Error processing pay later", $traceId);

            return [
                'success' => false,
                'message' => 'Failed to process pay later: ' . $e->getMessage(),
                'trace_id' => $traceId
            ];
        }
    }

    private function processMikrotikActions(Customer $customer, string $payLaterDate, string $traceId): void
    {
        $router = Mikrotik::find($customer->mikrotik_id);
        $autoIsolir = AutoIsolir::where('mikrotik_id', $customer->mikrotik_id)->first();

        if (!$router || $router->disabled == 'true' || !$autoIsolir || $autoIsolir->disabled == 'true') {
            return;
        }

        $paylaterDate = Carbon::parse($payLaterDate)->format('d');
        $paylaterMonth = Carbon::parse($payLaterDate)->format('m_Y');
        $commentPayment = $this->getCommentPayment($autoIsolir, $paylaterDate, $paylaterMonth);

        if ($customer->internet_service == 'ppp') {
            $this->processPppPayment($router, $customer, $commentPayment, $traceId);
        } elseif ($customer->internet_service == 'ip_static') {
            $this->processIpStaticPayment($router, $customer, $autoIsolir, $commentPayment, $traceId);
        }
    }

    private function getCommentPayment(AutoIsolir $autoIsolir, string $date, string $month): string
    {
        if ($autoIsolir->activation_date == 'false') {
            return $autoIsolir->comment_payment;
        }

        return $autoIsolir->comment_unpayment . '_' . $date . '_' . $month;
    }

    private function processPppPayment(Mikrotik $router, Customer $customer, string $commentPayment, $traceId): void
    {
        try {
            $userSecretId = $this->secretService->getSecretById($router, $customer->secret_id, $traceId);

            if ($userSecretId == 'error') {
                throw new \Exception($customer->secret_id . '! Cannot find user "' . $customer->username . '" on server ' . $router->name . '.');
            }

            $secretProfiles = SecretResource::collection($userSecretId)->value('profile');
            $customerProfile = $customer->paket->profile;

            if ($secretProfiles == $customerProfile) {
                dispatch(new AddPaymentBeforeDueDateJob($router, $commentPayment, $customer->secret_id, $traceId))
                    ->onQueue('default');
            } else {
                dispatch(new AddPaymentAfterDueDateJob(
                    $router,
                    $customerProfile,
                    $commentPayment,
                    $customer->secret_id,
                    $customer->username,
                    $traceId
                ))->onQueue('default');
            }
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'routerId' => $router->id,
                    'customerId' => $customer->id,
                    'commentPayment' => $commentPayment,
                    'action' => WhatsAppAction::PAYLATER->value,
                    'errorCode' => $e->getCode(),
                    'errorMessage' => $e->getMessage()
                ],
                500,
                'Process Ppp Payment Failed',
                $traceId
            );
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
        try {
            $ipAddress = $customer->ip_static;
            $simplequeId = $customer->simpleque_id;
            $addressListIsolir = $autoIsolir->address_list_isolir;
            $comment = $customer->username . '_' . $commentPayment;

            $addressListCustomer = $this->ipStaticService->getAddressListByIpAddress($router, $ipAddress, $traceId);

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
            } else {
                dispatch(new AddPaymentBeforeDueDateJob($router, $simplequeId, $commentPayment, $traceId))
                    ->onQueue('default');
            }
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'routerId' => $router->id,
                    'customerId' => $customer->id,
                    'autoIsolir' => $autoIsolir->id,
                    'commentPayment' => $commentPayment,
                    'action' => WhatsAppAction::PAYLATER->value,
                    'errorCode' => $e->getCode(),
                    'errorMessage' => $e->getMessage()
                ],
                500,
                'Process Ip Static Payment Failed',
                $traceId
            );
            throw new \Exception('Cannot connect to Server ' . $router->name . '. ' . $e->getMessage());
        }
    }

    private function sendWhatsappNotification(Billing $billing, Customer $customer, string $traceId): void
    {
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
                LogHelper::logInfo(
                    null,
                    [
                        'customerId' => $billing->customer_id,
                        'billingId' => $billing->id,
                        'action' => WhatsAppAction::PAYLATER->value
                    ],
                    200,
                    'The delivery of the paylater payment information message has been successfully scheduled.',
                    $traceId
                );
            } else {
                LogHelper::logWarning(
                    null,
                    [
                        'customerId' => $billing->customer_id,
                        'billingId' => $billing->id,
                        'action' => WhatsAppAction::PAYLATER->value
                    ],
                    200,
                    "Customer contact not found",
                    $traceId
                );
            }
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'customerId' => $billing->customer_id,
                    'billingId' => $billing->id,
                    'action' => WhatsAppAction::PAYLATER->value,
                    'errorCode' => $e->getCode(),
                    'errorMessage' => $e->getMessage()
                ],
                500,
                "Failed to send WhatsApp notification",
                $traceId
            );
            throw $e;
        }
    }
}
