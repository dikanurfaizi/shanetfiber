<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Request;
use Illuminate\Pagination\LengthAwarePaginator;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
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
