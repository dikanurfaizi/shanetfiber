<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Models\Billing\BillingPeriode;
use App\Models\Mikrotik;
use App\Models\Billing\Billing;
use App\Models\AutoIsolir;
use App\Services\Billings\PaymentProcessingService;
use App\Services\Billings\PaymentCancellationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use App\Helpers\LogHelper;

class CheckBillingAndUpdateProfileSecretJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */

    private string $traceId;

    public function __construct(string $traceId)
    {
        $this->traceId = $traceId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $latestPeriod = BillingPeriode::latest('created_at')->first();
        $periodeId = $latestPeriod->id;

        $billingsPayment = Billing::whereHas('billingSubscription', function ($query) use ($periodeId) {
            $query->where('billing_periode_id', $periodeId);
        })
            ->where('status', 'LS')
            ->whereDate('payment_time', Carbon::today()) // filter hanya yang dibayar hari ini
            ->get();

        $paymentService = app()->make(PaymentProcessingService::class);

        foreach ($billingsPayment as $billing) {
            // Panggil service dengan billing dan traceId
            $paymentService->processPaymentActions($billing, $this->traceId);
        }

        $billingsUnpayment = Billing::where('status', '!=', 'LS')
            ->whereDate('due_date', '<=', Carbon::today())
            ->whereHas('billingSubscription', function ($q) use ($periodeId) {
                $q->where('billing_periode_id', $periodeId);
            })
            ->get();

        $paymentCancellationService = app()->make(PaymentCancellationService::class);

        // Loop dan panggil cancelPayment
        foreach ($billingsUnpayment as $billing) {
            $result = $paymentCancellationService->cancelPayment($billing, $this->traceId);
            LogHelper::logInfo(null, ['billing_id' => $billing->id, 'result' => $result], 200, "Cancel Payment Result", $this->traceId);
        }
    }
}
