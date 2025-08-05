<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Carbon;
use App\Services\Billings\PaymentCancellationService;
use App\Services\Billings\PaymentProcessingService;
use App\Services\Billings\PaymentService;
use App\Services\Routerboard\Ppp\SecretService;
use App\Services\Routerboard\IpStaticService;

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

        $this->app->bind(PaymentService::class, function ($app) {
            return new PaymentService(
                $app->make(PaymentProcessingService::class),
                $app->make(PaymentCancellationService::class)
            );
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
    }
}
