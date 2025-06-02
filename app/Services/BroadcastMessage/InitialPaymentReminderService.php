<?php

namespace App\Services\BroadcastMessage;

use App\Models\Billing\BillingPeriode;
use App\Models\WhatsappGateway\WhatsappGateway;
use App\Models\Billing\Billing;
use App\Models\Websystem;
use App\Jobs\SendWhatsappSingleJob;
use App\Helpers\LogHelper;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Exception\RequestException;
use App\Services\WhatsappMessageService;
use Illuminate\Support\Str;

class InitialPaymentReminderService
{
    public function sendReminders(): void
    {
        $traceId = (string) Str::uuid();
        try {
            $gateway = WhatsappGateway::where('disabled', false)->first();
            if (!$gateway) {
                LogHelper::logWarning(null, null, 404, "No active WhatsApp gateway found.", $traceId);
                return;
            }

            $lastBillingPeriode = BillingPeriode::latest('created_at')->first();
            if (!$lastBillingPeriode) {
                LogHelper::logWarning(null, null, 404, "No billing period found.", $traceId);
                return;
            }

            $billings = Billing::query()
                ->join('customers', 'billings.customer_id', '=', 'customers.id')
                ->leftJoin('users', 'customers.user_id', '=', 'users.id')
                ->leftJoin('message_logs', function ($join) {
                    $join->on('billings.customer_id', '=', 'message_logs.customer_id')
                        ->where('message_logs.message_type', '=', 'create_new_bill');
                })
                ->select(
                    'billings.id as billing_id',
                    'billings.billing_number',
                    'billings.customer_id',
                    'customers.customer_id as customer_unique_id',
                    'customers.name as customer_name',
                    'customers.contact as customer_contact',
                    'customers.address as customer_address',
                    'billings.due_date',
                    'users.name as admin_name',
                    'users.id as admin_id',
                    'users.contact as admin_contact',
                    DB::raw('COUNT(billings.id) as total_bills'),
                    DB::raw('SUM(billings.paket_price) as total_amount')
                )
                ->whereNull('message_logs.id')
                ->whereNotNull('customers.contact')
                ->where('customers.contact', '!=', '')
                ->where(function ($query) {
                    $query->where(function ($q) {
                        $q->where('billings.status', 'BL')->whereNotNull('billings.due_date');
                    })->orWhere(function ($q) {
                        $q->where('billings.status', 'PL')->whereNotNull('billings.pay_later');
                    });
                })
                ->groupBy(
                    'billings.id',
                    'billings.billing_number',
                    'billings.customer_id',
                    'customers.customer_id',
                    'customers.name',
                    'customers.contact',
                    'customers.address',
                    'billings.due_date',
                    'users.name',
                    'users.id',
                    'users.contact'
                )
                ->orderBy('customers.name')
                ->limit(10)
                ->get();

            if ($billings->isEmpty()) {
                LogHelper::logWarning(
                    null,
                    ['periode_id' => $lastBillingPeriode->id],
                    404,
                    "No billing data found.",
                    $traceId
                );
                return;
            }

            $messageService = app(WhatsappMessageService::class);
            $websystem = WebSystem::first();
            $count = 0;

            foreach ($billings as $billing) {
                $accountBank = $messageService->getBankInfo($billing->admin_id ?? 1);
                $pakets = $messageService->getBillingPackages($billing->customer_id);
                $admin_contact = $messageService->manipulateContactBillInfo($billing->admin_contact, null);

                if (empty($pakets) || (method_exists($pakets, 'isEmpty') && $pakets->isEmpty())) {
                    LogHelper::logError(null, [
                        'customer_id' => $billing->customer_id,
                        'customer_name' => $billing->customer_name,
                        'contact_customer' => $billing->customer_contact,
                    ], 404, "Paket atau tagihan tidak ditemukan.", $traceId);
                    continue;
                }

                // Format rincian paket
                $paketsText = $pakets->map(fn($paket) => $messageService->formatPaketInfo($paket))->implode("\n---\n");

                $replace = [
                    '%name%'           => $billing->customer_name,
                    '%address%'        => $billing->customer_address,
                    '%customer_id%'    => $billing->customer_unique_id,
                    '%billing_number%' => $billing->billing_number ?? 'Nomor tidak tersedia',
                    '%account_bank%'   => $accountBank,
                    '%whatsapp_number%' => $admin_contact,
                    '%total-bill%'     => 'Rp. ' . number_format($billing->total_amount, 2),
                    '%pakets%'         => $paketsText,
                    '%company_address%' => $websystem->address . ', ' . $websystem->city . ' ' . $websystem->postal_code,
                ];

                $action = 'create_new_bill';
                $messageTemplate = $messageService->getMessageTemplate($action, $pakets, $billing->admin_name);
                $message = $messageService->replaceMessagePlaceholders($messageTemplate, $replace);

                LogHelper::logInfo(null, [
                    'action' => $action,
                    'message_template' => $messageTemplate,
                    'message' => $message,
                ], 200, "Dispatched {$count} WhatsApp messages.", $traceId);

                SendWhatsappSingleJob::dispatch(
                    $billing->billing_id,
                    $action,
                    $gateway->id,
                    $billing->customer_contact,
                    $message
                );

                $count++;
            }

            LogHelper::logInfo(null, [
                'periode_id' => $lastBillingPeriode->id,
                'periode_name' => $lastBillingPeriode->name,
                'count' => $count,
                'gateway_id' => $gateway->id
            ], 200, "Dispatched {$count} WhatsApp messages.", $traceId);
        } catch (RequestException $e) {
            LogHelper::logError(null, [], 500, "HTTP Request Exception: " . $e->getMessage(), $traceId);
        } catch (\Exception $e) {
            LogHelper::logError(null, [], 500, "Exception occurred while creating billing: " . $e->getMessage(), $traceId);
        }
    }
}
