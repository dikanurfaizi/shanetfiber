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

        if ($customer->billing_option == 2 && $billing->billingSubscription->billingPeriode->name === $customer->billing_period) {
            $customer->update([
                'billing_option' => '0',
                'billing_period' => null,
                'member_id' => null,
                'discount' => '0'
            ]);
        }

        $receiptNumber = $billing->receipt_id;
        Receipt::where('id', $receiptNumber)->delete();

        $billing->update([
            'payment_time' => null,
            'teller_name' => optional($customer->user)->name ?? 'Unknown',
            'status' => 'BL',
            'receipt_id' => null,
        ]);
    }

    public function processPaymentSuccess($order, $billing, $payment_time = null, string $traceId): Receipt
    {
        $customer = $order->customer ?? Customer::find($order->customer_id);
        $user = auth()->user() ?? $customer->user;
        $now = $payment_time ?? Carbon::now();

        $customerData = $this->mapCustomerData($customer, $traceId);

        //Validasi bahwa semua data tidak null
        foreach ($customerData as $key => $value) {
            if (is_null($value)) {
                throw new \Exception("Data pelanggan tidak lengkap: {$key} bernilai null");
            }
        }

        $periodeName = $billing->billingSubscription->billingPeriode->name;
        $nextMonth = Carbon::parse($periodeName)->addMonth()->format('m_Y');
        $router = Mikrotik::find($customerData['mikrotik_id']);
        $autoIsolir = AutoIsolir::where('mikrotik_id', $customerData['mikrotik_id'])->first();

        if ($router && $autoIsolir && $autoIsolir->disabled === 'false' && $router->disabled === 'false') {
            $comment = $autoIsolir->activation_date === 'false'
                ? $autoIsolir->comment_payment
                : $this->generateCommentPayment($autoIsolir, $customerData['activation_date'], $nextMonth, $traceId);

            $this->processMikrotikPayment($customerData, $customer->paket->profile, $router, $autoIsolir, $comment, $traceId);
        }

        [$discount, $priceAfterDiscount, $member] = $this->handleCustomerBilling($customer, $billing, $traceId);

        $receipt = Receipt::create($this->generateReceiptData($billing, $order, $customer, $user, $now, $discount, $priceAfterDiscount, $member, $traceId));
        $getReceipt = $receipt->fresh();

        $billing->update([
            'payment_time' => $now,
            'teller_name' => optional($user)->name ?? 'Unknown',
            'status' => 'LS',
            'receipt_id' => $receipt->id,
        ]);

        $getBilling = $billing->fresh();
        LogHelper::logInfo(null, [
            'billing_id'   => $getBilling->id,
            'customer_id'  => $getBilling->customer_id,
            'total_amount' => $getBilling->total_amount,
            'status'       => $getBilling->status ?? null,
        ], 200, "[Tripay] Billing Details");

        $order->update(['payment_time' => $now]);
        LogHelper::logInfo(null, [
            'order_id'     => $order->id,
            'payment_time' => $now,
        ], 200, "[Tripay] Order Updated with Payment Time");

        $billSubs = BillingSubscriptionPeriode::where('billing_subscription_id', $billing->billing_subscription_id)->first();
        LogHelper::logInfo(null, [
            'billing_subscription_id' => $billing->billing_subscription_id,
            'periode_id'              => $billSubs?->id,
            'before_status'           => $billSubs?->status,
            'before_is_paid'          => $billSubs?->is_paid,
        ], 200, "[Tripay] BillingSubscriptionPeriode Before Update");

        $billSubs->update(['is_paid' => true, 'status' => 'paid']);
        LogHelper::logInfo(null, [
            'periode_id'  => $billSubs->id,
            'after_status' => $billSubs->status,
            'after_is_paid' => $billSubs->is_paid,
        ], 200, "[Tripay] BillingSubscriptionPeriode After Update");

        $whatsappJobData = new WhatsappJobDto(
            customerId: $customer->id,
            action: WhatsappAction::PAYMENT_GATEWAY->value,
            traceId: $traceId,
            billingId: $getBilling->id,
            receiptId: $getReceipt->id,
            receiptData: null,
            startDate: null,
            endDate: null,
            orderId: null,
            oldPaketName: null,
            oldPaketPrice: null
        );

        ProcessWhatsappMessageJob::dispatch($whatsappJobData)->onQueue('whatsapp_single');

        return $receipt;
    }


    protected function mapCustomerData(Customer $customer, string $traceId): array
    {
        return [
            'paket_id' => $customer->paket_id,
            'secret_id' => $customer->secret_id,
            'username' => $customer->username,
            'activation_date' => Carbon::parse($customer->activation_date)->format('d'),
            'mikrotik_id' => $customer->mikrotik_id,
            'internet_service' => $customer->internet_service,
            // 'ip_static' => $customer->ip_static,
            // 'simpleque_id' => $customer->simpleque_id,
        ];
    }

    protected function generateCommentPayment($autoIsolir, $activationDate, $nextMonth, string $traceId): string
    {
        $adjustedDate = min($activationDate, Carbon::now()->addMonth()->daysInMonth);
        return $autoIsolir->comment_unpayment . '_' . $adjustedDate . '_' . $nextMonth;
    }

    protected function processMikrotikPayment(array $data, string $profile, $router, $autoIsolir, string $comment, string $traceId): void
    {
        try {
            if ($data['internet_service'] === 'ppp') {
                $secret = $this->secretService->getSecretById($router, $data['secret_id'], $traceId);
                if ($secret === 'error') throw new \Exception("Secret not found");

                $currentProfile = SecretResource::collection($secret)->value('profile');

                $job = $currentProfile === $profile
                    ? new AddPaymentBeforeDueDateJob($router, $comment, $data['secret_id'], $traceId)
                    : new AddPaymentAfterDueDateJob($router, $profile, $comment, $data['secret_id'], $data['username'], $traceId);

                dispatch($job);
            } elseif ($data['internet_service'] === 'ip_static') {
                $comment_full = $data['username'] . '_' . $comment;
                $address = $this->ipStaticService->getAddressListByIpAddress($router, $data['ip_static'], $traceId);

                if (isset($address[0]['list']) && $address[0]['list'] !== $autoIsolir->address_list_isolir) {
                    dispatch(new AddPaymentBeforeDueDateJob($router, $data['simpleque_id'], $comment, $traceId));
                } else {
                    dispatch(new AddPaymentIpStaticAfterDueDateJob(
                        $router,
                        $data['ip_static'],
                        $data['simpleque_id'],
                        $autoIsolir->address_list_isolir,
                        $comment,
                        $comment_full,
                        $traceId
                    ));
                }
            }
        } catch (\Exception $e) {
            Log::error('Mikrotik payment error: ' . $e->getMessage());
        }
    }

    protected function handleCustomerBilling(Customer $customer, $billing, string $traceId): array
    {
        $discount = (int) $billing->discount_amount;
        $priceAfterDiscount = (int) $billing->total_amount;
        $member = 'Non Member / Tidak Ada';

        if ($customer->billing_option == 1) {
            $member = optional($customer->member)->type;
        } elseif ($customer->billing_option == 2 && $billing->billing_periode->name === $customer->billing_period) {
            $member = optional($customer->member)->type;
            $customer->update([
                'billing_option' => '0',
                'billing_period' => null,
                'member_id' => null,
                'discount' => '0',
            ]);
        } else {
            $customer->update([
                'billing_option' => '0',
                'billing_period' => null,
                'member_id' => null,
                'discount' => '0',
            ]);
        }

        return [$discount, $priceAfterDiscount, $member];
    }

    protected function generateReceiptData($billing, $order, Customer $customer, $user, $now, $discount, $priceAfterDiscount, $member, string $traceId): array
    {
        return [
            'slug' => $billing->slug,
            'periode' => $billing->billingSubscription->billingPeriode->name,
            'number' => $this->generateReceiptNumber($traceId),
            'billing_number' => $billing->billing_number,
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_address' => $customer->address,
            'paket_name' => $order->package ?? $customer->paket->name,
            'paket_price' => $billing->base_price,
            'discount' => $discount,
            'price_after_discount' => $priceAfterDiscount,
            'member_type' => $member,
            'created_name' => $customer->name,
            'teller_name' => optional($user)->name ?? 'Unknown',
            'payment_time' => $now,
            'payment_methode' => $order->payment_name,
            'reference_id' => $order->number,
            'tax' => $billing->tax_amount,
        ];
    }

    protected function generateReceiptNumber(string $traceId): string
    {
        $last = Receipt::latest('created_at')->value('number');
        $number = $last ? (int) explode('-', $last)[1] + 1 : 1;
        return sprintf("KWT-%05d-%s-%s", $number, Carbon::now()->format('F'), Carbon::now()->format('Y'));
    }

    public function processUpdatePaymentStatus(Order $order, string $status, string $traceId): void
    {
        $order->update(['payment_status' => $status]);
    }
}
