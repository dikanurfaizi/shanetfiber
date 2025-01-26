<?php

namespace App\Console\Commands;

use Exception;
use App\Models\Customer;
use App\Models\Mikrotik;
use App\Models\AutoIsolir;
use Illuminate\Support\Str;
use App\Jobs\CreateBillingJob;
use Illuminate\Support\Carbon;
use App\Models\Billing\Billing;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use App\Models\Billing\BillingPeriode;
use Illuminate\Support\Facades\Redirect;
use App\Http\Controllers\BillingController;

class CreateBillingCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create_billing:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        // $this->call([BillingController::class, 'store']);
        //return \App::call([BillingController::class, 'store']);
        //  app('App\Http\Controllers\BillingController')->store();
        // return \Redirect::route('billings.store');
        //$result = app('App\Http\Controllers\BillingController')->store();
        //$result = (new BillingController::class)->store();

        $periode = Carbon::now()->format('F Y');
        $billingPeriode = BillingPeriode::create([
            'name' => $periode,
        ]);
        $routersEnable = Mikrotik::where('disabled', 'false')->get();
        foreach ($routersEnable as $routerEnable) {
            $customers = Customer::where('disabled', 'false')->where('activation', 'true')->where('mikrotik_id', $routerEnable->id)->get();
            $monthYear = date_parse($periode);
            $month = $monthYear['month'];
            $year = $monthYear['year'];
            $billing = Billing::select('customer_id')->where('month', $month)
                ->where('year', $year)
                ->get();

            $customersUnbilling = $customers->diff(
                Customer::whereIn('id', $billing)
                    ->get()
            );

            //
            foreach ($customersUnbilling as $customer) {
                $customerActivation = date_parse($customer->activation_date);

                $customerActivationDate = $customerActivation['day'];

                if ($customerActivationDate >= 29) {
                    $customerActivationDate = 28;
                }
                $secretId = $customer->secret_id;
                $mikrotikId = $customer->mikrotik_id;
                $router = Mikrotik::where('id', $mikrotikId)->first();
                $autoIsolir = AutoIsolir::where('mikrotik_id', $mikrotikId)->first();
                if ($autoIsolir != NULL) {
                    if ($autoIsolir->disabled == 'false') {

                        if ($autoIsolir->activation_date == 'false') {
                            $comment = $autoIsolir->comment_unpayment;
                            try {
                                dispatch(new CreateBillingJob($router, $comment, $secretId))->onQueue('default');
                            } catch (\Exception $e) {
                                $billingPeriode->delete();
                                return redirect()->back()->withErrors(['msg' => 'Server ' . $router->name . ' offline, please check your router.']);
                            }
                        }
                    }
                }
                //
                Billing::create([
                    'slug' => str($customer->id . '-' . Str::random(4))->slug(),
                    'month' => $month,
                    'year' => $year,
                    'billing_periode_id' => $billingPeriode->id,
                    'billing_number' => str($customer->id . '-' . Str::random(4)  . '-' . $month . '-' . $year)->slug(),
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->name,
                    'paket_name' => $customer->paket->name,
                    'paket_price' => $customer->paket->price,
                    'created_name' => 'System',
                    'status' => 'BL'
                ]);
            }
        }

        Log::info("Cron is working fine!");
    }
}
