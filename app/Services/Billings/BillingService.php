<?php

namespace App\Services\Billings;

use App\Models\Customer;
use App\Models\Mikrotik;
use App\Models\AutoIsolir;
use App\Models\Billing\Billing;
use App\Models\Billing\BillingPeriode;
use App\Models\Websystem;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Jobs\CreateBillingJob;
use App\Jobs\CreateBillingIpStaticJob;

class BillingService
{
    public function createBillingPeriode(string $periode): BillingPeriode
    {
        $periodeExplode = explode(' ', $periode);
        return BillingPeriode::create([
            'name' => $periode,
            'month' => $periodeExplode[0],
            'year' => $periodeExplode[1],
        ]);
    }

    public function generateBillingsForPeriod(BillingPeriode $billingPeriode): int
    {
        $totalBillingCreated = 0;
        $websystem = Websystem::where('slug', 'websystem')->first();
        $routers = Mikrotik::where('disabled', 'false')->get();

        foreach ($routers as $router) {
            $customers = Customer::where('disabled', 'false')
                ->where('activation', 'true')
                ->where('mikrotik_id', $router->id)
                ->get();

            $billing = Billing::select('customer_id')
                ->where('billing_periode_id', $billingPeriode->id)
                ->get();

            $customersUnbilling = $customers->diff(
                Customer::whereIn('id', $billing)->get()
            );

            $totalBillingCreated += $this->createBillingsForCustomers(
                $customersUnbilling,
                $billingPeriode,
                $websystem,
                $router
            );
        }

        return $totalBillingCreated;
    }

    private function createBillingsForCustomers(
        $customers,
        BillingPeriode $billingPeriode,
        $websystem,
        $router
    ): int {
        $totalBillingCreated = 0;
        $autoIsolir = AutoIsolir::where('mikrotik_id', $router->id)->first();

        foreach ($customers as $customer) {
            $customerPriceAfterDiscount = $this->calculateCustomerPrice(
                $customer,
                $billingPeriode->name
            );

            $customerTax = $websystem->enable_tax == 'true'
                ? ceil($customerPriceAfterDiscount * ($websystem->tax_rate / 100))
                : 0;

            if ($autoIsolir && $autoIsolir->disabled == 'false') {
                $this->dispatchBillingJobForCustomer($customer, $router, $autoIsolir, $customerPriceAfterDiscount);
            }

            Billing::create([
                'billing_periode_id' => $billingPeriode->id,
                'slug' => str($customer->id . '-' . Str::random(4))->slug(),
                'billing_number' => str($customer->id . '-' . Str::random(4) . '-' .
                    Carbon::parse($billingPeriode->name)->format('m') . '-' .
                    Carbon::parse($billingPeriode->name)->format('Y'))->slug(),
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'paket_name' => $customer->paket->name,
                'paket_price' => $customerPriceAfterDiscount,
                'tax' => $customerTax,
                'created_name' => auth()->user()->name,
                'status' => $customerPriceAfterDiscount > 0 ? 'BL' : 'LS',
                'due_date' => Carbon::createFromFormat('d F Y', "15 " .
                    Carbon::parse($billingPeriode->name)->format('F Y'))->endOfDay(),
            ]);

            $totalBillingCreated++;
        }

        return $totalBillingCreated;
    }

    private function calculateCustomerPrice(Customer $customer, string $periodeName): float
    {
        $paket_price = $customer->paket->price;
        $discount = optional($customer->member)->discount ?? 0;

        if ($customer->billing_option == 2 && $customer->billing_period === $periodeName) {
            return $paket_price - $discount;
        } elseif ($customer->billing_option == 1) {
            return $paket_price - $discount;
        }

        return $paket_price;
    }

    private function dispatchBillingJobForCustomer(
        Customer $customer,
        $router,
        $autoIsolir,
        float $price
    ): void {
        $comment = $price > 0
            ? $autoIsolir->comment_unpayment
            : $autoIsolir->comment_payment;

        if ($customer->internet_service == 'ppp') {
            dispatch(new CreateBillingJob($router, $comment, $customer->secret_id))
                ->onQueue('default');
        } elseif ($customer->internet_service == 'ip_static') {
            dispatch(new CreateBillingIpStaticJob($router, $customer->simpleque_id, $comment))
                ->onQueue('default');
        }
    }
}
