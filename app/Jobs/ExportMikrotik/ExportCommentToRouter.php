<?php

namespace App\Jobs\ExportMikrotik;

use App\Helpers\LogHelper;
use App\Models\Billings\Billing;
use App\Models\Billings\BillingPeriode;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use App\Helpers\ExecutionTimer;
use App\Services\RouterCommentSyncService as ServicesRouterCommentSyncService;

class ExportCommentToRouter implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $traceId;
    protected int $mikrotikId;

    public function __construct(int $mikrotikId, string $traceId)
    {
        $this->mikrotikId = $mikrotikId;
        $this->traceId = $traceId;
    }

    public function handle(ServicesRouterCommentSyncService $syncService): void
    {
        $timer = new ExecutionTimer();
        $latestPeriod = BillingPeriode::where('status', 'active')->first();

        if (!$latestPeriod) {
            LogHelper::logError(null, [], "Active billing period not found", $this->traceId, $timer->lap());
            return;
        }

        $periodeId = $latestPeriod->id;
        $today = Carbon::today();
        $targetMikrotikId = $this->mikrotikId; // Disimpan ke variabel untuk di-use di dalam closure

        // 1. Ambil data Billing yang LUNAS (LS) berdasarkan mikrotik_id di table customers
        $billingsPayment = Billing::with(['customer'])
            ->where('status', 'LS')
            ->whereHas('customer', function ($query) use ($targetMikrotikId) {
                $query->where('mikrotik_id', $targetMikrotikId);
            })
            ->whereHas('billingSubscription', function ($query) use ($periodeId) {
                $query->where('billing_periode_id', $periodeId);
            })
            ->get();

        foreach ($billingsPayment as $billing) {
            if ($billing->customer) {
                $syncService->syncPaymentComment($billing, $this->traceId);
            }
        }

        // 2. Ambil data Billing yang BELUM LUNAS (Bukan LS) berdasarkan mikrotik_id di table customers
        $billingsUnpayment = Billing::with(['customer'])
            ->where('status', '!=', 'LS')
            ->whereDate('due_date', '<=', $today)
            ->whereHas('customer', function ($query) use ($targetMikrotikId) {
                $query->where('mikrotik_id', $targetMikrotikId);
            })
            ->whereHas('billingSubscription', function ($q) use ($periodeId) {
                $q->where('billing_periode_id', $periodeId);
            })
            ->where(function ($query) use ($today) {
                $query->whereNull('paylater_until')
                    ->orWhereDate('paylater_until', '<', $today);
            })
            ->get();

        foreach ($billingsUnpayment as $billing) {
            if ($billing->customer) {
                $syncService->syncUnpaymentComment($billing, $this->traceId);
            }
        }
    }
}
