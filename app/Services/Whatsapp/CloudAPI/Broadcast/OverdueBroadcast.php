<?php

namespace App\Services\WhatsApp\CloudAPI\Broadcast;

use App\Helpers\ExecutionTimer;
use App\Helpers\FormatReceiver;
use App\Helpers\LogHelper;
use App\Models\WhatsApp\WhatsAppGateway;
use App\Services\WhatsApp\CloudAPI\Collectors\OverdueBillingCollector;
use App\Services\Messaging\Collectors\AdminBankCollector;
use App\Services\WhatsApp\CloudAPI\Broadcast\Traits\MapsBroadcastBankPayload;
use App\Services\WhatsApp\CloudAPI\Broadcast\Traits\ResolvesAdminContact;
use App\Services\WhatsApp\CloudAPI\Messages\TemplateMessageService;
use Carbon\Carbon;

class OverdueBroadcast
{
    use MapsBroadcastBankPayload, ResolvesAdminContact;

    private const BILLING_TEMPLATES = [
        '3_banks_1_ewallet' => 'terlambat_3rek_1ewallet',
        '3_banks_only'      => 'terlambat_3rek',
        '2_banks_1_ewallet' => 'terlambat_2rek_1ewallet',
        '2_banks_only'      => 'terlambat_2rek',
        '1_bank_1_ewallet'  => 'terlambat_1rek_1ewallet',
        '1_bank_only'       => 'terlambat_1rek',
        '1_ewallet_only'    => 'terlambat_1ewallet',
    ];

    public function __construct(
        private OverdueBillingCollector $collector,
        private AdminBankCollector $bankCollector,
        private TemplateMessageService $templateMessageService,
        private FormatReceiver $formatter
    ) {}

    public function run(string $traceId): void
    {
        $timer = new ExecutionTimer();
        LogHelper::logInfo(null, [], 100, '[OverdueBroadcast] Start', $traceId, $timer->lap());

        $overdueBillings = $this->collector->collect(now(), ...array_values(self::BILLING_TEMPLATES));

        LogHelper::logInfo(null, [
            'count' => $overdueBillings->count()
        ], 110, '[OverdueBroadcast] Billing collected', $traceId, $timer->lap());

        if ($overdueBillings->isEmpty()) {
            LogHelper::logInfo(null, [], 111, '[OverdueBroadcast] No billing to process', $traceId, $timer->lap());
            return;
        }

        // ===============================================================
        // 💡 LIMIT HANDLING UNTUK OVERRIDE MODE (MAKSIMAL 3 PESAN TESTING)
        // ===============================================================
        $sampleRecipient = $this->formatter->resolveRecipient(null);
        if ($sampleRecipient['is_override']) {
            $overdueBillings = $overdueBillings->take(3);
            LogHelper::logInfo(null, ['override_detected' => true], 100, '[OverdueBroadcast] Throttling active. Reminders sliced to max 3 items for testing.', $traceId, $timer->lap());
        }

        // ===============================================================
        // RESOLVE GATEWAY & ACCOUNT
        // ===============================================================
        $gateway = WhatsAppGateway::where('type', 'cloud')
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

        // ===============================================================
        // PROCESS ENGINE LOOP (DIRECT EXECUTION TO META CLOUD API)
        // ===============================================================
        foreach ($overdueBillings as $idx => $data) {
            $adminBanks = $this->bankCollector->getBanksByUserId($data->user_id ?? 0);
            $banks      = $adminBanks->where('type', 'bank')->values();
            $ewallets   = $adminBanks->where('type', 'ewallet')->values();

            // Selesaikan pemetaan template & isi bank_payload lewat Trait secara dinamis
            $bankResolution = $this->resolveBankTemplateAndPayload($banks, $ewallets, self::BILLING_TEMPLATES);

            $resolvedTemplate = $bankResolution['template'];
            $bankPayload      = $bankResolution['payload'];

            $lateDays = max(
                Carbon::parse($data->final_due_date)->diffInDays(now(), false),
                0
            );

            // Resolusi penerima & format via FormatReceiver (Otomatis handle override number)
            $recipient = $this->formatter->resolveRecipient($data->customer_contact);

            $this->templateMessageService->sendOverdueBroadcast([
                'recipient'        => $recipient['formatted'],
                'customer_id'      => $data->customer_id,
                'package_name'     => $data->package_name,
                'total_amount'     => $this->currency($data->total),
                'late_days'        => $lateDays,
                'note'             => 'penghentian layanan internet',
                'templateName'     => $resolvedTemplate,
                'account'          => $account,
                'bank_payload'     => $bankPayload,
                'contact_admin'    => $contactAdmin,
                'contact_whatsapp' => $contactWhatsapp,
            ]);
        }

        LogHelper::logInfo(
            null,
            ['total_sent' => $overdueBillings->count()],
            200,
            '[OverdueBroadcast] Direct execution to Meta Cloud API finished successfully',
            $traceId,
            $timer->lap()
        );
    }

    private function currency(int|float|null $n): string
    {
        return 'Rp. ' . number_format($n ?? 0, 2, ',', '.');
    }
}
