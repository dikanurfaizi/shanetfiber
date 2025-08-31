<?php

namespace App\Jobs\Billing;

use App\Helpers\LogHelper;
use App\Models\Billing\BillingPeriode;
use App\Services\Billings\BillingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
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

        // 1) Parse period safely
        try {
            $periodDate = Carbon::createFromFormat('F Y', $period);
        } catch (\Exception $e) {
            LogHelper::logError(null, ['error' => $e->getMessage()], 400, "Invalid period format: {$period}", $traceId);
            throw $e;
        }

        // 2) Create / retrieve BillingPeriode (no long transaction)
        $billingPeriode = BillingPeriode::firstOrCreate(
            ['name' => $period],
            [
                'month' => $periodDate->format('F'),
                'year'  => $periodDate->format('Y'),
            ]
        );

        LogHelper::logInfo(
            null,
            [
                'billing_periode_id'   => $billingPeriode->id,
                'billing_periode_name' => $billingPeriode->name,
            ],
            200,
            "{$period} ready (id: {$billingPeriode->id}).",
            $traceId
        );

        // 3) Call service (service should manage its own transactions / chunking)
        try {
            $totalCreated = $billingService->generateBillingsForPeriod($billingPeriode->id, $traceId);

            LogHelper::logInfo(
                null,
                ['total_billing_created' => $totalCreated],
                200,
                "{$totalCreated} billing for {$period} was successfully created.",
                $traceId
            );
        } catch (Throwable $e) {
            // service failure -> log and rethrow to allow job retry policy
            LogHelper::logError(
                null,
                ['error' => $e->getMessage()],
                500,
                "Failed to generate billings for period {$period}.",
                $traceId
            );
            throw $e;
        }
    }
}
