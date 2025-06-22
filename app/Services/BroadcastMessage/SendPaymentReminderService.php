<?php

namespace App\Services\BroadcastMessage;

use Carbon\Carbon;
use App\Models\WhatsappGateway\WhatsappGateway;
use App\Models\Billing\Billing;
use App\Models\MessageLog;
use App\Helpers\FormatReceiver;
use App\Helpers\LogHelper;
use App\Services\WhatsappMessageService;
use Illuminate\Support\Str;

class SendPaymentReminderService
{
    private FormatReceiver $formatter;

    public function __construct(
        protected WhatsappMessageService $messageService
    ) {
        $this->formatter = new FormatReceiver();
    }

    public function sendReminders(): void
    {
        $traceId = (string) Str::uuid();
        $today = Carbon::today();

        $gateways = WhatsappGateway::where('disabled', false)->get();

        if ($gateways->isEmpty()) {
            LogHelper::logWarning(null, null, 404, "No active gateways found", $traceId);
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
                LogHelper::logInfo(null, [], 200, "Found {$count} billing(s) for type '{$type}' on gateway: {$gateway->name}", $traceId);

                $messageType = $this->getMessageType($type);
                foreach ($billingList as $billing) {
                    $this->checkSendMessage($billing, $messageType, $gateway, $traceId);
                }
            }
        }
    }

    private function checkSendMessage($billing, $messageType, $gateway, $traceId)
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
            ], 409, "Skipping redundant message for {$billing->customer_name} with type {$messageType}.", $traceId);
            return;
        }

        // LogHelper::logInfo(null, 200, json_encode(['billing' => $billing->toArray()]), 'Informasi Billing', $traceId);

        $message = $messageService->bill_info($billing, $messageType, $traceId);

        if ($message) {
            try {
                $this->messageService->sendWhatsappSingleJob(
                    $billing->customer_id,
                    $billing->customer_name,
                    $billing->customer_contact,
                    $gateway,
                    $message,
                    $messageType,
                    $traceId,
                    null
                );
            } catch (\Exception $e) {
                LogHelper::logError(null, [
                    'billing_id'     => $billing->id ?? null,
                    'customer_id'    => $billing->customer_id ?? null,
                    'customer_name'  => $billing->customer_name ?? null,
                    'message_type'   => $messageType,
                    'error_message'  => $e->getMessage(),
                ], 500, "Exception while sending WhatsApp message.", $traceId);
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
        $messageType = $this->getMessageType($type);

        $baseQuery = Billing::query()
            ->join('customers', 'billings.customer_id', '=', 'customers.id')
            ->leftJoin('users', 'customers.user_id', '=', 'users.id')
            ->leftJoin('message_logs', function ($join) use ($today, $messageType) {
                $join->on('customers.id', '=', 'message_logs.customer_id')
                    ->whereDate('message_logs.created_at', '=', $today)
                    ->where('message_logs.message_type', '=', $messageType);
            })
            ->leftJoin('message_log_errors', function ($join) use ($today, $messageType) {
                $join->on('customers.id', '=', 'message_log_errors.customer_id')
                    ->whereDate('message_log_errors.created_at', '=', $today)
                    ->where('message_log_errors.message_type', '=', $messageType);
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
            ->whereNull('message_logs.id')        // belum sukses kirim
            ->whereNull('message_log_errors.id');

        switch ($type) {
            case 'past_due':
                $baseQuery->where(function ($query) use ($today) {
                    $query->where(function ($q) use ($today) {
                        $q->where('billings.status', 'BL')
                            ->whereNotNull('billings.due_date')
                            ->whereDate('billings.due_date', '<', $today);
                    })->orWhere(function ($q) use ($today) {
                        $q->where('billings.status', 'PL')
                            ->whereNotNull('billings.pay_later')
                            ->whereDate('billings.pay_later', '<', $today);
                    });
                });
                break;

            case 'due_today':
                $baseQuery->where(function ($query) use ($today) {
                    $query->where(function ($q) use ($today) {
                        $q->where('billings.status', 'BL')
                            ->whereNotNull('billings.due_date')
                            ->whereDate('billings.due_date', '=', $today);
                    })->orWhere(function ($q) use ($today) {
                        $q->where('billings.status', 'PL')
                            ->whereNotNull('billings.pay_later')
                            ->whereDate('billings.pay_later', '=', $today);
                    });
                });
                break;

            case 'upcoming':
                $baseQuery->where(function ($query) use ($today) {
                    $query->where(function ($q) use ($today) {
                        $q->where('billings.status', 'BL')
                            ->whereNotNull('billings.due_date')
                            ->whereDate('billings.due_date', '>', $today);
                    })->orWhere(function ($q) use ($today) {
                        $q->where('billings.status', 'PL')
                            ->whereNotNull('billings.pay_later')
                            ->whereDate('billings.pay_later', '>', $today);
                    });
                });
                break;

            default:
                return collect();
        }

        return $baseQuery
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
            ->limit(20)
            ->get();
    }
}
