<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\StringType;
use App\Services\Billings\PaymentCancellationService;
use App\Services\Billings\PaymentProcessingService;
use App\Services\Billings\PaymentService;
use App\Services\Routerboard\Ppp\SecretService;
use App\Services\Routerboard\IpStaticService;
use App\Services\Networks\Olt\OltDataService;
use App\Services\Networks\Olt\OltScanJobService;
use App\Services\Networks\Olt\OltValidationService;

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
        Schema::defaultStringLength(191);

        // Registrasi mapping tipe data timestamp & enum untuk Doctrine DBAL
        if (class_exists(Type::class)) {
            try {
                if (!Type::hasType('timestamp')) {
                    Type::addType('timestamp', StringType::class);
                }

                $platform = DB::connection()->getDoctrineSchemaManager()->getDatabasePlatform();
                $platform->registerDoctrineTypeMapping('timestamp', 'timestamp');
                $platform->registerDoctrineTypeMapping('enum', 'string');
            } catch (\Exception $e) {
                // Dimatikan secara aman jika database belum terhubung saat instalasi awal
            }
        }

        // App::setLocale('id');
        config(['app.locale' => 'id']);
        Carbon::setLocale('id');
        Blade::directive('moneyIDR', function ($amount) {
            return "<?php echo 'Rp. ' . number_format($amount, 2); ?>";
        });

        Paginator::useBootstrap();
    }
}
