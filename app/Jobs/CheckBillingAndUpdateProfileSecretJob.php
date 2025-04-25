<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Models\Billing\BillingPeriode;
use App\Models\Mikrotik;
use App\Models\Paket;
use App\Models\AutoIsolir;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class CheckBillingAndUpdateProfileSecretJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct() {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $latestPeriod = BillingPeriode::latest('created_at')->first();
        $periodeId = $latestPeriod->id;
        $periodeName = $latestPeriod->name;

        // Pelanggan yang lunas (status LS)
        $customers = Customer::whereHas('billing', function ($q) use ($periodeId) {
            $q->where('billing_periode_id', $periodeId)
                ->where('status', 'LS');
        })->get();

        foreach ($customers as $customer) {
            try {
                $router = Mikrotik::find($customer->mikrotik_id);
                $customerProfile = Paket::where('id', $customer->paket_id)->value('profile');
                $comment_payment = 'Auto update profile after LS';

                dispatch(new AddPaymentAfterDueDateJob(
                    $router,
                    $customerProfile,
                    $comment_payment,
                    $customer->secret_id,
                    $customer->username
                ))->onQueue('default');

                Log::info('Job AddPaymentAfterDueDateJob dispatched for customer: ' . $customer->username);
            } catch (\Exception $e) {
                Log::error('Failed to dispatch AddPaymentAfterDueDateJob for customer: ' . $customer->username . ' Error: ' . $e->getMessage());
            }
        }

        // Pelanggan belum lunas dan sudah jatuh tempo
        $today = Carbon::today();

        $customersUnpayment = Customer::whereHas('billing', function ($q) use ($periodeId, $today) {
            $q->where('billing_periode_id', $periodeId)
                ->where('status', '!=', 'LS')
                ->whereDate('due_date', '<=', $today);
        })->get();

        foreach ($customersUnpayment as $customer) {
            try {
                $autoIsolir = AutoIsolir::where('mikrotik_id', $customer->mikrotik_id)->first();
                $router = Mikrotik::find($customer->mikrotik_id);
                $date = Carbon::parse($customer->activation_date)->day;
                $periode = Carbon::parse($periodeName);
                $month = $periode->month;
                $year = $periode->year;

                if ($autoIsolir) {
                    $profileIsolir = $autoIsolir->profile_id;
                    $comment_unpayment = $autoIsolir->activation_date == 'false'
                        ? $autoIsolir->comment_unpayment
                        : "{$autoIsolir->comment_unpayment}_{$date}_{$month}_{$year}";

                    dispatch(new CancelPaymentAfterDueDateJob(
                        $router,
                        $comment_unpayment,
                        $profileIsolir,
                        $customer->secret_id,
                        $customer->username
                    ))->onQueue('default');

                    Log::info('Job CancelPaymentAfterDueDateJob dispatched for customer: ' . $customer->username);
                } else {
                    Log::warning('AutoIsolir not found for customer: ' . $customer->username);
                }
            } catch (\Exception $e) {
                Log::error('Failed to dispatch CancelPaymentAfterDueDateJob for customer: ' . $customer->username . ' Error: ' . $e->getMessage());
            }
        }
    }
}
