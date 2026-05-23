<?php

namespace App\Services\Customers;

use App\Models\Billings\Billing;
use App\Models\User;
use App\Models\Websystem;
use App\Models\Packages\PackageDuration;
use App\Models\Whatsapp\WhatsappNumber;
use Illuminate\Support\Carbon;
use Jenssegers\Agent\Agent;
use App\Helpers\LogHelper;
use App\Helpers\FormatReceiver;
use App\Models\Billings\BillingPayment;
use App\Models\Customers\CustomerSubscription;
use App\Models\Membership\CustomerMembership;

class PaymentDashboardService
{
    public function getDashboardData($customer)
    {
        $traceId = uniqid('dashboard_', true);

        $customerId = $customer->id;

        $billings = Billing::where('customer_id', $customerId)
            ->where('status', 'BL')
            ->get();

        $lastPayment = BillingPayment::with('billing')
            ->whereHas(
                'billing',
                fn($q) =>
                $q->where('customer_id', $customerId)->where('status', 'LS')
            )
            ->latest('payment_time')
            ->first();

        // 1. Ambil data user penanggung jawab customer beserta banknya yang aktif
        $user = User::with(['banks' => function ($query) {
            $query->where('disabled', 'false'); // Memastikan hanya mengambil bank master yang aktif
        }])->find($customer->user_id);

        // 2. Ambil daftar banknya (Menghasilkan Collection objek Bank)
        $banks = $user ? $user->banks : collect();

        $customerSubscription = CustomerSubscription::with('package')
            ->where('customer_id', $customerId)
            ->where('status', 'active')
            ->first();

        $customerMembership = CustomerMembership::with('membership')
            ->where('customer_id', $customerId)
            ->where('is_active', true)
            ->first();

        $basePrice = $customerSubscription?->package?->price ?? 0;
        $packageName = $customerSubscription?->package?->name ?? 'No Active Package';

        /** =======================
         * Diskon
         * ======================= */
        $memberName = 'No Active Member';
        $discountMemberNominal = 0;

        if ($member = $customerMembership?->membership) {
            $memberName = $member->name;
            $discountMemberNominal = $member->discount_type === 'percent'
                ? $basePrice * ($member->discount_value / 100)
                : $member->discount_value;
        }

        $discountDuration = null;
        $discountDurationNominal = 0;
        $periodEn = $periodIn = null;

        if ($packageDuration = PackageDuration::where('package_id', $customerSubscription?->package_id)->first()) {
            $periodEn = $packageDuration->duration->name_en;
            $periodIn = $packageDuration->duration->name_id;

            if ($packageDuration->discount_type !== 'none') {
                $discountDuration = $packageDuration->name_en;
                $discountDurationNominal = $packageDuration->discount_type === 'percent'
                    ? $basePrice * ($packageDuration->discount_value / 100)
                    : $packageDuration->discount_value;
            }
        }

        $totalDiscount = $discountMemberNominal + $discountDurationNominal;

        $lastBilling = $billings->last();

        $dueDateText = $lastBilling?->due_date
            ? Carbon::parse($lastBilling->due_date)->translatedFormat('d F Y, H:i') . ' WIB'
            : Carbon::now()->startOfMonth()->addDays(14)->translatedFormat('d F Y, H:i') . ' WIB';

        $formatter = new FormatReceiver();

        // Menarik data nomor whatsapp default yang terhubung ke gateway "gowa"
        $whatsappNumberRecord = WhatsappNumber::where('is_enabled', true)
            ->where('is_default', true)
            ->whereHas('account', function ($query) {
                $query->where('is_active', 1)
                    ->whereHas('gateway', function ($q) {
                        $q->where('slug', 'gowa');
                    });
            })
            ->first();

        // Ambil value kolom 'number'-nya, jika tidak ada pasang fallback nomor lama
        $adminNumber = $whatsappNumberRecord ? $whatsappNumberRecord->number : $customer->user->contact;

        // Masukkan ke dalam formatter Anda yang lama
        $contactAdmin    = $formatter->formatReceiver($adminNumber, 'broadcast', false);
        $contactWhatsapp = $formatter->formatReceiver($adminNumber, null, false);

        $websystem = Websystem::where('slug', 'websystem')->first();

        $priceAfterTax = max($basePrice - $totalDiscount, 0);
        if ($websystem?->enable_tax) {
            $priceAfterTax *= (1 + $websystem->tax_rate / 100);
        }

        $view = (new Agent())->isMobile()
            ? 'mobile.customers.pages.dashboard'
            : 'customers.pages.dashboard';

        /** =======================
         * Compile log (FINAL)
         * ======================= */
        LogHelper::logInfo(
            null,
            [
                'customer_id' => $customerId,
                'billing_count' => $billings->count(),
                'last_billing_id' => $lastBilling?->id,
                'last_payment_time' => $lastPayment?->payment_time,
                'base_price' => $basePrice,
                'member_name' => $memberName,
                'discount_member_nominal' => $discountMemberNominal,
                'discount_duration' => $discountDuration,
                'discount_duration_nominal' => $discountDurationNominal,
                'total_discount' => $totalDiscount,
                'price_after_tax' => $priceAfterTax,
                'due_date_text' => $dueDateText,
                'tax_enabled' => $websystem?->enable_tax,
                'tax_rate' => $websystem?->tax_rate,
                'view' => $view,
            ],
            200,
            'Payment dashboard compiled',
            $traceId
        );

        return [
            'view' => $view,
            'data' => compact(
                'customer',
                'packageName',
                'billings',
                'websystem',
                'lastPayment',
                'banks',
                'contactAdmin',
                'memberName',
                'lastBilling',
                'totalDiscount',
                'basePrice',
                'priceAfterTax',
                'periodEn',
                'periodIn',
                'dueDateText',
                'contactWhatsapp'
            ),
        ];
    }
}
