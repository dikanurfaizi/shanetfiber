<?php

namespace App\Services\Tripays;

use App\Models\{Customer, Receipt, Order, Mikrotik, AutoIsolir};
use App\Jobs\{AddPaymentBeforeDueDateJob, AddPaymentAfterDueDateJob, AddPaymentIpStaticAfterDueDateJob, WhatsApp\ProcessWhatsappMessageJob};
use App\Services\Routerboard\Ppp\SecretService;
use App\Services\Routerboard\IpStaticService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\Pppoe\SecretResource;
use App\Services\WhatsappMessageService;
use App\Enums\WhatsappAction;
use App\DataTransferObjects\WhatsappJobDto;
use App\Models\Billing\BillingSubscriptionPeriode;
use App\Helpers\LogHelper;

class CallbackPaymentService
{
    protected SecretService $secretService;
    protected IpStaticService $ipStaticService;
    private WhatsappMessageService $messageService;

    public function __construct(SecretService $secretService, IpStaticService $ipStaticService)
    {
        $this->secretService = $secretService;
        $this->ipStaticService = $ipStaticService;
        $this->messageService = app(WhatsappMessageService::class);
    }

    public function processPaymentUnpaid($order, $billing, string $traceId): void
    {
        $customer = Customer::find($order->customer_id);

        LogHelper::logInfo(null, [
            'order_id'    => $order->id,
            'billing_id'  => $billing->id,
            'customer_id' => $customer?->id,
        ], 200, "[Tripay] Start processPaymentUnpaid", $traceId);

        // Reset opsi billing kalau cocok dengan periode
        if ($customer && $customer->billing_option == 2 && $billing->billingSubscription->billingPeriode->name === $customer->billing_period) {
            $customer->update([
                'billing_option' => '0',
                'billing_period' => null,
                'member_id'      => null,
                'discount'       => '0'
            ]);

            LogHelper::logInfo(null, [
                'customer_id'      => $customer->id,
                'billing_option'   => $customer->billing_option,
                'billing_period'   => $customer->billing_period,
                'member_id'        => $customer->member_id,
                'discount'         => $customer->discount,
            ], 200, "[Tripay] Customer billing option reset", $traceId);
        }

        // Hapus receipt
        $receiptNumber = $billing->receipt_id;
        if ($receiptNumber) {
            Receipt::where('id', $receiptNumber)->delete();
            LogHelper::logInfo(null, [
                'receipt_id' => $receiptNumber,
            ], 200, "[Tripay] Receipt deleted", $traceId);
        }

        // Update billing jadi unpaid
        $billing->update([
            'payment_time' => null,
            'teller_name'  => optional($customer->user)->name ?? 'Unknown',
            'status'       => 'BL',
            'receipt_id'   => null,
        ]);

        LogHelper::logInfo(null, [
            'billing_id'   => $billing->id,
            'payment_time' => null,
            'status'       => $billing->status,
        ], 200, "[Tripay] Billing marked as unpaid", $traceId);

        $billSubs = BillingSubscriptionPeriode::find($billing->billing_subscription_id);
        if ($billSubs) {
            LogHelper::logInfo($traceId, [
                'periode_id'   => $billSubs->id,
                'before_status' => $billSubs->status,
                'before_paid'  => $billSubs->is_paid,
            ], 200, "[Tripay][$traceId] BillingSubscriptionPeriode before update");

            $billSubs->update(['is_paid' => false, 'status' => 'unpaid']);

            LogHelper::logInfo($traceId, [
                'periode_id'   => $billSubs->id,
                'after_status' => $billSubs->status,
                'after_paid'   => $billSubs->is_paid,
            ], 200, "[Tripay] BillingSubscriptionPeriode after update");
        }

        LogHelper::logInfo(null, [
            'order_id'   => $order->id,
            'billing_id' => $billing->id,
        ], 200, "[Tripay] End processPaymentUnpaid", $traceId);
    }

