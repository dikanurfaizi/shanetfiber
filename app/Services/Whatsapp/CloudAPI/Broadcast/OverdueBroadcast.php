<?php

namespace App\Services\Whatsapp\CloudAPI\Broadcast;

use App\Helpers\FormatReceiver;
use App\Helpers\LogHelper;
use App\Jobs\Whatsapp\CloudAPI\Broadcast\OverdueReminderJob;
use App\Models\Whatsapp\WhatsappGateway;
use App\Services\Whatsapp\CloudAPI\Collectors\OverdueBillingCollector;
use App\Services\Whatsapp\CloudAPI\Collectors\AdminBankCollector; // 💡 Inject collector bank
// 💡 Gunakan Trait reusable
use App\Services\Whatsapp\CloudAPI\Broadcast\Traits\MapsBroadcastBankPayload;
use App\Services\Whatsapp\CloudAPI\Broadcast\Traits\ResolvesAdminContact;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OverdueBroadcast
{
    use MapsBroadcastBankPayload, ResolvesAdminContact;

    public function __construct(
        private OverdueBillingCollector $collector,
        private AdminBankCollector $bankCollector // 💡 Daftarkan di constructor
    ) {}

    public function run(string $traceId)
    {
        LogHelper::logInfo(null, [], 100, '[OverdueBroadcast] Start', $traceId);

        $templateName1 = 'pembayaran_terlambat_1';    // 2 Bank + 1 E-Wallet
        $templateName2 = 'pembayaran_terlambat_v3';  // 1 Bank + 1 E-Wallet
        $templateName3 = 'pembayaran_terlambat_v2';  // Hanya 2 Bank

        // Ambil data dari collector dengan 3 parameter template slug
        $billings = $this->collector->collect(now(), $templateName1, $templateName2, $templateName3);

        LogHelper::logInfo(null, [
            'count' => $billings->count()
        ], 110, '[OverdueBroadcast] Billing collected', $traceId);

        if ($billings->isEmpty()) {
            LogHelper::logInfo(null, [], 111, '[OverdueBroadcast] No billing to process', $traceId);
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
            LogHelper::logError(null, [], 120, '[OverdueBroadcast] Gateway/Account invalid', $traceId);
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
        $overrideNumber    = config('messaging.phone_number_override');

        // Ambil timing dasar delay antrean
        $delay = $this->getNextDelay('notification', $traceId);
        $delayInSeconds = 0;

        // ===============================================================
        // PROCESS ENGINE LOOP
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
                'delay_at'      => $delay->toDateTimeString(),
            ], 200, '[OverdueBroadcast] Dispatch job to queue', $traceId);

            // Kirim payload lengkap menuju job antrean background
            OverdueReminderJob::dispatch(
                contact: $recipient,
                customerId: (string) $data->customer_id,
                packageName: $data->package_name,
                totalAmount: $this->currency($data->total),
                lateDays: $lateDays,
                note: 'penghentian layanan internet',
                templateName: $resolvedTemplate,
                account: $account,
                bankPayload: $bankPayload,          // 💡 Parameter Baru
                contactAdmin: $contactAdmin,        // 💡 Parameter Baru
                contactWhatsapp: $contactWhatsapp   // 💡 Parameter Baru
            )
                ->delay($delay)
                ->onQueue('notification');

            // Tambahkan increment jeda rilis antrean agar tidak menumpuk serentak
            $delayInSeconds += 2;
            $delay = now()->addSeconds($delayInSeconds);
        }

        LogHelper::logInfo(null, [], 900, '[OverdueBroadcast] Finished dispatch loop', $traceId);
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

    private function currency(int|float|null $n): string
    {
        return 'Rp. ' . number_format($n ?? 0, 2, ',', '.');
    }
}
