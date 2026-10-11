<?php

namespace App\Services\WhatsApp\CloudAPI\Broadcast;

use App\Helpers\ExecutionTimer;
use App\Helpers\FormatReceiver;
use App\Helpers\LogHelper;
use App\Helpers\MaskCustomerName;
use App\Models\WhatsApp\WhatsAppGateway;
use App\Services\WhatsApp\CloudAPI\Collectors\BillingReminderCollector;
use App\Services\Messaging\Collectors\AdminBankCollector;
use App\Services\WhatsApp\CloudAPI\Messages\TemplateMessageService;
use App\Services\WhatsApp\CloudAPI\Broadcast\Traits\MapsBroadcastBankPayload;
use App\Services\WhatsApp\CloudAPI\Broadcast\Traits\ResolvesAdminContact;
use Carbon\Carbon;

class MidMonthBroadcast
{
    use MapsBroadcastBankPayload, ResolvesAdminContact;

    private const BILLING_TEMPLATES = [
        '3_banks_1_ewallet' => 'pengingat_3rek_1ewallet',
        '3_banks_only'      => 'pengingat_3rek',
        '2_banks_1_ewallet' => 'pengingat_2rek_1ewallet',
        '2_banks_only'      => 'pengingat_2rek',
        '1_bank_1_ewallet'  => 'pengingat_1rek_1ewallet',
        '1_bank_only'       => 'pengingat_1rek',
        '1_ewallet_only'    => 'pengingat_1ewallet',
    ];

    public function __construct(
        private BillingReminderCollector $collector,
        private TemplateMessageService $templateMessageService,
        private AdminBankCollector $bankCollector,
        private FormatReceiver $formatter
    ) {}

    public function run(string $traceId): void
    {
        $timer = new ExecutionTimer();
        LogHelper::logInfo(null, [], 100, '[MidMonthBroadcast] Start', $traceId, $timer->lap());

        $reminders = $this->collector->collect(now(), ...array_values(self::BILLING_TEMPLATES));

        LogHelper::logInfo(null, [
            'count' => $reminders->count()
        ], 110, '[MidMonthBroadcast] Billing collected', $traceId, $timer->lap());

        if ($reminders->isEmpty()) {
            LogHelper::logInfo(null, [], 111, '[MidMonthBroadcast] No billing to process', $traceId, $timer->lap());
            return;
        }

        // ===============================================================
        // 💡 LIMIT HANDLING UNTUK OVERRIDE MODE (MAKSIMAL 3 PESAN TESTING)
        // ===============================================================
        $sampleRecipient = $this->formatter->resolveRecipient(null);
        if ($sampleRecipient['is_override']) {
            $reminders = $reminders->take(3);
            LogHelper::logInfo(null, ['override_detected' => true], 100, '[MidMonthBroadcast] Throttling active. Reminders sliced to max 3 items for testing.', $traceId, $timer->lap());
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
            LogHelper::logError(null, [], 120, '[MidMonthBroadcast] Gateway/Account invalid', $traceId, $timer->lap());
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
        foreach ($reminders as $data) {
            $adminBanks = $this->bankCollector->getBanksByUserId($data->user_id ?? 0);
            $banks      = $adminBanks->where('type', 'bank')->values();
            $ewallets   = $adminBanks->where('type', 'ewallet')->values();

            // Panggil Trait untuk dapati Template & Payload Bank secara otomatis
            $bankResolution = $this->resolveBankTemplateAndPayload($banks, $ewallets, self::BILLING_TEMPLATES);

            $resolvedTemplate = $bankResolution['template'];
            $bankPayload      = $bankResolution['payload'];

            $due_date_formatted = $data->final_due_date
                ? Carbon::parse($data->final_due_date)->translatedFormat('d F Y')
                : '-';

            $accountData = MaskCustomerName::buildAccountData($data->customer_name, $data->package_name);

            // Resolusi penerima & format via FormatReceiver (Otomatis handle override number)
            $recipient = $this->formatter->resolveRecipient($data->customer_contact);

            $this->templateMessageService->sendBroadcastMidMonth([
                'recipient'        => $recipient['formatted'],
                'customer_id'      => $data->customer_id,
                'customer_name'    => $data->customer_name,
                'package_name'     => $data->package_name,
                'account_data'     => $accountData,
                'total_amount'     => $this->currency($data->total),
                'due_date'         => $due_date_formatted,
                'note'             => 'gangguan layanan internet',
                'templateName'     => $resolvedTemplate,
                'account'          => $account,
                'bank_payload'     => $bankPayload,
                'contact_admin'    => $contactAdmin,
                'contact_whatsapp' => $contactWhatsapp,
            ]);
        }

        LogHelper::logInfo(
            null,
            ['total_sent' => $reminders->count()],
            200,
            '[MidMonthBroadcast] Direct execution to Meta Cloud API finished successfully',
            $traceId,
            $timer->lap()
        );
    }

    private function currency(int|float|null $n): string
    {
        return 'Rp. ' . number_format($n ?? 0, 2, ',', '.');
    }
}