    public function processPaymentSuccess($order, $billing, $payment_time = null, string $traceId): Receipt
    {
        try {
            LogHelper::logInfo(null, [
                'order_id'   => $order->id ?? null,
                'billing_id' => $billing->id ?? null,
                'customer_id' => $order->customer_id ?? null,
            ], 200, "[Tripay] Start processPaymentSuccess", $traceId);

            $customer = $order->customer ?? Customer::find($order->customer_id);
            $user     = auth()->user() ?? $customer->user;
            $now      = $payment_time ?? Carbon::now();

            $customerData = $this->mapCustomerData($customer, $traceId);

            // Validasi data
            foreach ($customerData as $key => $value) {
                if (is_null($value)) {
                    LogHelper::logError(null, [
                        'key' => $key,
                        'customer_id' => $customer?->id,
                        'order_id' => $order?->id,
                    ], 500, "[Tripay] Data pelanggan tidak lengkap", $traceId);
                    throw new \Exception("Data pelanggan tidak lengkap: {$key} bernilai null");
                }
            }
            LogHelper::logInfo(null, [
                'customer_id' => $customer->id,
                'user_id'     => $user?->id,
                'validated_keys' => array_keys($customerData),
            ], 200, "[Tripay] Customer data validated", $traceId);

            // Hitung periode & router
            $periodeName = $billing->billingSubscription->billingPeriode->name;
            $nextMonth   = Carbon::parse($periodeName)->addMonth()->format('m_Y');
            $router      = Mikrotik::find($customerData['mikrotik_id']);
            $autoIsolir  = AutoIsolir::where('mikrotik_id', $customerData['mikrotik_id'])->first();

            if ($router && $autoIsolir && $autoIsolir->disabled === 'false' && $router->disabled === 'false') {
                $comment = $autoIsolir->activation_date === 'false'
                    ? $autoIsolir->comment_payment
                    : $this->generateCommentPayment($autoIsolir, $customerData['activation_date'], $nextMonth, $traceId);

                LogHelper::logInfo(null, [
                    'router_id'     => $router->id,
                    'auto_isolir_id' => $autoIsolir->id,
                    'comment'       => $comment,
                ], 200, "[Tripay] Processing Mikrotik payment", $traceId);

                $this->processMikrotikPayment(
                    $customerData,
                    $customer->paket->profile,
                    $router,
                    $autoIsolir,
                    $comment,
                    $traceId
                );
            }

            // Handle billing & buat receipt
            [$discount, $priceAfterDiscount, $member] = $this->handleCustomerBilling($customer, $billing, $traceId);
            $receipt = Receipt::create(
                $this->generateReceiptData($billing, $order, $customer, $user, $now, $discount, $priceAfterDiscount, $member, $traceId)
            );
            $getReceipt = $receipt->fresh();

            $billing->update([
                'payment_time' => $now,
                'teller_name'  => optional($user)->name ?? 'Unknown',
                'status'       => 'LS',
                'receipt_id'   => $receipt->id,
            ]);

            LogHelper::logInfo(null, [
                'billing_id'   => $billing->id,
                'status'       => $billing->status,
                'receipt_id'   => $receipt->id,
            ], 200, "[Tripay] Billing updated", $traceId);

            $order->update(['payment_time' => $now]);
            LogHelper::logInfo(null, [
                'order_id'     => $order->id,
                'payment_time' => $now,
            ], 200, "[Tripay] Order updated", $traceId);

            $billSubs = BillingSubscriptionPeriode::find($billing->billing_subscription_id);
            if ($billSubs) {
                LogHelper::logInfo(null, [
                    'periode_id'   => $billSubs->id,
                    'before_status' => $billSubs->status,
                    'before_paid'  => $billSubs->is_paid,
                ], 200, "[Tripay] BillingSubscriptionPeriode before update", $traceId);

                $billSubs->update(['is_paid' => true, 'status' => 'paid']);

                LogHelper::logInfo(null, [
                    'periode_id'   => $billSubs->id,
                    'after_status' => $billSubs->status,
                    'after_paid'   => $billSubs->is_paid,
                ], 200, "[Tripay] BillingSubscriptionPeriode after update", $traceId);
            }

            // Dispatch WhatsApp
            $whatsappJobData = new WhatsappJobDto(
                customerId: $customer->id,
                action: WhatsappAction::PAYMENT_GATEWAY->value,
                traceId: $traceId,
                billingId: $billing->id,
                receiptId: $getReceipt->id,
                receiptData: null,
                startDate: null,
                endDate: null,
                orderId: null,
                oldPaketName: null,
                oldPaketPrice: null
            );
            ProcessWhatsappMessageJob::dispatch($whatsappJobData)->onQueue('whatsapp_single');
            LogHelper::logInfo(null, [
                'customer_id' => $customer->id,
                'whatsapp_job' => true,
            ], 200, "[Tripay] WhatsApp job dispatched", $traceId);

            LogHelper::logInfo(null, [
                'receipt_id'   => $getReceipt->id,
                'customer_id'  => $customer->id,
            ], 200, "[Tripay] Payment success completed", $traceId);

            return $receipt;
        } catch (\Throwable $e) {
            LogHelper::logError(null, [
                'order_id' => $order->id ?? null,
                'billing_id' => $billing->id ?? null,
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ], 500, "[Tripay] processPaymentSuccess FAILED", $traceId);

            throw $e; // tetap lempar supaya ketangkap error handler Laravel
        }
    }

