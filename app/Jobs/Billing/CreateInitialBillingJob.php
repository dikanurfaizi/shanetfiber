<?php

namespace App\Jobs\Billing;

use App\Helpers\LogHelper;
use App\Services\Billings\BillingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Throwable;

class CreateInitialBillingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected ?string $periodName = null,
        protected ?Auth $auth = null
    ) {}

    public function handle(BillingService $billingService): void
    {
        $traceId = (string) Str::uuid();
        $period  = $this->periodName ?: now()->format('F Y');

        // 1) Create / get BillingPeriode
        $billingPeriode = $billingService->createBillingPeriode($period, $traceId);

        if (!$billingPeriode) {
            LogHelper::logError(
                null,
                ['periode' => $period],
                500,
                "Failed to create or retrieve BillingPeriode for {$period}.",
                $traceId
            );

            // throw supaya job bisa retry sesuai policy
            throw new \RuntimeException("BillingPeriode could not be created for {$period}");
        }

        // 2) Generate billings for periode
        try {
            $totalCreated = $billingService->generateBillingsForPeriod($billingPeriode, $traceId);

            LogHelper::logInfo(
                null,
                [
                    'billing_periode_id'   => $billingPeriode->id,
                    'billing_periode_name' => $billingPeriode->name,
                    'total_billing_created' => $totalCreated
                ],
                200,
                "{$totalCreated} billing(s) for {$period} was successfully created.",
                $traceId
            );
        } catch (\Throwable $e) {
            // service failure -> log and rethrow to allow job retry policy
            LogHelper::logError(
                null,
                [
                    'billing_periode_id'   => $billingPeriode->id,
                    'billing_periode_name' => $billingPeriode->name,
                    'error'                => $e->getMessage()
                ],
                500,
                "Failed to generate billings for period {$period}.",
                $traceId
            );

            throw $e;
        }
    }
}
