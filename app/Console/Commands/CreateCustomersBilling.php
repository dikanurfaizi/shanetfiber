<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use App\Models\Customers\Customer;
use App\Models\Billings\BillingPeriode;
use App\Models\Billings\BillingSubscriptionPeriode;
use App\Models\Billings\Billing;
use App\Models\Websystem;
use App\Models\SubscriptionDuration;
use App\Helpers\LogHelper;
use App\Helpers\ExecutionTimer;
use App\Models\Customers\CustomerSubscription;
use App\Models\Receipt;
use App\Services\Billings\BillingService;
use Illuminate\Support\Carbon;

class CreateCustomersBilling extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:several-customers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'create billing for several customers';

    protected BillingService $billingService;

    public function __construct(BillingService $billingService)
    {
        parent::__construct();
        $this->billingService = $billingService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $traceId = Str::uuid()->toString();
        $timer   = new ExecutionTimer();

        $period_name = 'August 2025';

        $period = BillingPeriode::where('name', $period_name)->first();

        if (!$period) {
            $this->error("Billing periode {$period_name} not found");
            return 1;
        }

        $websystem = Websystem::where('slug', 'websystem')->first();

        $context = [
            'period_id' => $period->id,
            'period_name' => $period->name,
            'action' => $this->description
        ];
        $totalBillingCreated = 0;

        $customers = Customer::withoutTrashed()
            ->select('id', 'name', 'address', 'paket_id', 'member_id', 'user_id', 'billing_option', 'billing_period', 'activation_date')
            ->whereDoesntHave('billing.billingSubscription', function ($query) use ($period_name) {
                $query->where('billing_periode', $period_name);
            })
            ->with(['billing.billingSubscription', 'member', 'paket', 'user'])
            ->get();

        $paketIds = $customers->pluck('paket_id')->unique()->toArray();
        $subscriptionDurations = SubscriptionDuration::whereIn('paket_id', $paketIds)->get()->keyBy('paket_id');
        $customerIdsToBill = $customers->pluck('id')->unique()->toArray();
        $customersToBill = $customers->whereIn('id', $customerIdsToBill);

        try {
            $createdCount = $this->createBillingsForCustomers(
                $customersToBill,
                $period,
                $websystem,
                $traceId,
                $subscriptionDurations,
                $timer
            );

            if ($createdCount > 0) {
                LogHelper::logInfo(null, array_merge($context, [
                    'created_billings' => $createdCount
                ]), 200, "Billings created", $traceId, $timer->lap());
            }

            $totalBillingCreated += $createdCount;
            $this->info($this->description . 'successfull');
            return 0; // success
        } catch (\Throwable $e) {
            $this->error("Failed to {$this->description} " . $e->getMessage());
            return 1; // error code
        }
    }

    private function createBillingsForCustomers(
        $customers,
        BillingPeriode $billingPeriode,
        $websystem,
        string $traceId,
        $subscriptionDurations,
        ExecutionTimer $timer
    ): int {
        $totalBillingCreated = 0;
        try {
            foreach ($customers as $customer) {
                $customerSubs = CustomerSubscription::where('customer_id', $customer->id)->first();
                $billingSubs = BillingSubscriptionPeriode::where('customer_subscription_id', $customerSubs->id)->first();

                if (!$customerSubs || !$billingSubs) {
                    LogHelper::logWarning(null, [
                        'customerSubs' => $customerSubs,
                        'billingSubs' => $billingSubs
                    ], 404, 'Customer Subscription or Billing Subscription not found', $traceId, $timer->lap());
                    continue;
                }

                // Hitung harga, diskon, dan pajak
                $customerDiscount = $this->billingService->calculateCustomerDiscount($customer, $billingPeriode->name);
                $subDuration = $subscriptionDurations[$customer->paket_id] ?? null;

                $discountPaket = $this->billingService->calculatePackageDiscount($subDuration, $customer->paket->price);
                $discountAmount = $customerDiscount + $discountPaket;
                $priceAfterDiscount = max(0, $customer->paket->price - $discountAmount);

                $customerTax = ($websystem->enable_tax == 'true')
                    ? ceil($priceAfterDiscount * ($websystem->tax_rate / 100))
                    : 0;

                $totalAmount = $priceAfterDiscount + $customerTax;

                // Buat Billing
                $billing = Billing::create([
                    'slug' => str($customer->id . '-' . Str::random(4))->slug(),
                    'billing_number' => str($customer->id . '-' . Str::random(4) . '-' .
                        Carbon::parse($billingPeriode->name)->format('m-Y'))->slug(),
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->name,
                    'customer_address' => $customer->address,
                    'billing_subscription_id' => $billingSubs->id,
                    'base_price' => $customer->paket->price,
                    'discount_amount' => $discountAmount,
                    'subtotal' => $priceAfterDiscount,
                    'total_amount' => $totalAmount,
                    'tax_amount' => $customerTax,
                    'created_name' => auth()->user()->name ?? optional($customer->user)->name,
                    'status' => 'BL',
                    'due_date' => Carbon::parse('15 ' . $billingPeriode->name)->endOfDay(),
                ]);

                LogHelper::logInfo(null, [
                    'customer_id' => $customer->id,
                    'billing_subscription_periode_id' => $billingSubs->id,
                    'amount' => $totalAmount
                ], 201, "Billing created for {$customer->name}.", $traceId, $timer->lap());

                $totalBillingCreated++;

                $activation = Carbon::parse($customer->activation_date);
                $this->createReceiptAndUpdateBilling($customer, $traceId, $activation, $billing, $timer);
            }

            LogHelper::logInfo(null, [
                'total_created' => $totalBillingCreated,
            ], 200, "Finished creating billings for customers.", $traceId, $timer->lap());

            return $totalBillingCreated;
        } catch (\Throwable $e) {
            LogHelper::logError(null, [
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500, "Error occurred while creating billings.", $traceId, $timer->lap());

            throw $e;
        }
    }

    private function createReceiptAndUpdateBilling(Customer $customer, string $traceId, Carbon $activationDate, Billing $billing, ExecutionTimer $timer): void
    {
        LogHelper::logInfo(null, [
            'customer_id' => $customer->id,
            'activation_date' => $activationDate
        ], 200, 'Start createReceiptAndUpdateBilling', $traceId, $timer->lap());

        try {
            LogHelper::logInfo(null, [
                'billing_id' => $billing->id
            ], 200, 'Billing fetched successfully', $traceId, $timer->lap());

            $numberReceipt = $this->generateReceiptNumber($traceId, $timer);
            LogHelper::logInfo(null, [
                'number_receipt' => $numberReceipt
            ], 200, 'Generated receipt number', $traceId, $timer->lap());

            $receipt = Receipt::create([
                'slug' => str('inv-' . $customer->id . '-' . Str::random(4))->slug(),
                'periode' => $billing->billingSubscription->billing_periode,
                'number' => str(
                    sprintf("%05d", $numberReceipt) . '-KWT-' . Carbon::now()->format('F') . '-' . Carbon::now()->format('Y')
                ),
                'billing_number' => $billing->billing_number,
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'customer_address' => $customer->address,
                'paket_name' => $customer->paket->name,
                'paket_price' => $customer->paket->price * ((100 - $customer->discount) / 100),
                'payment_methode' => 'cash',
                'created_name' => auth()->user()->name ?? 'System',
                'teller_name' => auth()->user()->name ?? 'System',
                'payment_time' => $activationDate,
            ]);
            LogHelper::logInfo(null, [
                'receipt_id' => $receipt->id
            ], 200, 'Receipt created successfully', $traceId, $timer->lap());

            $billing->update([
                'payment_time' => $activationDate,
                'teller_name' => auth()->user()->name ?? 'System',
                'status' => 'LS',
                'receipt_id' => $receipt->id,
            ]);
            LogHelper::logInfo(null, [
                'billing_id' => $billing->id,
                'receipt_id' => $receipt->id
            ], 200, 'Billing updated successfully', $traceId, $timer->lap());

            $billing->billingSubscription->update([
                'is_paid' => true,
                'status' => 'paid',
                'paid_at' => Carbon::today()
            ]);
            LogHelper::logInfo(null, [
                'billing_subscription_id' => $billing->billingSubscription->id
            ], 200, 'Billing subscription updated successfully', $traceId, $timer->lap());

            LogHelper::logInfo(null, [
                'receipt_id' => $receipt->id,
                'billing_id' => $billing->id,
                'billing_subscription_id' => $billing->billingSubscription->id
            ], 200, 'Finished createReceiptAndUpdateBilling', $traceId, $timer->lap());
        } catch (\Throwable $e) {
            LogHelper::logError(null, [
                'exception' => [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ],
                'customer_id' => $customer->id
            ], 500, 'Failed to create receipt and update billing', $traceId, $timer->lap());
            throw $e;
        }
    }

    private function generateReceiptNumber(string $traceId, ExecutionTimer $timer): string
    {
        try {
            $lastReceipt = Receipt::latest('created_at')->value('number');

            LogHelper::logInfo(null, [
                'last_receipt_number' => $lastReceipt
            ], 200, 'Generating new receipt number', $traceId, $timer->lap());

            if ($lastReceipt && str_starts_with($lastReceipt, 'KWT-')) {
                $parts = explode('-', $lastReceipt);
                $number = (int)$parts[1] + 1;
            } else {
                $number = 1;
            }

            $newNumber = 'KWT-' . sprintf("%05d", $number) . '-' .
                Carbon::now()->format('F') . '-' . Carbon::now()->format('Y');

            LogHelper::logInfo(null, [
                'generated_receipt_number' => $newNumber
            ], 200, 'Receipt number generated successfully', $traceId, $timer->lap());

            return $newNumber;
        } catch (\Throwable $e) {
            LogHelper::logError(null, [
                'exception' => get_class($e),
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500, 'Failed to generate receipt number', $traceId, $timer->lap());

            // fallback kalau gagal
            return 'KWT-ERROR-' . Carbon::now()->timestamp;
        }
    }
}