    protected function mapCustomerData(Customer $customer, string $traceId): array
    {
        $data = [
            'paket_id'        => $customer->paket_id,
            'secret_id'       => $customer->secret_id,
            'username'        => $customer->username,
            'activation_date' => $customer->activation_date
                ? Carbon::parse($customer->activation_date)->format('d')
                : null,
            'mikrotik_id'     => $customer->mikrotik_id,
            'internet_service' => $customer->internet_service,
            // 'ip_static' => $customer->ip_static,
            // 'simpleque_id' => $customer->simpleque_id,
        ];

        LogHelper::logInfo(
            null,
            [
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'mapped_data' => $data,
            ],
            200,
            "[Tripay] Customer data mapped",
            $traceId
        );

        return $data;
    }

    protected function generateCommentPayment($autoIsolir, $activationDate, $nextMonth, string $traceId): string
    {
        $adjustedDate = min($activationDate, Carbon::now()->addMonth()->daysInMonth);

        $comment = $autoIsolir->comment_unpayment . '_' . $adjustedDate . '_' . $nextMonth;

        LogHelper::logInfo(
            null,
            [
                'auto_isolir_id' => $autoIsolir->id ?? null,
                'activation_date' => $activationDate,
                'next_month'     => $nextMonth,
                'generated_comment' => $comment,
            ],
            200,
            "[Tripay] Comment payment generated",
            $traceId
        );

        return $comment;
    }

    protected function processMikrotikPayment(array $data, string $profile, $router, $autoIsolir, string $comment, string $traceId): void
    {
        try {
            LogHelper::logInfo(null, [
                'customer_id'      => $data['customer_id'] ?? null,
                'internet_service' => $data['internet_service'],
                'profile'          => $profile,
                'router_id'        => $router?->id,
                'auto_isolir_id'   => $autoIsolir?->id,
                'comment'          => $comment,
            ], 200, "[Tripay][$traceId] Start processMikrotikPayment", $traceId);

            if ($data['internet_service'] === 'ppp') {
                $secret = $this->secretService->getSecretById($router, $data['secret_id'], $traceId);

                if ($secret === 'error') {
                    LogHelper::logError(null, [
                        'secret_id' => $data['secret_id'],
                        'router_id' => $router?->id,
                    ], 500, "[Tripay][$traceId] Secret not found", $traceId);
                    throw new \Exception("Secret not found");
                }

                $currentProfile = SecretResource::collection($secret)->value('profile');
                LogHelper::logInfo(null, [
                    'secret_id'       => $data['secret_id'],
                    'current_profile' => $currentProfile,
                    'expected_profile' => $profile,
                ], 200, "[Tripay][$traceId] PPP Secret profile check", $traceId);

                $job = $currentProfile === $profile
                    ? new AddPaymentBeforeDueDateJob($router, $comment, $data['secret_id'], $traceId)
                    : new AddPaymentAfterDueDateJob($router, $profile, $comment, $data['secret_id'], $data['username'], $traceId);

                dispatch($job);

                LogHelper::logInfo(null, [
                    'job' => get_class($job),
                    'secret_id' => $data['secret_id'],
                ], 200, "[Tripay][$traceId] PPP Job dispatched", $traceId);
            } elseif ($data['internet_service'] === 'ip_static') {
                $comment_full = $data['username'] . '_' . $comment;
                $address = $this->ipStaticService->getAddressListByIpAddress($router, $data['ip_static'], $traceId);

                LogHelper::logInfo(null, [
                    'ip_static'   => $data['ip_static'],
                    'address_list' => $address[0]['list'] ?? null,
                    'isolir_list' => $autoIsolir->address_list_isolir,
                ], 200, "[Tripay][$traceId] IP Static address check", $traceId);

                if (isset($address[0]['list']) && $address[0]['list'] !== $autoIsolir->address_list_isolir) {
                    $job = new AddPaymentBeforeDueDateJob($router, $data['simpleque_id'], $comment, $traceId);
                } else {
                    $job = new AddPaymentIpStaticAfterDueDateJob(
                        $router,
                        $data['ip_static'],
                        $data['simpleque_id'],
                        $autoIsolir->address_list_isolir,
                        $comment,
                        $comment_full,
                        $traceId
                    );
                }

                dispatch($job);

                LogHelper::logInfo(null, [
                    'job'        => get_class($job),
                    'simpleque_id' => $data['simpleque_id'],
                    'ip_static'  => $data['ip_static'],
                ], 200, "[Tripay][$traceId] IP Static Job dispatched", $traceId);
            }

            LogHelper::logInfo(null, [
                'customer_id' => $data['customer_id'] ?? null,
            ], 200, "[Tripay][$traceId] processMikrotikPayment completed", $traceId);
        } catch (\Throwable $e) {
            LogHelper::logError(null, [
                'customer_id'      => $data['customer_id'] ?? null,
                'internet_service' => $data['internet_service'] ?? null,
                'router_id'        => $router?->id,
                'error'            => $e->getMessage(),
                'trace'            => $e->getTraceAsString(),
            ], 500, "[Tripay][$traceId] processMikrotikPayment FAILED", $traceId);

            throw $e; // supaya error tetap naik ke handler
        }
    }

