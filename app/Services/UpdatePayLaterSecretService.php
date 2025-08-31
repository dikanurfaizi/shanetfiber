<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use App\Models\AutoIsolir;
use App\Models\Billing\Billing;
use App\Models\Mikrotik;
use App\Helpers\LogHelper; // pastikan file ini ada
use App\Jobs\CancelPaymentAfterDueDateJob;
use App\Jobs\CancelPaymentIpStaticAfterDueDateJob;
use Exception;

class UpdatePayLaterSecretService
{
    public function updatePayLaterSecret(string $traceId): void
    {
        $traceId = (string) Str::uuid();
        $today = Carbon::today();
        $currentDay = $today->format('d');
        $currentMonth = $today->format('m');
        $currentYear = $today->format('Y');

        // 1. Ambil billing yang status PL dan jatuh tempo hari ini
        $billings = Billing::with('customer')
            ->where('status', 'PL')
            ->whereDate('pay_later', $today)
            ->get();

        foreach ($billings as $billing) {
            $customer = $billing->customer;

            if (!$customer) {
                LogHelper::logError(null, [
                    'billing_id' => $billing->id,
                ], 404, 'Customer not found for this billing', $traceId);
                continue;
            }

            LogHelper::logInfo(null, [
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'billing_id' => $billing->id,
            ], 200, 'Starting the secret update process for this customer', $traceId);

            $mikrotikId = $customer->mikrotik_id;
            $router = Mikrotik::find($mikrotikId);
            if (!$router) {
                LogHelper::logError(null, [
                    'customer_id' => $customer->id,
                    'mikrotik_id' => $mikrotikId
                ], 404, 'Mikrotik router not found', $traceId);
                continue;
            }

            $customerDueDate = $billing->pay_later;
            $dueDate = Carbon::parse($customerDueDate);
            $now = Carbon::today();

            if ($now->greaterThanOrEqualTo($dueDate)) {
                $autoIsolir = AutoIsolir::where('mikrotik_id', $mikrotikId)->first();

                if (!$autoIsolir) {
                    LogHelper::logError(null, [
                        'mikrotik_id' => $mikrotikId
                    ], 404, 'AutoIsolir data not found', $traceId);
                    continue;
                }

                $profileIsolir = $autoIsolir->profile_id;
                $comment_unpayment = $autoIsolir->activation_date === 'false'
                    ? $autoIsolir->comment_unpayment
                    : $autoIsolir->comment_unpayment . '_' . $currentDay . '_' . $currentMonth . '_' . $currentYear;

                try {
                    if ($customer->internet_service === 'ppp') {
                        $secretId = $customer->secret_id;
                        $username = $customer->username;
                        $comment = $comment_unpayment;

                        if (!$username || !$secretId) {
                            LogHelper::logError(null, [
                                'customer_id' => $customer->id,
                                'reason' => 'username atau secretId kosong'
                            ], 400, 'PPP: Incomplete customer data', $traceId);
                            continue;
                        }

                        LogHelper::logInfo(null, [
                            'router_ip' => $router->host,
                            'username' => $username,
                            'secret_id' => $secretId,
                            'profile_isolir' => $profileIsolir,
                            'comment' => $comment,
                        ], 200, 'Ready to update secret for cancel payment after due date job', $traceId);

                        dispatch(new CancelPaymentAfterDueDateJob($router, $comment, $profileIsolir, $secretId, $username, $traceId))->onQueue('default');
                    } elseif ($customer->internet_service === 'ip_static') {
                        $simpleque_id = $customer->simpleque_id;
                        $ip_address = $customer->ip_static;
                        $address_list_isolir = $autoIsolir->address_list_isolir;
                        $comment = $customer->username . '_' . $comment_unpayment;

                        LogHelper::logInfo(null, [
                            'router_ip' => $router->host,
                            'profile_isolir' => $profileIsolir,
                            'comment' => $comment,
                        ], 200, 'Ready to update secret for cancel payment ip static after due date job', $traceId);

                        dispatch(new CancelPaymentIpStaticAfterDueDateJob($router, $address_list_isolir, $ip_address, $simpleque_id, $comment, $traceId))->onQueue('default');
                    }

                    LogHelper::logInfo(null, [
                        'customer_id' => $customer->id,
                        'mikrotik_id' => $mikrotikId,
                        'profile_isolir' => $profileIsolir,
                        'comment' => $comment,
                        'service' => $customer->internet_service
                    ], 200, 'UpdatePayLaterSecretJob Successfully done', $traceId);
                } catch (Exception $e) {
                    LogHelper::logError(null, [
                        'customer_id' => $customer->id,
                        'mikrotik_id' => $mikrotikId,
                        'service' => $customer->internet_service,
                        'error' => $e->getMessage()
                    ], 500, 'UpdatePayLaterSecretJob Error', $traceId);
                }
            } else {
                LogHelper::logInfo(null, [
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->name,
                    'mikrotik_id' => $mikrotikId,
                    'due_date' => $customerDueDate,
                    'now' => $now->format('Y-m-d'),
                    'service' => $customer->internet_service
                ], 200, 'Not due yet', $traceId);
            }
        }

        LogHelper::logInfo(null, [], 200, 'UpdatePayLaterSecretJob done with ' . $billings->count() . ' data', $traceId);
    }
}
