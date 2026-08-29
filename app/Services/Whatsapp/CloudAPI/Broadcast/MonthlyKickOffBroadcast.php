<?php

namespace App\Services\Whatsapp\CloudAPI\Broadcast;

use App\Enums\WhatsAppAction;
use App\Helpers\ExecutionTimer;
use App\Helpers\FormatReceiver;
use App\Helpers\LogHelper;
use App\Jobs\Whatsapp\CloudAPI\Broadcast\MonthlyKickOffBroadcastJob;
use App\Models\Whatsapp\WhatsappGateway;
use App\Services\Whatsapp\CloudAPI\Collectors\ActiveCustomerCollector;
use App\Services\Whatsapp\CloudAPI\Collectors\AdminBankCollector;

// 💡 Import kedua Trait Anda di sini
use App\Services\Whatsapp\CloudAPI\Broadcast\Traits\MapsBroadcastBankPayload;
use App\Services\Whatsapp\CloudAPI\Broadcast\Traits\ResolvesAdminContact;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MonthlyKickOffBroadcast
{
    // 💡 Gunakan kedua trait secara bersamaan
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
        private AdminBankCollector $bankCollector
    ) {}

    public function run(string $messageType, string $traceId)
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
        $gateway = WhatsappGateway::where('type', 'cloud')
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
        // PROCESS ENGINE LOOP
        // ===============================================================
        $delayInSeconds    = 0;
        $delay             = $this->getNextDelay('notification', $traceId);
        $initialDate       = Carbon::now()->translatedFormat('d F Y'); // Menyesuaikan property handler: initialDate
        $formatter         = new FormatReceiver;

        foreach ($customers as $idx => $data) {
            $adminBanks = $this->bankCollector->getBanksByUserId($data->user_id ?? 0);
            $banks      = $adminBanks->where('type', 'bank')->values();
            $ewallets   = $adminBanks->where('type', 'ewallet')->values();

            // Panggil Trait Bank Payload (Mendukung 7 Template Dinamis)
            $bankResolution = $this->resolveBankTemplateAndPayload($banks, $ewallets, self::BILLING_TEMPLATES);

            $resolvedTemplate = $bankResolution['template'];
            $bankPayload      = $bankResolution['payload'];
            $channel          = $data?->package_name ?? 'Shanetfiber';

            $targetContact = $overrideNumber ? (string) $overrideNumber : $data->customer_contact;
            $recipient     = $formatter->formatReceiver($targetContact, null, false);

            MonthlyKickOffBroadcastJob::dispatch(
                account: $account,
                templateName: $resolvedTemplate,
                customerId: (string) $data->customer_id,
                customerName: $data->customer_name,
                contact: $recipient,
                initialDate: $initialDate,
                channel: $channel,
                bankPayload: $bankPayload,
                contactAdmin: $contactAdmin,
                contactWhatsapp: $contactWhatsapp
            )
                ->delay($delay)
                ->onQueue('notification');

            $delayInSeconds += 2;
            $delay = now()->addSeconds($delayInSeconds);
        }

        LogHelper::logInfo(null, ['total_dispatched' => $customers->count()], 900, '[MonthlyKickOffBroadcast] Finished process', $traceId, $timer->lap());
    }

    private function getNextDelay(string $queue, string $traceId): Carbon
    {
        $last = DB::table('jobs')
            ->where('queue', $queue)
            ->orderByDesc('available_at')
            ->first();

        if (!$last || !$last->reserved_at) {
            return now();
        }

        return Carbon::createFromTimestamp($last->reserved_at)->addSeconds(1);
    }
}
