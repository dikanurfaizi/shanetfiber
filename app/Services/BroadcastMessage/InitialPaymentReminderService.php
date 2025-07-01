<?php

namespace App\Services\BroadcastMessage;

use App\Models\Billing\{BillingPeriode, Billing};
use App\Models\{Websystem};
use App\Models\WhatsappGateway\WhatsappGateway;
use App\Helpers\LogHelper;
use App\Services\WhatsappMessageService;
use App\Helpers\FormatReceiver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use GuzzleHttp\Exception\RequestException;
use Carbon\Carbon;

class InitialPaymentReminderService
{
    private FormatReceiver $formatter;

    public function __construct(
        protected WhatsappMessageService $messageService
    ) {
        $this->formatter = new FormatReceiver();
    }

    public function sendReminders(Carbon $startDate, Carbon $endDate): void
    {
        $traceId = (string) Str::uuid();

        try {
            $gateway = $this->getActiveGateway($traceId);
            if (!$gateway) return;

            $periode = $this->getLastBillingPeriod($traceId);
            if (!$periode) return;

            $billings = $this->getEligibleBillings($startDate, $endDate);
            if ($billings->isEmpty()) {
                LogHelper::logWarning(null, ['periode_id' => $periode->id], 404, "No billing data found.", $traceId);
                return;
            }

            $websystem = Websystem::first();
            $count = 0;

            foreach ($billings as $billing) {
                $success = $this->prepareAndSendMessage($billing, $gateway, $websystem, $traceId);
                LogHelper::logInfo(null, [
                    'action' => 'create_new_bill',
                    'periode_id' => $periode->id,
                    'periode_name' => $periode->name,
                ], $success ? 200 : 500, ($success ? "Successfully" : "Failed") . " Dispatched {$count} WhatsApp messages.", $traceId);
                $count++;
            }

            LogHelper::logInfo(null, [
                'periode_id'   => $periode->id,
                'periode_name' => $periode->name,
                'count'        => $count,
                'gateway_id'   => $gateway->id
            ], 200, "Dispatched {$count} WhatsApp messages.", $traceId);
        } catch (RequestException $e) {
            LogHelper::logError(null, [], 500, "HTTP Request Exception: " . $e->getMessage(), $traceId);
        } catch (\Exception $e) {
            LogHelper::logError(null, [], 500, "Exception occurred while creating billing: " . $e->getMessage(), $traceId);
        }
    }

    private function getActiveGateway(string $traceId): ?WhatsappGateway
    {
        $gateway = WhatsappGateway::whereDisabled(false)->first();
        if (!$gateway) {
            LogHelper::logWarning(null, null, 404, "No active WhatsApp gateway found.", $traceId);
        }
        return $gateway;
    }

    private function getLastBillingPeriod(string $traceId): ?BillingPeriode
    {
        $periode = BillingPeriode::latest('created_at')->first();
        if (!$periode) {
            LogHelper::logWarning(null, null, 404, "No billing period found.", $traceId);
        }
        return $periode;
    }

    private function getEligibleBillings(Carbon $startDate, Carbon $endDate): Collection
    {
        $billingPeriodeId = BillingPeriode::where('name', $startDate->format('F Y'))
            ->latest('id')->value('id');

        if (!$billingPeriodeId) {
            return collect();
        }

        return Billing::query()
            ->select([
                'billings.id   as billing_id',
                'billings.billing_number',
                'billings.customer_id',
                'customers.customer_id      as customer_unique_id',
                'customers.name             as customer_name',
                'customers.contact          as customer_contact',
                'customers.address          as customer_address',
                'billings.due_date',
                'users.name  as admin_name',
                'users.id    as admin_id',
                'users.contact as admin_contact',
                DB::raw('COUNT(*)  as total_bills'),
                DB::raw('SUM(billings.paket_price) as total_amount'),
            ])
            ->join('customers', 'billings.customer_id', '=', 'customers.id')
            ->leftJoin('users', 'customers.user_id', '=', 'users.id')

            /* === batasi hanya LOG tanggal 1–3 bulan ini ====================== */
            ->leftJoin('message_logs as ml', function ($join) use ($startDate, $endDate) {
                $join->on('billings.customer_id', '=', 'ml.customer_id')
                    ->where('ml.message_type', '=', 'create_new_bill')
                    ->whereBetween('ml.created_at', [$startDate, $endDate]);
            })
            ->leftJoin('message_log_errors as me', function ($join) use ($startDate, $endDate) {
                $join->on('billings.customer_id', '=', 'me.customer_id')
                    ->where('me.message_type', '=', 'create_new_bill')
                    ->whereBetween('me.created_at', [$startDate, $endDate]);
            })
            /* ================================================================= */

            ->whereNull('ml.id')
            ->whereNull('me.id')
            ->whereNotNull('customers.contact')
            ->where('customers.contact', '!=', '')
            ->where('billings.billing_periode_id', $billingPeriodeId)
            ->where(function ($q) {
                $q->where(fn($q1) => $q1->where('billings.status', 'BL')
                    ->whereNotNull('billings.due_date'))
                    ->orWhere(fn($q2) => $q2->where('billings.status', 'PL')
                        ->whereNotNull('billings.pay_later'));
            })
            ->groupBy([
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
                'users.contact',
            ])
            ->orderBy('customers.name')
            ->limit(20)
            ->get();
    }

    private function prepareAndSendMessage($billing, $gateway, $websystem, string $traceId): bool
    {
        $accountBank = $this->messageService->getBankInfo($billing->admin_id ?? 1);
        $pakets = $this->messageService->getBillingPackages($billing->customer_id);
        $admin_contact = $this->formatter->formatReceiver($billing->admin_contact, null);

        if (empty($pakets) || (method_exists($pakets, 'isEmpty') && $pakets->isEmpty())) {
            LogHelper::logError(null, [
                'customer_id'      => $billing->customer_id,
                'customer_name'    => $billing->customer_name,
                'contact_customer' => $billing->customer_contact,
            ], 404, "Paket atau tagihan tidak ditemukan.", $traceId);
            return false;
        }

        $paketsText = $pakets->map(fn($paket) => $this->messageService->formatPaketInfo($paket))->implode("\n---\n");

        $replace = [
            '%name%'            => $billing->customer_name,
            '%address%'         => $billing->customer_address,
            '%customer_id%'     => $billing->customer_unique_id,
            '%billing_number%'  => $billing->billing_number ?? 'Nomor tidak tersedia',
            '%account_bank%'    => $accountBank,
            '%whatsapp_number%' => $admin_contact,
            '%total-bill%'      => 'Rp. ' . number_format($billing->total_amount, 2),
            '%pakets%'          => $paketsText,
            '%company_address%' => $websystem->address . ', ' . $websystem->city . ' ' . $websystem->postal_code,
        ];

        $action = 'create_new_bill';
        $messageTemplate = $this->messageService->getMessageTemplate($action, $pakets, $billing->admin_name);
        $message = $this->messageService->replaceMessagePlaceholders($messageTemplate, $replace);

        return $this->messageService->sendWhatsappSingleJob(
            $billing->customer_id,
            $billing->customer_name,
            $billing->customer_contact,
            $gateway,
            $message,
            $action,
            $traceId,
            null
        );
    }
}
