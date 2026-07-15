<?php

namespace App\Services\Customers\Membership;

use App\Helpers\LogHelper;
use App\Models\Billings\Billing;
use App\Models\Billings\BillingPeriode;
use App\Models\Customers\Customer;
use Carbon\Carbon;

class MembershipChangeService
{
    public function __construct(
        private MembershipResolver $membershipResolver
    ) {}

    public function apply(
        Customer $customer,
        array $data,
        BillingPeriode $period,
        Billing $billing,
        string $traceId,
        string $mode,
        bool $updateBilling
    ): void {

        LogHelper::logInfo(null, [
            'step' => 'MEMBERSHIP_APPLY_ENTER',
            'member_id' => $data['member_id'] ?? null,
            'mode' => $mode,
            'update_billing' => $updateBilling,
            'array_data' => $data,
        ], 200, 'Membership Change Flow', $traceId);

        // ===============================
        // REMOVE MEMBERSHIP
        // ===============================
        if ($mode === 'remove') {

            LogHelper::logInfo(null, [
                'step' => 'MEMBERSHIP_REMOVE'
            ], 200, 'Membership Change Flow', $traceId);

            $this->membershipResolver->remove($customer, $traceId);

            return;
        }

        // ===============================
        // APPLY NEXT PERIOD
        // ===============================
        if ($mode === 'next') {

            // 1. Ambil nama periode dari data input FE (jika ada, misal untuk tipe one_time)
            $periodeName = $data['billing_periode_name'] ?? null;

            // 2. Jika KOSONG (kasus tipe 'monthly' saat billing bulan ini sudah lunas)
            // Otomatis buat nama periode berdasarkan bulan depan dari objek $period saat ini
            if (empty($periodeName) && $period) {
                // Misal $period->end_date adalah 2026-07-31, maka ditambah 1 hari menjadi Agustus 2026
                $nextMonth = Carbon::parse($period->end_date)->addDay();
                $periodeName = $nextMonth->translatedFormat('F Y'); // Menghasilkan "August 2026" atau "Agustus 2026"

                // Simpan kembali ke array $data agar di dalam resolver datanya konsisten jika dibutuhkan
                $data['billing_periode_name'] = $periodeName;
            }

            // 3. Validasi akhir (jika benar-benar tidak bisa dihitung)
            if (!$periodeName) {
                throw new \InvalidArgumentException('billing_periode_name required for next membership');
            }

            // 4. Parsing format nama bulan ke range tanggal (Pastikan locale/bahasa sesuai format Carbon kamu)
            // Jika format database menggunakan bahasa Indonesia (Juli, Agustus), gunakan parse atau createFromLocaleFormat
            try {
                $targetStart = Carbon::createFromFormat('F Y', $periodeName)->startOfMonth();
            } catch (\Exception $e) {
                // Backup handle jika format string bahasa lokal Indonesia (misal: "Agustus 2026")
                $targetStart = Carbon::parse($periodeName)->startOfMonth();
            }

            $targetEnd = $targetStart->copy()->endOfMonth();

            LogHelper::logInfo(null, [
                'step' => 'MEMBERSHIP_SCHEDULE_NEXT',
                'resolved_periode_name' => $periodeName,
                'start' => $targetStart->toDateString(),
                'end' => $targetEnd->toDateString()
            ], 200, 'Membership Change Flow', $traceId);

            $this->membershipResolver->scheduleNext(
                $customer,
                $data,
                $targetStart,
                $targetEnd,
                $traceId,
                $billing
            );

            return;
        }

        // ===============================
        // APPLY CURRENT
        // ===============================
        LogHelper::logInfo(null, [
            'step' => 'MEMBERSHIP_APPLY_CURRENT'
        ], 200, 'Membership Change Flow', $traceId);

        $this->membershipResolver->applyNow(
            $customer,
            $data,
            $period,
            $billing,
            $traceId,
            $updateBilling
        );
    }
}
