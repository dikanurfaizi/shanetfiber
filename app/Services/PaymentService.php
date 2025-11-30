<?php

namespace App\Services;

use App\Http\Resources\Pppoe\SecretResource;
use App\Jobs\AddPaymentAfterDueDateJob;
use App\Jobs\AddPaymentBeforeDueDateJob;
use App\Jobs\AddPaymentIpStaticAfterDueDateJob;
use App\Jobs\AddPaymentIpStaticBeforeDueDateJob;
use App\Models\AutoIsolir;
use App\Models\Customer;
use App\Models\Mikrotik;
use App\Models\Order;
use App\Models\Receipt;
use App\Models\WhatsApp\WhatsAppGateway;
use App\Services\Routerboard\IpStaticService;
use App\Services\Routerboard\Ppp\SecretService;
use App\Services\WhatsApp\GowaService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    protected SecretService $secretService;

    protected IpStaticService $ipStaticService;

    private GowaService $messageService;

    public function __construct(SecretService $secretService, IpStaticService $ipStaticService)
    {
        $this->secretService = $secretService;
        $this->ipStaticService = $ipStaticService;
        $this->messageService = app(GowaService::class);
    }

    public function processPaymentUnpaid($order, $billing): void
    {
        $customer = Customer::find($order->customer_id);

        if ($customer->billing_option == 2 && $billing->billing_periode->name === $customer->billing_period) {
            $customer->update([
                'billing_option' => '0',
                'billing_period' => null,
                'member_id' => null,
                'discount' => '0',
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

    public function processPaymentSuccess($order, $billing, $payment_time, string $traceId): Receipt
    {
        $customer = $order->customer ?? Customer::find($order->customer_id);
        $user = auth()->user() ?? $customer->user;
        $now = $payment_time ?? Carbon::now();

        $customerData = $this->mapCustomerData($customer);

        // Validasi bahwa semua data tidak null
        foreach ($customerData as $key => $value) {
            if (is_null($value)) {
                throw new \Exception("Data pelanggan tidak lengkap: {$key} bernilai null");
            }
        }

        $periodeName = $billing->billing_periode->name;
        $nextMonth = Carbon::parse($periodeName)->addMonth()->format('m_Y');
        $router = Mikrotik::find($customerData['mikrotik_id']);
        $autoIsolir = AutoIsolir::where('mikrotik_id', $customerData['mikrotik_id'])->first();

        if ($router && $autoIsolir && $autoIsolir->disabled === 'false' && $router->disabled === 'false') {
            $comment = $autoIsolir->activation_date === 'false'
                ? $autoIsolir->comment_payment
                : $this->generateCommentPayment($autoIsolir, $customerData['activation_date'], $nextMonth, $traceId);

            $this->processMikrotikPayment($customerData, $customer->paket->profile, $router, $autoIsolir, $comment, $traceId);
        }

        [$discount, $priceAfterDiscount, $member] = $this->handleCustomerBilling($customer, $billing);

        $receipt = Receipt::create($this->generateReceiptData($billing, $order, $customer, $user, $now, $discount, $priceAfterDiscount, $member));
        $getReceipt = $receipt->fresh();

        $billing->update([
            'payment_time' => $now,
            'teller_name' => optional($user)->name ?? 'Unknown',
            'status' => 'LS',
            'receipt_id' => $receipt->id,
        ]);

        $getBilling = $billing->fresh();

        $order->update(['payment_time' => $now]);

        if (WhatsAppGateway::where('slug', 'gateway')->value('disabled') == false && $customer->contact) {
            $this->messageService->payment_package($getBilling, $getReceipt, 'payment_gateway', $traceId);
        }

        return $receipt;
    }

    protected function mapCustomerData(Customer $customer): array
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

        return $autoIsolir->comment_unpayment.'_'.$adjustedDate.'_'.$nextMonth;
    }

    protected function processMikrotikPayment(array $data, string $profile, $router, $autoIsolir, string $comment, string $traceId): void
    {
        try {
            if ($data['internet_service'] === 'ppp') {
                $secret = $this->secretService->getSecretById($router, $data['secret_id'], $traceId);
                if ($secret === 'error') {
                    throw new \Exception('Secret not found');
                }

                $currentProfile = SecretResource::collection($secret)->value('profile');

                $job = $currentProfile === $profile
                    ? new AddPaymentBeforeDueDateJob($router, $comment, $data['secret_id'], $traceId)
                    : new AddPaymentAfterDueDateJob($router, $profile, $comment, $data['secret_id'], $data['username'], $traceId);

                dispatch($job);
            } elseif ($data['internet_service'] === 'ip_static') {
                $comment_full = $data['username'].'_'.$comment;
                $address = $this->ipStaticService->getAddressListByIpAddress($router, $data['ip_static'], $traceId);

                if (isset($address[0]['list']) && $address[0]['list'] !== $autoIsolir->address_list_isolir) {
                    dispatch(new AddPaymentIpStaticBeforeDueDateJob($router, $comment, $data['simpleque_id'], $traceId));
                } else {
                    dispatch(new AddPaymentIpStaticAfterDueDateJob(
                        $router,
                        $data['ip_static'],
                        $comment,
                        $autoIsolir->address_list_isolir,
                        $data['simpleque_id'],
                        $comment_full,
                        $traceId
                    ));
                }
            }
        } catch (\Exception $e) {
            Log::error('Mikrotik payment error: '.$e->getMessage());
        }
    }

    protected function handleCustomerBilling(Customer $customer, $billing): array
    {
        $discount = 0;
        $priceAfterDiscount = $customer->paket->price;
        $member = 'Non Member / Tidak Ada';

        if ($customer->billing_option == 1) {
            $discount = optional($customer->member)->discount ?? 0;
            $priceAfterDiscount -= $discount;
            $member = optional($customer->member)->type;
        } elseif ($customer->billing_option == 2 && $billing->billing_periode->name === $customer->billing_period) {
            $discount = optional($customer->member)->discount ?? 0;
            $priceAfterDiscount -= $discount;
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

    protected function generateReceiptData($billing, $order, Customer $customer, $user, $now, $discount, $priceAfterDiscount, $member): array
    {
        return [
            'slug' => $billing->slug,
            'periode' => $billing->billing_periode->name,
            'number' => $this->generateReceiptNumber(),
            'billing_number' => $billing->billing_number,
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_address' => $customer->address,
            'paket_name' => $order->package ?? $customer->paket->name,
            'paket_price' => $customer->paket->price,
            'discount' => $discount,
            'price_after_discount' => $priceAfterDiscount,
            'member_type' => $member,
            'created_name' => $customer->name,
            'teller_name' => optional($user)->name ?? 'Unknown',
            'payment_time' => $now,
            'payment_methode' => $order->payment_name,
            'reference_id' => $order->number,
            'tax' => $billing->tax,
        ];
    }

    protected function generateReceiptNumber(): string
    {
        $last = Receipt::latest('created_at')->value('number');
        $number = $last ? (int) explode('-', $last)[1] + 1 : 1;

        return sprintf('KWT-%05d-%s-%s', $number, Carbon::now()->format('F'), Carbon::now()->format('Y'));
    }

    public function processUpdatePaymentStatus(Order $order, string $status): void
    {
        $order->update(['payment_status' => $status]);
    }
}