    protected function handleCustomerBilling(Customer $customer, $billing, string $traceId): array
    {
        LogHelper::logInfo(null, [
            'customer_id'   => $customer->id,
            'billing_id'    => $billing->id,
            'billing_option' => $customer->billing_option,
            'billing_period' => $customer->billing_period,
        ], 200, "[Tripay] Start handleCustomerBilling", $traceId);

        $discount = (int) $billing->discount_amount;
        $priceAfterDiscount = (int) $billing->total_amount;
        $member = 'Non Member / Tidak Ada';

        if ($customer->billing_option == 1) {
            $member = optional($customer->member)->type;
            LogHelper::logInfo(null, [
                'customer_id' => $customer->id,
                'member_type' => $member,
            ], 200, "[Tripay] Customer Member (Option 1)", $traceId);
        } elseif ($customer->billing_option == 2 && $billing->billing_periode->name === $customer->billing_period) {
            $member = optional($customer->member)->type;
            $customer->update([
                'billing_option' => '0',
                'billing_period' => null,
                'member_id'      => null,
                'discount'       => '0',
            ]);
            LogHelper::logInfo(null, [
                'customer_id' => $customer->id,
                'member_type' => $member,
            ], 200, "[Tripay] Customer Member (Option 2) Reset Billing", $traceId);
        } else {
            $customer->update([
                'billing_option' => '0',
                'billing_period' => null,
                'member_id'      => null,
                'discount'       => '0',
            ]);
            LogHelper::logInfo(null, [
                'customer_id' => $customer->id,
            ], 200, "[Tripay] Customer Non-Member, Reset Billing", $traceId);
        }

        LogHelper::logInfo(null, [
            'discount'            => $discount,
            'price_after_discount' => $priceAfterDiscount,
            'member'              => $member,
        ], 200, "[Tripay] End handleCustomerBilling", $traceId);

        return [$discount, $priceAfterDiscount, $member];
    }

    protected function generateReceiptData(
        $billing,
        $order,
        Customer $customer,
        $user,
        $now,
        $discount,
        $priceAfterDiscount,
        $member,
        string $traceId
    ): array {
        $receiptData = [
            'slug'               => $billing->slug,
            'periode'            => $billing->billingSubscription->billingPeriode->name,
            'number'             => $this->generateReceiptNumber($traceId),
            'billing_number'     => $billing->billing_number,
            'customer_id'        => $customer->id,
            'customer_name'      => $customer->name,
            'customer_address'   => $customer->address,
            'paket_name'         => $order->package ?? $customer->paket->name,
            'paket_price'        => $billing->base_price,
            'discount'           => $discount,
            'price_after_discount' => $priceAfterDiscount,
            'member_type'        => $member,
            'created_name'       => $customer->name,
            'teller_name'        => optional($user)->name ?? 'Unknown',
            'payment_time'       => $now,
            'payment_methode'    => $order->payment_name,
            'reference_id'       => $order->number,
            'tax'                => $billing->tax_amount,
        ];

        LogHelper::logInfo(null, $receiptData, 200, "[Tripay] Generate Receipt Data", $traceId);

        return $receiptData;
    }


    protected function generateReceiptNumber(string $traceId): string
    {
        $last = Receipt::latest('created_at')->value('number');
        $number = $last ? (int) explode('-', $last)[1] + 1 : 1;
        $newNumber = sprintf("KWT-%05d-%s-%s", $number, Carbon::now()->format('F'), Carbon::now()->format('Y'));

        LogHelper::logInfo(null, [
            'last_number' => $last,
            'new_number'  => $newNumber,
        ], 200, "[Tripay] Generate Receipt Number", $traceId);

        return $newNumber;
    }


    public function processUpdatePaymentStatus(Order $order, string $status, string $traceId): void
    {
        $oldStatus = $order->payment_status;

        $order->update(['payment_status' => $status]);

        LogHelper::logInfo(null, [
            'order_id'    => $order->id,
            'old_status'  => $oldStatus,
            'new_status'  => $status,
        ], 200, "[Tripay] Update Payment Status", $traceId);
    }
}
