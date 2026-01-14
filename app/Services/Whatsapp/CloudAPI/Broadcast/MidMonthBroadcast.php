<?php

namespace App\Services\Whatsapp\CloudAPI\Broadcast;

use App\Helpers\FormatReceiver;
use App\Helpers\LogHelper;
use App\Jobs\Whatsapp\CloudAPI\Broadcast\MidMonthReminderJob;
use App\Models\Whatsapp\WhatsappGateway;
use App\Services\Whatsapp\CloudAPI\Collectors\BillingReminderCollector;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MidMonthBroadcast
{
    public function __construct(private BillingReminderCollector $collector) {}

    public function run(string $traceId)
    {
        LogHelper::logInfo(null, [], 100, '[MidMonthBroadcast] Start', $traceId);

        $billings = $this->collector->collect(now(), 'payment_due_reminder');

        LogHelper::logInfo(null, [
            'count' => $billings->count()
        ], 110, '[MidMonthBroadcast] Billing collected', $traceId);

        if ($billings->isEmpty()) {
            LogHelper::logInfo(null, [], 111, '[MidMonthBroadcast] No billing to process', $traceId);
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
            LogHelper::logError(null, [], 120, '[MidMonthBroadcast] No active gateway found', $traceId);
            return;
        }

        if ($gateway->accounts->isEmpty()) {
            LogHelper::logError(null, [
                'gateway_id' => $gateway->id
            ], 121, '[MidMonthBroadcast] No active account found', $traceId);
            return;
        }

        $account = $gateway->accounts->first();

        LogHelper::logInfo(null, [
            'gateway_id' => $gateway->id,
            'account_id' => $account->id
        ], 130, '[MidMonthBroadcast] Gateway resolved', $traceId);

        $delay = $this->getNextDelay('notification', $traceId);

        foreach ($billings as $idx => $data) {

            $recipient = $formatter->formatReceiver(
                $data->customer_contact,
                null,
                false
            );

            $due_date_formatted = $data->final_due_date
                ? Carbon::parse($data->final_due_date)->translatedFormat('d F Y')
                : '-';

            LogHelper::logInfo(null, [
                'index'         => $idx,
                'customer_id'   => $data->customer_id,
                'customer_name' => $data->customer_name,
                'recipient'     => $recipient,
                'package'       => $data->package_name,
                'total'         => $this->currency($data->total),
                'due_date'      => $due_date_formatted,
                'delay_at'      => $delay->toDateTimeString(),
            ], 200, '[MidMonthBroadcast] Dispatch job', $traceId);

            MidMonthReminderJob::dispatch(
                contact: $recipient,
                customerId: $data->customer_id,
                customerName: $data->customer_name,
                packageName: $data->package_name,
                totalAmount: $this->currency($data->total),
                dueDate: $due_date_formatted,
                account: $account
            )
                ->delay($delay)
                ->onQueue('notification');

            $delay = $delay->addMinute();
        }

        LogHelper::logInfo(null, [], 900, '[MidMonthBroadcast] Finished dispatch loop', $traceId);
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
            ], 300, '[MidMonthBroadcast] Queue empty, start now', $traceId);

            return now();
        }

        // $delay = Carbon::createFromTimestamp($last->reserved_at)->addMinute();
        $delay = Carbon::createFromTimestamp($last->reserved_at)->addSeconds(1);

        LogHelper::logInfo(null, [
            'queue'        => $queue,
            'reserved_at'  => Carbon::createFromTimestamp($last->reserved_at)->toDateTimeString(),
            'next_delay'   => $delay->toDateTimeString(),
        ], 301, '[MidMonthBroadcast] Next delay calculated', $traceId);

        return $delay;
    }

    private function currency(int|float|null $n): string
    {
        return 'Rp. ' . number_format($n ?? 0, 2, ',', '.');
    }
}
