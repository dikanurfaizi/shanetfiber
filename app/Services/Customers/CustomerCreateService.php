<?php

namespace App\Services\Customers;

use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Helpers\FormatReceiver;
use App\Models\Customers\Customer;
use App\Models\Customers\CustomerSubscription;
use App\Models\Customers\CustomerAddon;
use App\Models\Billings\BillingPeriode;
use App\Models\Billings\BillingSubscriptionPeriode;
use App\Models\Membership\Membership;
use App\Models\Membership\CustomerMembership;
use App\Models\Packages\PackageDuration;
use App\Models\Websystem;
use App\Models\Customers\CustomerOnt;
use App\Services\Billings\BillingCreateService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class CustomerCreateService
{
    private BillingCreateService $billingCreateService;

    public function __construct(
        BillingCreateService $billingCreateService
    ) {
        $this->billingCreateService = $billingCreateService;
    }

    public function createCustomer(array $data, string $traceId): Customer
    {
        $timer = new ExecutionTimer();
        LogHelper::logInfo(
            null,
            [],
            201,
            'Customer created successfully',
            $traceId,
            $timer->lap()
        );

        return DB::transaction(function () use ($data, $traceId, &$timer) {

            /** ----------------------------------------------------
             * 1. Password Fallback
             * ---------------------------------------------------- */
            if (empty($data['password']) || trim($data['password']) === '') {
                $data['password'] = '12345678'; // default
            }


            /** ----------------------------------------------------
             * 2. Create Customer
             * ---------------------------------------------------- */
            $customerData = $this->prepareCustomerData($data, $traceId);
            $customer = Customer::create($customerData);


            /** ----------------------------------------------------
             * 3. Generate Customer ID
             * ---------------------------------------------------- */
            $websystem = Websystem::where('slug', 'websystem')->firstOrFail();

            $longCode = sprintf('%%0%dd', $websystem->long_customer_code);

            $customerId = sprintf(
                '%s%02d-%s',
                $websystem->customer_code,
                $data['mikrotik_id'],
                sprintf($longCode, $customer->id)
            );

            $customer->update(['customer_id' => $customerId]);


            /** ----------------------------------------------------
             * 4. Create ONT
             * ---------------------------------------------------- */
            CustomerOnt::create(['customer_id' => $customer->id]);


            /** ----------------------------------------------------
             * 4.5 Create Customer Membership (if member selected)
             * ---------------------------------------------------- */
            if (!empty($data['member_id'])) {

                [$startDate, $endDate] = $this->resolveMembershipDates($data);

                CustomerMembership::create([
                    'customer_id'   => $customer->id,
                    'membership_id' => $data['member_id'],
                    'start_date'    => $startDate,
                    'end_date'      => $endDate,
                    'is_active'     => 1,
                ]);
            }


            /** ----------------------------------------------------
             * 5. Fetch Duration + Package (Eager Loaded)
             * ---------------------------------------------------- */
            $packageDuration = PackageDuration::with(['duration', 'package'])
                ->findOrFail($data['package_duration_id']);

            $package  = $packageDuration->package;
            $duration = $packageDuration->duration;


            /** ----------------------------------------------------
             * 6. Calculate Pricing (💡 Diperbarui sesuai Logika Prorate/Full Cost Step 3)
             * ---------------------------------------------------- */
            $months = $duration->duration_months;

            // Ambil potongan nominal diskon membership yang sudah dihitung valid di Step 3
            // Jika tidak ada diskon, nilainya otomatis 0
            $discountNominalStep3 = $data['discount_nominal'] ?? 0;

            $discountType  = 'none';
            $discountValue = 0;

            // Jika ada diskon dari keanggotaan/membership, catat tipenya
            if ($discountNominalStep3 > 0) {
                // Sifat data mengikuti kalkulasi asal di database
                $discountType  = 'amount';
                $discountValue = $discountNominalStep3;
            }

            // Jalankan Engine Kalkulasi Ulang Back-End Terproteksi
            $now = Carbon::now();
            $currentDay = $now->day;
            $totalDaysInMonth = $now->daysInMonth;

            // Sisa hari aktif pendaftaran reguler
            $activeDays = ($totalDaysInMonth - $currentDay) + 1;

            // Harga dasar setelah dipotong nominal diskon keanggotaan
            $basePriceAfterDiscount = max(0, $package->price - $discountNominalStep3);

            // Tentukan harga final langganan berdasarkan keputusan 'is_prorate'
            if (isset($data['is_prorate']) && $data['is_prorate'] == '1' && $currentDay > 15) {
                // Rumus Prorate murni proporsional sisa hari
                $exactProratePrice = ($activeDays / $totalDaysInMonth) * $basePriceAfterDiscount;

                // Pembulatan ke kelipatan Rp 500 ke atas murni
                $priceFinal = ceil($exactProratePrice / 500) * 500;
            } else {
                // Default / Full Cost: Menggunakan harga penuh satu bulan reguler
                $priceFinal = $basePriceAfterDiscount;
            }

            // Jika durasi paket lebih dari 1 bulan (multi-months), kalikan sisa bulan tambahannya
            if ($months > 1) {
                $priceFinal += ($package->price * ($months - 1));
            }


            /** ----------------------------------------------------
             * 7. Create Customer Subscription
             * ---------------------------------------------------- */
            // Siklus penagihan jatuh tempo (Default: tanggal 15 setelah masa durasi bulan habis)
            $subscriptionEndDate = now()->addMonths($months)->day(15);

            $subscription = CustomerSubscription::create([
                'customer_id'          => $customer->id,
                'package_id'           => $package->id,
                'package_duration_id'  => $packageDuration->id,
                'status'               => 'active',
                'start_date'           => now(),
                'end_date'             => $subscriptionEndDate,
                'price_final'          => $priceFinal,         // 💡 Menyimpan hasil akhir kalkulasi prorate/full cost
                'discount_type_final'  => $discountType,       // 💡 Menyimpan tipe diskon membership
                'discount_value_final' => $discountValue,      // 💡 Menyimpan nominal potongan diskon membership
            ]);


            /** ----------------------------------------------------
             * 8. Ambil Billing Periode Aktif (bulan saat ini)
             * ---------------------------------------------------- */
            $now = now();
            $billingPeriode = BillingPeriode::where('year', $now->year)
                ->where('month', $now->format('F'))
                ->firstOrFail();


            /** ----------------------------------------------------
             * 9. Create Billing Subscription Periode
             * ---------------------------------------------------- */
            $subscriptionPeriod = BillingSubscriptionPeriode::create([
                'customer_subscription_id' => $subscription->id,
                'billing_periode_id'       => $billingPeriode->id,
                'status'               => 'active',
            ]);


            /** ----------------------------------------------------
             * 10. Create Customer Addons (if provided)
             * ---------------------------------------------------- */
            if (!empty($data['addons'])) {
                foreach ($data['addons'] as $addon) {
                    $subtotal = ($addon['qty'] ?? 1) * $addon['unit_price'];

                    CustomerAddon::create([
                        'slug'        => Str::uuid(),
                        'customer_id' => $customer->id,
                        'addon_id'    => $addon['id'],
                        'qty'         => $addon['qty'] ?? 1,
                        'unit_price'  => $addon['unit_price'],
                        'subtotal'    => $subtotal,
                        'start_date'  => now(),
                        'end_date'    => null,   // recurring → null
                        'is_active'   => 1,
                    ]);
                }
            }


            /** ----------------------------------------------------
             * 11. Create Initial Billing
             * ---------------------------------------------------- */
            $result = $this->billingCreateService->createBilling(
                $websystem,
                $customer,
                $subscriptionPeriod,
                $billingPeriode,
                $subscription,
                $traceId
            );

            /** ----------------------------------------------------
             * 11. Logging
             * ---------------------------------------------------- */
            LogHelper::logInfo(
                null,
                [
                    'customer_id'   => $customer->id,
                ],
                201,
                'Customer created successfully',
                $traceId,
                $timer->lap()
            );

            return $customer;
        });
    }

    private function prepareCustomerData(array $data, string $traceId): array
    {
        $formatter = new FormatReceiver();
        $creds = $this->generateCredential($data['name'], $traceId);

        return $this->addServiceSpecificData([
            'name' => $data['name'],
            'email' => $data['email'],
            'slug' => Str::slug(explode(' ', $data['name'])[0] . '-' . Str::random(6)),
            'password' => Hash::make($data['password']),
            'address' => $data['address'],
            'contact' => $formatter->formatReceiver($data['contact'], null, false),
            'internet_service' => $data['internet_service'],
            'primary_server' => $data['mikrotik_id'],
            'mikrotik_id' => $data['mikrotik_id'],
            'username' => $creds['username'],
            'user_id' => $data['user_id'],
            'disabled' => 'true',
            'activation' => 'false'

        ], $data, $creds, $traceId);
    }

    private function addServiceSpecificData(array $customerData, array $requestData, array $creds, string $traceId): array
    {
        if ($requestData['internet_service'] === 'ppp') {
            $customerData['password_ppp'] = $creds['password'];
            $customerData['ppp_type_id'] = $requestData['ppp_type_id'];
        } elseif ($requestData['internet_service'] === 'ip_static') {
            $customerData += [
                'simpleque_name' => $creds['username'],
                'ip_static' => $requestData['ip_static'],
                'mac_address' => $requestData['mac_address'],
                'interface' => $requestData['interface']
            ];
        }

        return $customerData;
    }

    public function generateCredential(string $name, string $traceId): array
    {
        try {
            $today = Carbon::today();
            $countToday = Customer::whereDate('created_at', $today)->count();
            $activationNumber = str_pad($countToday + 1, 2, '0', STR_PAD_LEFT);
            $datePrefix = $today->format('Ymd') . $activationNumber;
            $username = $this->generateUsernameFromName($datePrefix, $name);

            $credentials = [
                'username' => $username,
                'password' => $datePrefix,
            ];

            return $credentials;
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'exception' => [
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ],
                    'name' => $name
                ],
                500,
                'Failed to generate credentials',
                $traceId
            );
            throw $e;
        }
    }

    public function generateUsernameFromName(string $datePrefix, string $name): string
    {
        $parts = array_values(array_filter(explode(' ', strtolower($name))));
        $count = count($parts);

        if ($count === 1) {
            $base = $parts[0];
        } elseif ($count === 2) {
            $base = $parts[0] . '-' . $parts[1];
        } else {
            // Ambil kata1 + kata2 + inisial kata terakhir
            $lastInitial = substr(end($parts), 0, 1);
            $base = $parts[0] . '-' . $parts[1] . '-' . $lastInitial;
        }

        // Convert to slug safe format
        $base = Str::slug($base);

        // Cek bentrok di database (PPP username)
        $username = $datePrefix . '@' . $base;
        $counter = 1;

        while (Customer::where('username', $username)->exists()) {
            $username = $base . '-' . $counter;
            $counter++;
        }

        return $username;
    }

    private function resolveMembershipDates(array $data): array
    {
        // default (monthly)
        $startDate = now();
        $endDate   = null;

        if ($data['billing_option'] === 'one_time') {

            if (empty($data['billing_periode_name'])) {
                throw new \InvalidArgumentException(
                    'billing_periode_name is required for one_time billing'
                );
            }

            // contoh: "January 2026"
            $startDate = Carbon::createFromFormat(
                'F Y',
                $data['billing_periode_name']
            )->startOfMonth();

            $endDate = $startDate->copy()->endOfMonth();
        }

        return [$startDate, $endDate];
    }
}
