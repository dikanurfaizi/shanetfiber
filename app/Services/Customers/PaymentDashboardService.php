<?php

namespace App\Services\Customers;

use App\Models\Billing\Billing;
use App\Models\Bank;
use App\Models\Websystem;
use App\Models\SubscriptionDuration;
use Illuminate\Support\Carbon;
use Jenssegers\Agent\Agent;
use App\Helpers\LogHelper;
use App\Helpers\FormatReceiver;

class PaymentDashboardService
{
    public function getDashboardData($customer)
    {
        $traceId = uniqid('dashboard_', true);
        $customerId = $customer->id;

        LogHelper::logInfo(null, ['customer_id' => $customerId], 200, 'Mulai load dashboard customer', $traceId);

        // Ambil billing
        $billings = Billing::where('customer_id', $customerId)
            ->where('status', 'BL')
            ->get();
        LogHelper::logInfo(null, ['billing_count' => $billings->count()], 200, 'Billing BL berhasil diambil', $traceId);

        $lastPayment = Billing::where('customer_id', $customerId)
            ->where('status', 'LS')
            ->latest('payment_time')
            ->first();
        LogHelper::logInfo(null, ['last_payment_id' => $lastPayment?->id], 200, 'Last payment berhasil diambil', $traceId);

        $banks = Bank::where('user_id', $customer->user_id)
            ->where('disabled', 'false')
            ->get();
        LogHelper::logInfo(null, ['banks_count' => $banks->count()], 200, 'Bank aktif diambil', $traceId);

        // Harga dasar paket dan diskon member
        $basePrice = $customer->paket->price;
        $discountMember = null;
        $discountNominal = 0;

        if ($customer->member_id) {
            $discountMember = $customer->member->type;
            $discountNominal = $customer->member->discount;
            LogHelper::logInfo(null, [
                'member_type' => $discountMember,
                'member_discount' => $discountNominal
            ], 200, 'Diskon member diterapkan', $traceId);
        }

        // Diskon durasi
        $discountDuration = null;
        $discountDurationNominal = 0;
        $periodEn = null;
        $periodIn = null;

        if ($subDuration = SubscriptionDuration::where('paket_id', $customer->paket_id)->first()) {
            $periodEn = $subDuration->name_en;
            $periodIn = $subDuration->name_id;

            if ($subDuration->discount_type !== 'none') {
                $discountDuration = $subDuration->name_en;
                $discountDurationNominal = $subDuration->discount_type === 'percent'
                    ? $basePrice * ($subDuration->discount_value / 100)
                    : $subDuration->discount_value;

                LogHelper::logInfo(null, [
                    'duration_name' => $discountDuration,
                    'duration_discount' => $discountDurationNominal
                ], 200, 'Diskon durasi diterapkan', $traceId);
            }
        }

        // Hitung total diskon
        $totalDiscount = $discountNominal + $discountDurationNominal;
        LogHelper::logInfo(null, ['total_discount' => $totalDiscount], 200, 'Total diskon dihitung', $traceId);

        // Tentukan tipe diskon
        $discountType = match (true) {
            $discountMember && $discountDuration => $discountMember . ' & ' . $discountDuration,
            $discountMember => $discountMember,
            $discountDuration => $discountDuration,
            default => '-',
        };
        LogHelper::logInfo(null, ['discount_type' => $discountType], 200, 'Tipe diskon ditentukan', $traceId);

        $lastBilling = $billings->last();
        LogHelper::logInfo(null, ['last_billing_id' => $lastBilling?->id], 200, 'Last billing diambil', $traceId);

        // Due date
        if ($lastBilling && $lastBilling->due_date) {
            $dueDateText = Carbon::parse($lastBilling->due_date)
                ->translatedFormat('d F Y, H:i') . ' WIB';
        } else {
            $dueDateText = Carbon::now()
                ->startOfMonth()
                ->addDays(14)
                ->translatedFormat('d F Y, H:i') . ' WIB';
        }
        LogHelper::logInfo(null, ['due_date_text' => $dueDateText], 200, 'Due date text ditentukan', $traceId);

        // Kontak admin
        $formatter = new FormatReceiver();
        $contactAdmin = $formatter->formatReceiver($customer->user->contact, 'broadcast', false);
        $contactWhatsapp = $formatter->formatReceiver($customer->user->contact, null, false);
        LogHelper::logInfo(null, ['contact_admin' => $contactAdmin], 200, 'Kontak admin diformat', $traceId);

        // Websystem
        $websystem = Websystem::where('slug', 'websystem')->first();
        LogHelper::logInfo(null, [
            'websystem_tax_enabled' => $websystem->enable_tax,
            'tax_rate' => $websystem->tax_rate
        ], 200, 'Websystem diambil', $traceId);

        // Harga setelah pajak
        $priceAfterTax = $basePrice - $totalDiscount;
        if ($websystem->enable_tax) {
            $priceAfterTax *= (1 + $websystem->tax_rate / 100);
        }
        LogHelper::logInfo(null, ['price_after_tax' => $priceAfterTax], 200, 'Harga setelah pajak dihitung', $traceId);

        // Device check
        $isMobile = (new Agent())->isMobile();
        $view = $isMobile
            ? 'mobile.customers.pages.dashboard'
            : 'customers.pages.dashboard';
        LogHelper::logInfo(null, ['view' => $view], 200, 'View dashboard ditentukan', $traceId);

        return [
            'view' => $view,
            'data' => compact(
                'customer',
                'billings',
                'websystem',
                'lastPayment',
                'banks',
                'contactAdmin',
                'discountType',
                'lastBilling',
                'totalDiscount',
                'basePrice',
                'priceAfterTax',
                'periodEn',
                'periodIn',
                'dueDateText',
                'contactWhatsapp'
            )
        ];
    }
}
