<?php

namespace App\Services\BroadcastMessage;

use Carbon\Carbon;
use App\Models\WhatsappGateway\WhatsappGateway;
use App\Models\Billing\Billing;
use App\Models\MessageLog;
use App\Jobs\SendWhatsappSingleJob;
use App\Helpers\LogHelper;
use App\Services\WhatsappMessageService;

class SendPaymentReminderService
{
    public function sendReminders(): void
    {
        $today = Carbon::today();

        $gateways = WhatsappGateway::where('disabled', false)->get();

        if ($gateways->isEmpty()) {
            LogHelper::logWarning(null, null, 404, "No active gateways found");
            return;
        }

        foreach ($gateways as $gateway) {
            $billings = [
                'past_due' => $this->getBillings($today, 'past_due'),
                'due_today' => $this->getBillings($today, 'due_today'),
                'upcoming' => $this->getBillings($today, 'upcoming')
            ];

            foreach ($billings as $type => $billingList) {
                $count = $billingList->count();
                LogHelper::logInfo(null, [], 200, "Found {$count} billing(s) for type '{$type}' on gateway: {$gateway->name}");

                $messageType = $this->getMessageType($type);
                foreach ($billingList as $billing) {
                    $this->checkSendMessage($billing, $messageType, $gateway);
                }
            }
        }
    }

    private function checkSendMessage($billing, $messageType, $gateway)
    {
        $messageService = app(WhatsappMessageService::class);

        // Skip jika sudah pernah dikirim hari ini
        $alreadySent = MessageLog::where('customer_id', $billing->customer_id)
            ->where('message_type', $messageType)
            ->whereDate('created_at', Carbon::today())
            ->exists();

        if ($alreadySent) {
            LogHelper::logWarning(null, [
                'customer_name' => $billing->customer_name,
                'message_type'  => $messageType,
            ], 409, "Skipping redundant message for {$billing->customer_name} with type {$messageType}.");
            return;
        }

        $message = $messageService->bill_info($billing, $messageType);

        if ($message) {
            try {
                SendWhatsappSingleJob::dispatch(
                    $billing->billing_id,
                    $messageType,
                    $gateway->id,
                    $billing->customer->contact,
                    $message
                );
            } catch (\Exception $e) {
                LogHelper::logError(null, [
                    'billing_id'     => $billing->id ?? null,
                    'customer_id'    => $billing->customer_id ?? null,
                    'customer_name'  => $billing->customer_name ?? null,
                    'message_type'   => $messageType,
                    'error_message'  => $e->getMessage(),
                ], 500, "Exception while sending WhatsApp message.");
            }
        }
    }

    private function getMessageType(string $billingType): string
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
