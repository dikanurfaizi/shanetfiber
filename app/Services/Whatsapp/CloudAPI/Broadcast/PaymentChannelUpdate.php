<?php

namespace App\Services\Whatsapp\CloudAPI\Broadcast;

use App\Helpers\FormatReceiver;
use App\Helpers\LogHelper;
use App\Jobs\Whatsapp\CloudAPI\Broadcast\PaymentChannelUpdateJob;
use App\Models\Whatsapp\WhatsappGateway;
use App\Services\Whatsapp\CloudAPI\Collectors\ActiveCustomerCollector;
use App\Services\Whatsapp\CloudAPI\Collectors\AdminBankCollector;

// 💡 Import kedua Trait Anda di sini
use App\Services\Whatsapp\CloudAPI\Broadcast\Traits\MapsBroadcastBankPayload;
use App\Services\Whatsapp\CloudAPI\Broadcast\Traits\ResolvesAdminContact;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PaymentChannelUpdate
{
    // 💡 Gunakan kedua trait secara bersamaan
    use MapsBroadcastBankPayload, ResolvesAdminContact;

    public function __construct(
        private ActiveCustomerCollector $collector,
        private AdminBankCollector $bankCollector
    ) {}

    public function run(string $traceId)
    {
        LogHelper::logInfo(null, [], 100, '[PaymentChannelUpdate] Start process', $traceId);

        $templateName1 = 'info_perubahan_rekening_pembayaran';
        $templateName2 = 'info_perubahan_rekening_pembayaran_1_bank_1_ewallet';
        $templateName3 = 'info_perubahan_rekening_pembayaran_2_bank';

        $customers = $this->collector->collect(now(), $templateName1, $templateName2, $templateName3);
        if ($customers->isEmpty()) return;

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
        $channelUpdateDate = '1 Juni 2026';
        $channel           = 'Shanetfiber';
        $overrideNumber    = config('messaging.phone_number_override');
        $formatter         = new FormatReceiver; // Tetap butuh untuk format nomor customer di dalam loop

        foreach ($customers as $idx => $data) {
            $adminBanks = $this->bankCollector->getBanksByUserId($data->user_id ?? 0);
            $banks      = $adminBanks->where('type', 'bank')->values();
            $ewallets   = $adminBanks->where('type', 'ewallet')->values();

            // Panggil Trait Bank Payload
            $bankResolution = $this->resolveBankTemplateAndPayload($banks, $ewallets, [
                $templateName1,
                $templateName2,
                $templateName3
            ]);

            $resolvedTemplate = $bankResolution['template'];
            $bankPayload      = $bankResolution['payload'];

            $targetContact = $overrideNumber ? (string) $overrideNumber : $data->customer_contact;
            $recipient     = $formatter->formatReceiver($targetContact, null, false);

            PaymentChannelUpdateJob::dispatch(
                account: $account,
                templateName: $resolvedTemplate,
                customerId: (string) $data->customer_id,
                customerName: $data->customer_name,
                contact: $recipient,
                channelUpdateDate: $channelUpdateDate,
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

        LogHelper::logInfo(null, ['total_dispatched' => $customers->count()], 900, '[PaymentChannelUpdate] Finished process', $traceId);
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
