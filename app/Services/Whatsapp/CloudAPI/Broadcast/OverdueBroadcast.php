<?php

namespace App\Services\Whatsapp\CloudAPI\Broadcast;

use App\Helpers\FormatReceiver;
use App\Helpers\LogHelper;
use App\Helpers\MaskCustomerName;
use App\Jobs\Whatsapp\CloudAPI\Broadcast\MidMonthReminderJob;
use App\Jobs\Whatsapp\CloudAPI\Broadcast\OverdueReminderJob;
use App\Models\Whatsapp\WhatsappGateway;
use App\Services\Whatsapp\CloudAPI\Collectors\OverdueBillingCollector;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OverdueBroadcast
{
    public function __construct(private OverdueBillingCollector $collector) {}

    public function run(string $traceId)
    {
        LogHelper::logInfo(null, [], 100, '[OverdueBroadcast] Start', $traceId);
        $templateName = 'pembayaran_terlambat_1';

        // $billings = $this->collector->collect(now(), 'pembayaran_jatuh_tempo_2');
        $billings = $this->collector->collect(now(), $templateName);

        LogHelper::logInfo(null, [
            'count' => $billings->count()
        ], 110, '[OverdueBroadcast] Billing collected', $traceId);
        if ($billings->isEmpty()) {
            LogHelper::logInfo(null, [], 111, '[OverdueBroadcast] No billing to process', $traceId);
            return;
        }

        $formatter = new FormatReceiver;

        $gateway = WhatsappGateway::where('type', 'cloud')
            ->where('is_active', true)
            ->with(['accounts' => function ($q) {
                $q->where('is_active', true);
            }])
            ->first();

        if (!$gateway) {
            LogHelper::logError(null, [], 120, '[OverdueBroadcast] No active gateway found', $traceId);
            return;
        }

        if ($gateway->accounts->isEmpty()) {
            LogHelper::logError(null, [
                'gateway_id' => $gateway->id
            ], 121, '[OverdueBroadcast] No active account found', $traceId);
            return;
        }

        $account = $gateway->accounts->first();

        LogHelper::logInfo(null, [
            'gateway_id' => $gateway->id,
            'account_id' => $account->id
        ], 130, '[OverdueBroadcast] Gateway resolved', $traceId);

        $delay = $this->getNextDelay('notification', $traceId);

        foreach ($billings as $idx => $data) {

            $recipient = $formatter->formatReceiver(
                $data->customer_contact,
                null,
                false
            );

            $lateDays = max(
                Carbon::parse($data->final_due_date)->diffInDays(now(), false),
                0
            );

            LogHelper::logInfo(null, [
                'index'         => $idx,
                'customer_id'   => $data->customer_id,
                'customer_name' => $data->customer_name,
                'recipient'     => $recipient,
                'package'       => $data->package_name,
                'total'         => $this->currency($data->total),
                'delay_at'      => $delay->toDateTimeString(),
            ], 200, '[OverdueBroadcast] Dispatch job', $traceId);


            OverdueReminderJob::dispatch(
                contact: $recipient,
                customerId: $data->customer_id,
                packageName: $data->package_name,
                totalAmount: $this->currency($data->total),
                lateDays: $lateDays,
                note: 'penghentian layanan internet',
                templateName: $templateName,
                account: $account
            )
                ->delay($delay)
                ->onQueue('notification');

            $delay = $delay->addMinute();
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
            LogHelper::logInfo(null, [
                'queue' => $queue
            ], 300, '[OverdueBroadcast] Queue empty, start now', $traceId);

            return now();
        }

        // $delay = Carbon::createFromTimestamp($last->reserved_at)->addMinute();
        $delay = Carbon::createFromTimestamp($last->reserved_at)->addSeconds(1);

        LogHelper::logInfo(null, [
            'queue'        => $queue,
            'reserved_at'  => Carbon::createFromTimestamp($last->reserved_at)->toDateTimeString(),
            'next_delay'   => $delay->toDateTimeString(),
        ], 301, '[OverdueBroadcast] Next delay calculated', $traceId);

        return $delay;
    }

    private function currency(int|float|null $n): string
    {
        return 'Rp. ' . number_format($n ?? 0, 2, ',', '.');
    }
}
