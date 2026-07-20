<?php

namespace App\Services\Whatsapp\CloudAPI\Broadcast;

use App\Helpers\ExecutionTimer;
use App\Helpers\FormatReceiver;
use App\Helpers\LogHelper;
use App\Jobs\Whatsapp\CloudAPI\Broadcast\OverdueReminderJob;
use App\Models\Whatsapp\WhatsappGateway;
use App\Services\Whatsapp\CloudAPI\Collectors\OverdueBillingCollector;
use App\Services\Whatsapp\CloudAPI\Collectors\AdminBankCollector;
use App\Services\Whatsapp\CloudAPI\Broadcast\Traits\MapsBroadcastBankPayload;
use App\Services\Whatsapp\CloudAPI\Broadcast\Traits\ResolvesAdminContact;
use Carbon\Carbon;

class OverdueBroadcast
{
    use MapsBroadcastBankPayload, ResolvesAdminContact;

    public function __construct(
        private OverdueBillingCollector $collector,
        private AdminBankCollector $bankCollector
    ) {}

    public function run(string $traceId)
    {
        $timer = new ExecutionTimer();
        LogHelper::logInfo(null, [], 100, '[OverdueBroadcast] Start', $traceId, $timer->lap());

        $templateName1 = 'pembayaran_terlambat_1';    // 2 Bank + 1 E-Wallet
        $templateName2 = 'pembayaran_terlambat_v3';  // 1 Bank + 1 E-Wallet
        $templateName3 = 'pembayaran_terlambat_v2';  // Hanya 2 Bank

        // Ambil data dari collector dengan 3 parameter template slug
        $billings = $this->collector->collect(now(), $templateName1, $templateName2, $templateName3);

        LogHelper::logInfo(null, [
            'count' => $billings->count()
        ], 110, '[OverdueBroadcast] Billing collected', $traceId, $timer->lap());

        if ($billings->isEmpty()) {
            LogHelper::logInfo(null, [], 111, '[OverdueBroadcast] No billing to process', $traceId, $timer->lap());
            return;
        }

        // ===============================================================
        // RESOLVE GATEWAY & ACCOUNT
        // ===============================================================
        $gateway = WhatsappGateway::where('type', 'cloud')
            ->where('is_active', true)
            ->with(['accounts' => function ($q) {
                $q->where('is_active', true);
            }])
            ->first();

        if (!$gateway || $gateway->accounts->isEmpty()) {
            LogHelper::logError(null, [], 120, '[OverdueBroadcast] Gateway/Account invalid', $traceId, $timer->lap());
            return;
        }

        $account = $gateway->accounts->first();

        // ===============================================================
        // RESOLVE GLOBAL ADMIN CONTACT VIA TRAIT
        // ===============================================================
        $adminContact    = $this->getAdminContact();
        $contactAdmin    = $adminContact['contact_admin'];
        $contactWhatsapp = $adminContact['contact_whatsapp'];

        $formatter = new FormatReceiver;
        $overrideNumber    = config('messaging.phone_number_override', null);

        // ===============================================================
        // PROCESS ENGINE LOOP (Delay-Free Throttling)
        // ===============================================================
        foreach ($billings as $idx => $data) {

            $adminBanks = $this->bankCollector->getBanksByUserId($data->user_id ?? 0);
            $banks      = $adminBanks->where('type', 'bank')->values();
            $ewallets   = $adminBanks->where('type', 'ewallet')->values();

            // Selesaikan pemetaan template & isi bank_payload lewat Trait secara dinamis
            $bankResolution = $this->resolveBankTemplateAndPayload($banks, $ewallets, [
                $templateName1,
                $templateName2,
                $templateName3
            ]);

            $resolvedTemplate = $bankResolution['template'];
            $bankPayload      = $bankResolution['payload'];

            $targetContact = $overrideNumber ? (string) $overrideNumber : $data->customer_contact;
            $recipient     = $formatter->formatReceiver($targetContact, null, false);

            $lateDays = max(
                Carbon::parse($data->final_due_date)->diffInDays(now(), false),
                0
            );

            LogHelper::logInfo(null, [
                'index'         => $idx,
                'customer_id'   => $data->customer_id,
                'template'      => $resolvedTemplate,
            ], 200, '[OverdueBroadcast] Dispatch job to queue instantly', $traceId, $timer->lap());

            // 💡 Diperbaiki: Mengirim job langsung ke queue tanpa rantai ->delay()
            OverdueReminderJob::dispatch(
                contact: $recipient,
                customerId: (string) $data->customer_id,
                packageName: $data->package_name,
                totalAmount: $this->currency($data->total),
                lateDays: $lateDays,
                note: 'penghentian layanan internet',
                templateName: $resolvedTemplate,
                account: $account,
                bankPayload: $bankPayload,
                contactAdmin: $contactAdmin,
                contactWhatsapp: $contactWhatsapp
            )->onQueue('notification');
        }

        LogHelper::logInfo(null, [], 900, '[OverdueBroadcast] Finished dispatch loop safely without application side delays', $traceId, $timer->lap());
    }

    private function currency(int|float|null $n): string
    {
        return 'Rp. ' . number_format($n ?? 0, 2, ',', '.');
    }
}
