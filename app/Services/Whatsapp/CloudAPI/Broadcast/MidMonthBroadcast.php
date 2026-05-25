<?php

namespace App\Services\Whatsapp\CloudAPI\Broadcast;

use App\Helpers\FormatReceiver;
use App\Helpers\LogHelper;
use App\Helpers\MaskCustomerName;
use App\Jobs\Whatsapp\CloudAPI\Broadcast\MidMonthReminderJob; // 💡 Pastikan terpanggil
use App\Models\Whatsapp\WhatsappGateway;
use App\Services\Whatsapp\CloudAPI\Collectors\BillingReminderCollector;
use App\Services\Whatsapp\CloudAPI\Collectors\AdminBankCollector;
use App\Services\Whatsapp\CloudAPI\Messages\TemplateMessageService;
use App\Services\Whatsapp\CloudAPI\Broadcast\Traits\MapsBroadcastBankPayload;
use App\Services\Whatsapp\CloudAPI\Broadcast\Traits\ResolvesAdminContact;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MidMonthBroadcast
{
    use MapsBroadcastBankPayload, ResolvesAdminContact;

    public function __construct(
        private BillingReminderCollector $collector,
        private TemplateMessageService $templateMessageService,
        private AdminBankCollector $bankCollector
    ) {}

    public function run(string $traceId)
    {
        LogHelper::logInfo(null, [], 100, '[MidMonthBroadcast] Start', $traceId);

        $templateName1 = 'pengingat_pembayaran_v3';    // 2 Bank + 1 E-Wallet
        $templateName2 = 'pengingat_pembayaran_v2_2';  // 1 Bank + 1 E-Wallet
        $templateName3 = 'pengingat_pembayaran_v2_1';  // Hanya 2 Bank

        $billings = $this->collector->collect(now(), $templateName1, $templateName2, $templateName3);

        LogHelper::logInfo(null, [
            'count' => $billings->count()
        ], 110, '[MidMonthBroadcast] Billing collected', $traceId);

        if ($billings->isEmpty()) {
            LogHelper::logInfo(null, [], 111, '[MidMonthBroadcast] No billing to process', $traceId);
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
            LogHelper::logError(null, [], 120, '[MidMonthBroadcast] Gateway/Account invalid', $traceId);
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

        // ===============================================================
        // PREPARE QUEUE TIMING DELAY
        // ===============================================================
        $delayInSeconds = 0;
        $delay = $this->getNextDelay('notification', $traceId);

        // ===============================================================
        // PROCESS ENGINE LOOP
        // ===============================================================
        foreach ($billings as $idx => $data) {

            $adminBanks = $this->bankCollector->getBanksByUserId($data->user_id ?? 0);
            $banks      = $adminBanks->where('type', 'bank')->values();
            $ewallets   = $adminBanks->where('type', 'ewallet')->values();

            // Panggil Trait untuk dapati Template & Payload Bank secara otomatis
            $bankResolution = $this->resolveBankTemplateAndPayload($banks, $ewallets, [
                $templateName1,
                $templateName2,
                $templateName3
            ]);

            $resolvedTemplate = $bankResolution['template'];
            $bankPayload      = $bankResolution['payload'];

            $recipient = $formatter->formatReceiver($data->customer_contact, null, false);

            $due_date_formatted = $data->final_due_date
                ? Carbon::parse($data->final_due_date)->translatedFormat('d F Y')
                : '-';

            $accountData = MaskCustomerName::buildAccountData($data->customer_name, $data->package_name);

            // 💡 5. OPER SELLER / INJECT KE JOB ANTRIAN
            MidMonthReminderJob::dispatch(
                account: $account,
                customerId: (string) $data->customer_id,
                customerName: $data->customer_name,
                packageName: $data->package_name,
                accountData: $accountData,
                totalAmount: $this->currency($data->total),
                dueDate: $due_date_formatted,
                note: 'gangguan layanan internet',
                templateName: $resolvedTemplate,
                contact: $recipient,
                bankPayload: $bankPayload,          // 💡 Parameter baru untuk Trait Bank
                contactAdmin: $contactAdmin,        // 💡 Parameter baru untuk Admin Contact
                contactWhatsapp: $contactWhatsapp   // 💡 Parameter baru untuk WA Link Admin
            )
                ->delay($delay)
                ->onQueue('notification');

            // Naikkan delay 2 detik per iterasi data agar merata dan aman dari rate limit
            $delayInSeconds += 2;
            $delay = now()->addSeconds($delayInSeconds);
        }

        LogHelper::logInfo(null, [], 900, '[MidMonthBroadcast] Finished dispatch to Queue', $traceId);
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
