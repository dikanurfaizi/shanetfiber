<?php

namespace App\Services\Customers;

use App\Helpers\ExecutionTimer;
use App\Models\Customers\Customer;
use App\Models\Mikrotik;
use App\Models\Packages\Package;
use App\Models\Billings\Billing;
use App\Models\Billings\BillingPayment;
use App\Models\Customers\CustomerSubscription;
use App\Services\Receipts\CreateReceiptService;
use App\Services\Receipts\ReferenceIdGeneratorService;
use App\Helpers\LogHelper;
use App\Models\Packages\PppProfile;
use App\Services\Mikrotik\MikrotikActivation;
use Illuminate\Support\Facades\DB;

class CustomerActivationService
{
    public function __construct(
        private CreateReceiptService $createReceiptService,
        private ReferenceIdGeneratorService $referenceIdGeneratorService,
        private MikrotikActivation $mikrotikActivation
    ) {}

    public function activateAllCustomers(int $mikrotikId, string $traceId): array
    {
        $timer = new ExecutionTimer();
        $server = Mikrotik::where('id', $mikrotikId)->value('name');

        if (!$server) {
            $error = "Server Mikrotik tidak ditemukan dengan ID {$mikrotikId}.";
            LogHelper::logError(null, ['mikrotik_id' => $mikrotikId], 404, $error, $traceId, $timer->lap());

            return [
                'error'    => $error,
                'trace_id' => $traceId,
            ];
        }

        $customers = Customer::where('mikrotik_id', $mikrotikId)->get();

        if ($customers->isEmpty()) {
            $error = "Tidak ada customer yang terhubung ke server {$server}.";
            LogHelper::logError(null, ['server' => $server], 404, $error, $traceId, $timer->lap());

            return [
                'error'    => $error,
                'trace_id' => $traceId,
            ];
        }

        try {
            foreach ($customers as $customer) {
                $this->mikrotikActivation->handle($customer, $customer->activeSubscription, $traceId);
            }

            LogHelper::logInfo(null, [
                'count'  => count($customers),
                'server' => $server,
            ], 200, 'Successfully activated all customers', $traceId, $timer->lap());

            return [
                'success'  => 'Activated ' . count($customers) . ' customers on server ' . $server . '.',
                'count'    => count($customers),
                'server'   => $server,
                'trace_id' => $traceId,
            ];
        } catch (\Throwable $e) {
            $this->logException($e, 'Failed to activate all customers', ['mikrotik_id' => $mikrotikId], $traceId, $timer);

            return [
                'error'    => "Terjadi kesalahan saat aktivasi: " . $e->getMessage(),
                'trace_id' => $traceId,
            ];
        }
    }

