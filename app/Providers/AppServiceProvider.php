<?php

namespace App\Providers;

use App\Models\Receipt;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Carbon;
use App\Services\Billings\PaymentCancellationService;
use App\Services\Billings\PaymentProcessingService;
use App\Services\Routerboard\Ppp\SecretService;
use App\Services\Routerboard\IpStaticService;
use App\Services\Networks\Olt\OltDataService;
use App\Services\Networks\Olt\OltScanJobService;
use App\Services\Networks\Olt\OltValidationService;
use App\View\Components\Layouts\TopBar;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(PaymentCancellationService::class, function ($app) {
            return new PaymentCancellationService();
        });

        $this->app->bind(PaymentProcessingService::class, function ($app) {
            return new PaymentProcessingService(
                $app->make(SecretService::class),
                $app->make(IpStaticService::class)
            );
        });

        $this->app->singleton(OltDataService::class, function ($app) {
            return new OltDataService();
        });

        $this->app->singleton(OltScanJobService::class, function ($app) {
            return new OltScanJobService();
        });

        $this->app->singleton(OltValidationService::class, function ($app) {
            return new OltValidationService();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // App::setLocale('id');
        config(['app.locale' => 'id']);
        Carbon::setLocale('id');
        Blade::directive('moneyIDR', function ($amount) {
            return "<?php echo 'Rp. ' . number_format($amount, 2); ?>";
        });

        Paginator::useBootstrap();
        Blade::component(TopBar::class, 'layouts.top-bar');

        View::composer('components.layouts.cpanel.billings.navigation', function ($view) {
            $address = Receipt::whereNotNull('customer_address')
                ->where('customer_address', '!=', '')
                ->distinct()
                ->orderBy('customer_address', 'asc')
                ->pluck('customer_address');

            $tellers = Receipt::whereNotNull('teller_name')
                ->distinct()
                ->orderBy('teller_name', 'asc')
                ->pluck('teller_name', 'teller_name');

            $months = [
                '01' => 'Januari',
                '02' => 'Februari',
                '03' => 'Maret',
                '04' => 'April',
                '05' => 'Mei',
                '06' => 'Juni',
                '07' => 'Juli',
                '08' => 'Agustus',
                '09' => 'September',
                '10' => 'Oktober',
                '11' => 'November',
                '12' => 'Desember'
            ];

            $currentYear = (int) date('Y');
            $years = array_combine(
                range($currentYear, $currentYear - 5),
                range($currentYear, $currentYear - 5)
            );

            $view->with(compact('address', 'tellers', 'months', 'years'));
        });
    }
}
