<?php

namespace App\Services\WhatsApp\CloudAPI\Broadcast;

use App\Helpers\ExecutionTimer;
use App\Helpers\FormatReceiver;
use App\Helpers\LogHelper;
use App\Models\WhatsApp\WhatsAppGateway;
use App\Services\WhatsApp\CloudAPI\Collectors\ActiveCustomerCollector;
use App\Services\Messaging\Collectors\AdminBankCollector;
use App\Services\WhatsApp\CloudAPI\Broadcast\Traits\MapsBroadcastBankPayload;
use App\Services\WhatsApp\CloudAPI\Broadcast\Traits\ResolvesAdminContact;
use App\Services\WhatsApp\CloudAPI\Messages\TemplateMessageService;
use Illuminate\Support\Carbon;

class MonthlyKickOffBroadcast
{
    use MapsBroadcastBankPayload, ResolvesAdminContact;

    private const BILLING_TEMPLATES = [
        '3_banks_1_ewallet' => 'info_billing_3rek_1ewallet',
        '3_banks_only'      => 'info_billing_3rek',
        '2_banks_1_ewallet' => 'info_billing_2rek_1ewallet',
        '2_banks_only'      => 'info_billing_2rek',
        '1_bank_1_ewallet'  => 'info_billing_1rek_1ewallet',
        '1_bank_only'       => 'info_billing_1rek',
        '1_ewallet_only'    => 'info_billing_1ewallet',
    ];

    public function __construct(
        private ActiveCustomerCollector $collector,
        private AdminBankCollector $bankCollector,
        private TemplateMessageService $templateMessageService,
        private FormatReceiver $formatter
    ) {}

    public function run(string $messageType, string $traceId): void
    {
        $timer = new ExecutionTimer();
        LogHelper::logInfo(null, [], 100, '[MonthlyKickOffBroadcast] Start process', $traceId, $timer->lap());

        $customers = $this->collector->collect(now(), $messageType, ...self::BILLING_TEMPLATES);
        if ($customers->isEmpty()) return;

        $overrideNumber = config('messaging.phone_number_override', null);

        // ===============================================================
        // 💡 LIMIT HANDLING UNTUK OVERRIDE MODE (MAKSIMAL 3 PESAN)
        // ===============================================================
        if (!empty($overrideNumber)) {
            $customers = $customers->take(3);
            LogHelper::logInfo(null, ['override_detected' => true], 100, '[MonthlyKickOffBroadcast] Throttling active. Customers sliced to max 3 items.', $traceId, $timer->lap());
        }

        // ===============================================================
        // RESOLVE GATEWAY & ACCOUNT
        // ===============================================================
        $gateway = WhatsAppGateway::where('type', 'cloud')
            ->where('is_active', true)
            ->with(['accounts' => fn($q) => $q->where('is_active', true)])
            ->first();

        if (!$gateway || $gateway->accounts->isEmpty()) return;
        $account = $gateway->accounts->first();

        // ===============================================================
        // 💡 RESOLVE GLOBAL ADMIN CONTACT VIA TRAIT
        // ===============================================================
        $adminContact    = $this->getAdminContact();
        $contactAdmin    = $adminContact['contact_admin'];
        $contactWhatsapp = $adminContact['contact_whatsapp'];

        // ===============================================================
        // PROCESS ENGINE LOOP (DIRECT EXECUTION TO META CLOUD API)
        // ===============================================================
        $initialDate = Carbon::now()->translatedFormat('d F Y');

        foreach ($customers as $data) {
            $adminBanks = $this->bankCollector->getBanksByUserId($data->user_id ?? 0);
            $banks      = $adminBanks->where('type', 'bank')->values();
            $ewallets   = $adminBanks->where('type', 'ewallet')->values();

            // Panggil Trait Bank Payload (Mendukung 7 Template Dinamis)
            $bankResolution = $this->resolveBankTemplateAndPayload($banks, $ewallets, self::BILLING_TEMPLATES);

            $resolvedTemplate = $bankResolution['template'];
            $bankPayload      = $bankResolution['payload'];
            $channel          = $data?->package_name ?? 'Shanetfiber';

            // Resolusi penerima & format via FormatReceiver
            $recipient = $this->formatter->resolveRecipient($data->customer_contact);

            $this->templateMessageService->sendMonthlyKickOffBroadcast([
                'account'          => $account,
                'templateName'     => $resolvedTemplate,
                'customer_id'      => $data->customer_id,
                'customer_name'    => $data->customer_name,
                'recipient'        => $recipient['formatted'],
                'initial_date'     => $initialDate,
                'channel'          => $channel,
                'bank_payload'     => $bankPayload,
                'contact_admin'    => $contactAdmin,
                'contact_whatsapp' => $contactWhatsapp,
            ]);
        }

        LogHelper::logInfo(
            null,
            ['total_sent' => $customers->count()],
            200,
            '[MonthlyKickOffBroadcast] Direct execution to Meta Cloud API finished successfully',
            $traceId,
            $timer->lap()
        );
    }
}
