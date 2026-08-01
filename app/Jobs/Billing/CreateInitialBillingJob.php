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
use RuntimeException;
use Throwable;

class CreateInitialBillingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected ?string $periodName = null,
        protected ?Auth $auth = null,
        protected ?string $traceId = null,
    ) {}

    public function handle(BillingService $billingService): void
    {
        $traceId = $this->traceId ?: (string) Str::uuid();

        // 1) Langsung kirim $this->periodName ke service (boleh null)
        $billingPeriode = $billingService->createBillingPeriode($this->periodName, $traceId);

        if (!$billingPeriode) {
            LogHelper::logError(
                null,
                ['periode' => $this->periodName],
                500,
                "Failed to create or retrieve BillingPeriode.",
                $traceId
            );

            throw new RuntimeException("BillingPeriode could not be created.");
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
                "{$totalCreated} billing(s) for {$billingPeriode->name} was successfully created.",
                $traceId
            );
        } catch (Throwable $e) {
            LogHelper::logError(
                $e,
                [
                    'billing_periode_id'   => $billingPeriode->id,
                    'billing_periode_name' => $billingPeriode->name,
                ],
                500,
                "Failed to generate billings for period {$billingPeriode->name}.",
                $traceId
            );

            throw $e;
        }
    }
}
