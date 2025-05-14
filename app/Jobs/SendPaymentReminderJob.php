<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use App\Models\WhatsappGateway\WhatsappGateway;
use App\Models\Billing\Billing;
use App\Services\WhatsappMessageService;
use Illuminate\Support\Facades\Log;
use App\Helpers\LogHelper;

class SendPaymentReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private WhatsappMessageService $messageService;

    public function __construct()
    {
        $this->messageService = app(WhatsappMessageService::class);
    }

    public function handle(): void
    {
        Carbon::setTimezone('Asia/Jakarta');
        $today = Carbon::today();
        // $allowedDates = [10, 15, 20];

        // if (!in_array($today->day, $allowedDates)) {
        //     LogHelper::logWarning(
        //         null,
        //         null,
        //         422,
        //         "Job will not proceed: date not in allowed list. The allowed list is " . json_encode($allowedDates) . " but today is {$today->format('Y-m-d')}"
        //     );
        //     return;
        // }

        //Log::info("==================== Start Job Send WhatsApp Message Bill Reminder  ====================");

        $gateways = WhatsappGateway::where('disabled', false)->get();

        if ($gateways->isEmpty()) {
            LogHelper::logWarning(
                null,
                null,
                404,
                "No active gateways found"
            );
            return;
        }

        foreach ($gateways as $gateway) {
            Log::info("Processing gateway: {$gateway->name}, remaining days: {$gateway->remaining_day}");

            $billings = [
                'past_due' => $this->getBillings($today, 'past_due'),
                'due_today' => $this->getBillings($today, 'due_today'),
                'upcoming' => $this->getBillings($today, 'upcoming')
            ];

            foreach ($billings as $type => $billingList) {
                $messageType = $this->getMessageType($type);
                $this->dispatchMessages($billingList, $messageType, $gateway);
            }
        }
    }

    private function dispatchMessages($billings, $messageType, $gateway)
    {
        $delaySeconds = 0;

        foreach ($billings as $billing) {
            $delaySeconds += 2; // Tambah delay 2 detik untuk setiap pesan

            SendWhatsappSingleJob::dispatch($billing, $messageType, $gateway)
                ->delay(now()->addSeconds($delaySeconds));
        }
    }

    private function getMessageType($billingType)
    {
        return match ($billingType) {
            'past_due' => 'warning_bill',
            'due_today' => 'deadline_bill',
            'upcoming' => 'reminder_bill',
            default => 'reminder_bill'
        };
    }

    private function getBillings($today, $type)
    {
        switch ($type) {
            case 'past_due':
                return Billing::query()
                    ->join('customers', 'billings.customer_id', '=', 'customers.id')
                    ->leftJoin('users', 'customers.user_id', '=', 'users.id')  // LEFT JOIN ke tabel users
                    ->select(
                        'billings.customer_id',
                        'billings.billing_number as billing_number',
                        'customers.customer_id as customer_unique_id',
                        'customers.name as customer_name',
                        'customers.contact as customer_contact',
                        'customers.address as customer_address',
                        'users.id as admin_id',
                        'users.name as admin_name',
                        'users.contact as admin_contact'
                    )
                    ->whereNotNull('customers.contact')
                    ->where('customers.contact', '!=', '')
                    ->where(function ($query) use ($today) {
                        $query->where('billings.status', 'BL')
                            ->whereNotNull('billings.due_date')
                            ->whereDate('billings.due_date', '<', $today)
                            ->orWhere(function ($q) use ($today) {
                                $q->where('billings.status', 'PL')
                                    ->whereNotNull('billings.pay_later')
                                    ->whereDate('billings.pay_later', '<', $today);
                            });
                    })
                    ->groupBy(
                        'billings.customer_id',
                        'customers.customer_id',
                        'billings.billing_number',
                        'customers.name',
                        'customers.contact',
                        'customers.address',
                        'users.id',
                        'users.name',
                        'users.contact'
                    )
                    ->cursor();

            case 'due_today':
                return Billing::query()
                    ->join('customers', 'billings.customer_id', '=', 'customers.id')
                    ->leftJoin('users', 'customers.user_id', '=', 'users.id')  // LEFT JOIN ke tabel users
                    ->select(
                        'billings.customer_id',
                        'billings.billing_number as billing_number',
                        'customers.customer_id as customer_unique_id',
                        'customers.name as customer_name',
                        'customers.contact as customer_contact',
                        'customers.address as customer_address',
                        'users.id as admin_id',
                        'users.name as admin_name',
                        'users.contact as admin_contact'
                    )
                    ->whereNotNull('customers.contact')
                    ->where('customers.contact', '!=', '')
                    ->where(function ($query) use ($today) {
                        $query->where('billings.status', 'BL')
                            ->whereNotNull('billings.due_date')
                            ->whereDate('billings.due_date', $today)
                            ->orWhere(function ($q) use ($today) {
                                $q->where('billings.status', 'PL')
                                    ->whereNotNull('billings.pay_later')
                                    ->whereDate('billings.pay_later', $today);
                            });
                    })
                    ->groupBy(
                        'billings.customer_id',
                        'customers.customer_id',
                        'billings.billing_number',
                        'customers.name',
                        'customers.contact',
                        'customers.address',
                        'users.id',
                        'users.name',
                        'users.contact'
                    )
                    ->cursor();

            case 'upcoming':
                return Billing::query()
                    ->join('customers', 'billings.customer_id', '=', 'customers.id')
                    ->leftJoin('users', 'customers.user_id', '=', 'users.id')  // LEFT JOIN ke tabel users
                    ->select(
                        'billings.customer_id',
                        'billings.billing_number as billing_number',
                        'customers.customer_id as customer_unique_id',
                        'customers.name as customer_name',
                        'customers.contact as customer_contact',
                        'customers.address as customer_address',
                        'users.id as admin_id',
                        'users.name as admin_name',
                        'users.contact as admin_contact'
                    )
                    ->whereNotNull('customers.contact')
                    ->where('customers.contact', '!=', '')
                    ->where(function ($query) use ($today) {
                        $query->where('billings.status', 'BL')
                            ->whereNotNull('billings.due_date')
                            ->whereDate('billings.due_date', '>', $today)
                            ->orWhere(function ($q) use ($today) {
                                $q->where('billings.status', 'PL')
                                    ->whereNotNull('billings.pay_later')
                                    ->whereDate('billings.pay_later', '>', $today);
                            });
                    })
                    ->groupBy(
                        'billings.customer_id',
                        'customers.customer_id',
                        'billings.billing_number',
                        'customers.name',
                        'customers.contact',
                        'customers.address',
                        'users.id',
                        'users.name',
                        'users.contact'
                    )
                    ->cursor();

            default:
                return collect(); // Return empty collection if type is not valid
        }
    }
}