    public function activateCustomer(Customer $customer, string $traceId): array
    {
        $timer = new ExecutionTimer();
        $activationDate = now();
        $paymentMethod = 'cash';

        try {
            $allResult = DB::transaction(function () use ($customer, $activationDate, $traceId, $paymentMethod, $timer) {
                /** -----------------------------------------
                 * 1. Ambil subscription aktif customer
                 * ----------------------------------------- */
                $subscription = CustomerSubscription::query()
                    ->with(['customer', 'package'])
                    ->where('customer_id', $customer->id)
                    ->where('status', 'active')
                    ->lockForUpdate()
                    ->firstOrFail();

                /** -----------------------------------------
                 * 2. Ambil billing terkait subscription
                 * ----------------------------------------- */
                $billing = Billing::query()
                    ->with(['billingSubscription.billingPeriode'])
                    ->whereHas(
                        'billingSubscription',
                        fn($q) => $q->where('customer_subscription_id', $subscription->id)
                    )
                    ->lockForUpdate()
                    ->latest()
                    ->firstOrFail();

                /** -----------------------------------------
                 * 3. Update billing (PAID)
                 * ----------------------------------------- */
                $billing->update([
                    'status'     => 'LS',
                    'updated_by' => auth()->id(),
                ]);

                /** -----------------------------------------
                 * 4. Insert billing payment
                 * ----------------------------------------- */
                $billingPayment = BillingPayment::create([
                    'billing_id'      => $billing->id,
                    'amount_paid'     => $billing->total_amount,
                    'payment_method'  => $paymentMethod,
                    'reference_id'    => $this->referenceIdGeneratorService->generate('ACT'),
                    'paid_by_user_id' => auth()->id(),
                    'payment_time'    => $activationDate,
                ]);

                /** -----------------------------------------
                 * 5. Buat receipt
                 * ----------------------------------------- */
                $result = $this->createReceiptService
                    ->createReceipt(
                        $subscription,
                        $billing,
                        $billingPayment,
                        ['payment_method' => $paymentMethod],
                        $traceId
                    );

                /** -----------------------------------------
                 * 6. Update customer
                 * ----------------------------------------- */
                $customer->update([
                    'activation_date' => $activationDate,
                    'activation'      => true,
                    'is_active'        => true,
                ]);

                $customer->refresh();
                $billing->refresh();

                LogHelper::logInfo(
                    null,
                    [
                        'customerId'       => $customer->id,
                        'customerName'     => $customer->name,
                        'billingId'        => $billing->id,
                        'billingPaymentId' => $billingPayment->id,
                        'receiptId'        => $result['receipt']->id,
                    ],
                    200,
                    "Customer, Billing, BillingPayment, Receipt Has been updated",
                    $traceId,
                    $timer->lap()
                );

                return [
                    'customer'       => $customer,
                    'billing'        => $billing,
                    'billingPayment' => $billingPayment,
                    'receipt'        => $result['receipt'],
                    'subscription'   => $subscription,
                    'membership'     => $result['membership'],
                ];
            });

            $pppProfile = PppProfile::where('profile_name', $allResult['subscription']->package->profile ?? null)->first();

            return [
                'success'        => true,
                'customer'       => $allResult['customer'],
                'billing'        => $allResult['billing'],
                'billingPayment' => $allResult['billingPayment'],
                'receipt'        => $allResult['receipt'],
                'traceId'        => $traceId,
                'subscription'   => $allResult['subscription'],
                'membership'     => $allResult['membership'],
                'pppProfile'     => $pppProfile,
            ];
        } catch (\Throwable $e) {
            $this->logException($e, 'Customer activation failed', ['customer_id' => $customer->id], $traceId, $timer);
            throw $e;
        }
    }

    /**
     * Reusable helper method for exception logging with timer.
     */
    private function logException(\Throwable $e, string $message, array $context, string $traceId, ?ExecutionTimer $timer = null): void
    {
        $payload = array_merge([
            'exception' => [
                'message' => $e->getMessage(),
                'code'    => $e->getCode(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ],
        ], $context);

        LogHelper::logError(null, $payload, 500, $message, $traceId, $timer?->lap());
    }

    /**
     * Memproses billing sebagai 'Not Active Period' (No Usage):
     * 1. Update DB (Billing: VD, BillingSubscription: expired, CustomerSubscription: paused)
     * 2. Ambil data Package/PppProfile 'Expire' untuk dikirim ke Event
     */
    public function processNotActivePeriod(Billing $billing, string $traceId): array
    {
        $timer = new ExecutionTimer();
        $userId = auth()->id();

        // Eager load relasi subscription agar aman dari N+1 query
        $billing->loadMissing(['billingSubscription.customerSubscription']);

        try {
            // 1. Eksekusi perubahan Database dalam DB Transaction
            DB::transaction(function () use ($billing, $userId, $traceId, $timer) {
                $billing->update([
                    'updated_by' => $userId,
                    'status'     => 'VD',
                    'updated_at' => now(),
                ]);

                $billingSubscription = $billing->billingSubscription;

                if ($billingSubscription) {
                    $billingSubscription->update([
                        'updated_by' => $userId,
                        'status'     => 'expired',
                    ]);

                    $billingSubscription->customerSubscription?->update([
                        'status'        => 'paused',
                        'terminated_at' => now(),
                    ]);
                }

                LogHelper::logInfo(null, [
                    'billing_id' => $billing->id,
                    'status'     => 'VD',
                    'updated_by' => $userId,
                ], 200, 'Billing processed as no usage (Not Active Period).', $traceId, $timer->lap());
            });

            // 2. Ambil Package & PppProfile 'Expire' dari DB
            $expirePackage = Package::where('name', 'Expire')->first();
            $pppProfile = null;

            if ($expirePackage && $expirePackage->profile) {
                $pppProfile = PppProfile::where('profile_name', $expirePackage->profile)->first();
            }

            return [
                'success'    => true,
                'package'    => $expirePackage,
                'pppProfile' => $pppProfile,
                'message'    => 'Successfully processed not active period',
            ];
        } catch (\Throwable $e) {
            $this->logException($e, 'Failed to process not active period billing', [
                'billing_id' => $billing->id,
            ], $traceId, $timer);

            throw $e;
        }
    }
}
