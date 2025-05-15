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
        $today = Carbon::today();

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
            $billings = [
                'past_due' => $this->getBillings($today, 'past_due'),
                'due_today' => $this->getBillings($today, 'due_today'),
                'upcoming' => $this->getBillings($today, 'upcoming')
            ];

            foreach ($billings as $type => $billingList) {
                $count = count($billingList);
                LogHelper::logInfo(null, [], 200, "Found {$count} billing(s) for type '{$type}' on gateway: {$gateway->name}");

                $messageType = $this->getMessageType($type);
                $this->dispatchMessages($billingList, $messageType, $gateway);
            }
        }
    }

    private function dispatchMessages($billings, $messageType, $gateway)
    {
        foreach ($billings as $billing) {
            SendWhatsappSingleJob::dispatch(
                $billing->billing_id,
                $messageType,
                $gateway->id
            );
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
                    ->leftJoin('message_logs', function ($join) use ($today) {
                        $join->on('customers.id', '=', 'message_logs.customer_id')
                            ->whereDate('message_logs.created_at', '=', $today);
                    })
                    ->select(
                        'billings.id as billing_id',
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
                    ->whereNull('message_logs.id') // hanya yang belum terkirim hari ini
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
                        'billings.id',
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
                    ->limit(10)
                    ->get();

            case 'due_today':
                return Billing::query()
                    ->join('customers', 'billings.customer_id', '=', 'customers.id')
                    ->leftJoin('users', 'customers.user_id', '=', 'users.id')  // LEFT JOIN ke tabel users
                    ->leftJoin('message_logs', function ($join) use ($today) {
                        $join->on('customers.id', '=', 'message_logs.customer_id')
                            ->whereDate('message_logs.created_at', '=', $today);
                    })
                    ->select(
                        'billings.id as billing_id',
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
                    ->whereNull('message_logs.id') // hanya yang belum terkirim hari ini
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
                        'billings.id',
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
                    ->limit(10)
                    ->get();

            case 'upcoming':
                return Billing::query()
                    ->join('customers', 'billings.customer_id', '=', 'customers.id')
                    ->leftJoin('users', 'customers.user_id', '=', 'users.id')
                    ->leftJoin('message_logs', function ($join) use ($today) {
                        $join->on('customers.id', '=', 'message_logs.customer_id')
                            ->whereDate('message_logs.created_at', '=', $today);
                    })
                    ->select(
                        'billings.id as billing_id',
                        'billings.customer_id',
                        'billings.billing_number',
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
                    ->whereNull('message_logs.id') // hanya yang belum terkirim hari ini
                    ->where(function ($query) use ($today) {
                        $query->where(function ($q) use ($today) {
                            $q->where('billings.status', 'BL')
                                ->whereNotNull('billings.due_date')
                                ->whereDate('billings.due_date', '>', $today);
                        })
                            ->orWhere(function ($q) use ($today) {
                                $q->where('billings.status', 'PL')
                                    ->whereNotNull('billings.pay_later')
                                    ->whereDate('billings.pay_later', '>', $today);
                            });
                    })
                    ->groupBy(
                        'billings.id',
                        'billings.customer_id',
                        'billings.billing_number',
                        'customers.customer_id',
                        'customers.name',
                        'customers.contact',
                        'customers.address',
                        'users.id',
                        'users.name',
                        'users.contact'
                    )
                    ->limit(10)
                    ->get();

            default:
                return collect(); // Return empty collection if type is not valid
        }
    }
}
