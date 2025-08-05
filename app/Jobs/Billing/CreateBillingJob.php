<?php

namespace App\Jobs\Billing;

use App\Helpers\LogHelper;
use App\Jobs\CreateBillingIpStaticJob;
use App\Models\AutoIsolir;
use App\Models\Billing\Billing;
use App\Models\Billing\BillingPeriode;
use App\Models\Customer;
use App\Models\Mikrotik;
use App\Models\Websystem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class CreateBillingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected ?string $periodeName = null,
        protected Auth $auth
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $traceId = (string) Str::uuid();
        $periode = $this->periodeName ?: now()->format('F Y');

        DB::beginTransaction();
        try {
            // 1) Buat atau ambil periode
            $billingPeriode = BillingPeriode::firstOrCreate(
                ['name' => $periode],
                [
                    'month' => Str::before($periode, ' '),
                    'year'  => Str::after($periode,  ' ')
                ]
            );

            // 2) Proses router satu‑per‑satu, pakai cursor supaya memori ringan
            $totalCreated = 0;
            $websystem    = Websystem::firstOrFail();

            Mikrotik::whereDisabled('false')->cursor()->each(function ($router) use (
                $billingPeriode,
                $websystem,
                &$totalCreated,
                $traceId
            ) {
                // Ambil pelanggan aktif per router pakai chunkById (hemat memori)
                Customer::where('disabled', 'false')
                    ->where('activation', 'true')
                    ->where('mikrotik_id', $router->id)
                    ->chunkById(500, function (Collection $customers) use (
                        $billingPeriode,
                        $websystem,
                        $router,
                        &$totalCreated,
                        $traceId
                    ) {
                        // Filter yg sudah punya billing via eager‑loaded relation
                        $customers->loadMissing('paket', 'member');
                        $alreadyBilledIds = Billing::where('billing_periode_id', $billingPeriode->id)
                            ->pluck('customer_id')
                            ->toArray();

                        $customers
                            ->whereNotIn('id', $alreadyBilledIds)
                            ->each(function ($customer) use (
                                $billingPeriode,
                                $websystem,
                                $router,
                                &$totalCreated,
                                $traceId
                            ) {
                                $this->createSingleBilling(
                                    $customer,
                                    $router,
                                    $billingPeriode,
                                    $websystem,
                                    $traceId
                                );
                                ++$totalCreated;
                            });
                    });
            });

            DB::commit();

            LogHelper::logInfo(
                null,
                ['total_billing_created' => $totalCreated],
                200,
                "{$totalCreated} billing untuk {$periode} sukses dibuat.",
                $traceId
            );
        } catch (Throwable $e) {
            DB::rollBack();
            LogHelper::logError(
                null,
                ['error' => $e->getMessage()],
                500,
                'CreateBillingJob gagal, DB rollback.',
                $traceId
            );
            throw $e;
        }
    }

    // =============================================================
    // ================  INTERNAL HELPERS  =========================
    // =============================================================

    private function createSingleBilling(
        Customer $customer,
        Mikrotik $router,
        BillingPeriode $periode,
        Websystem $websystem,
        string $traceId
    ): void {
        // Hitung harga+tax
        $price = $this->calculateCustomerPrice($customer, $periode->name);
        $tax   = $websystem->enable_tax === 'true'
            ? (int) ceil($price * ($websystem->tax_rate / 100))
            : 0;

        // Queue auto‑isolir jika perlu
        $this->maybeDispatchIsolationJob($customer, $router, $price, $traceId);

        // Simpan billing
        Billing::create([
            'billing_periode_id' => $periode->id,
            'slug'               => Str::slug($customer->id . '-' . Str::random(4)),
            'billing_number'     => Str::slug(
                "{$customer->id}-" .
                    Str::random(4) . '-' .
                    $periode->month_num . '-' .
                    $periode->year
            ),
            'customer_id'        => $customer->id,
            'customer_name'      => $customer->name,
            'paket_name'         => $customer->paket->name,
            'paket_price'        => $price,
            'tax'                => $tax,
            'created_name'       => optional($this->auth->guard()->user())->name ?? 'system',
            'status'             => $price > 0 ? 'BL' : 'LS',
            'due_date'           => Carbon::createFromFormat(
                'd F Y',
                '15 ' . $periode->name
            )->endOfDay(),
        ]);
    }

    private function calculateCustomerPrice(Customer $customer, string $periodeName): int
    {
        $base     = $customer->paket->price;
        $discount = $customer->member->discount ?? 0;

        return match (true) {
            // Pay-as-used & periode aktif
            $customer->billing_option === 2 && $customer->billing_period === $periodeName => $base - $discount,
            // Flat / bulanan
            $customer->billing_option === 1                                               => $base - $discount,
            default                                                                       => $base,
        };
    }

    private function maybeDispatchIsolationJob(
        Customer $customer,
        Mikrotik $router,
        int $price,
        string $traceId
    ): void {
        $autoIsolir = AutoIsolir::where('mikrotik_id', $router->id)
            ->where('disabled', 'false')
            ->first();

        if (!$autoIsolir) {
            return;
        }

        $comment = $price > 0
            ? $autoIsolir->comment_unpayment
            : $autoIsolir->comment_payment;

        try {
            match ($customer->internet_service) {
                'ppp'       => CreateBillingJob::dispatch(
                    $router,
                    $comment,
                    $customer->secret_id
                )->onQueue('default'),
                'ip_static' => CreateBillingIpStaticJob::dispatch(
                    $router,
                    $customer->simpleque_id,
                    $comment
                )->onQueue('default'),
                default     => null,
            };
        } catch (Throwable $e) {
            LogHelper::logError(
                null,
                ['error' => $e->getMessage()],
                500,
                'Gagal dispatch job isolir.',
                $traceId
            );
            throw $e;
        }
    }
}
