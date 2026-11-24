<?php

namespace App\Providers;

use App\Services\CurrencyService;
use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        date_default_timezone_set(config('app.timezone', 'America/Lima'));
        Carbon::setLocale(config('app.locale', 'es'));

        Paginator::defaultView('vendor.pagination.custom');
        Paginator::defaultSimpleView('vendor.pagination.custom-simple');

        View::composer('*', function ($view): void {
            /** @var CurrencyService $currencyService */
            $currencyService = app(CurrencyService::class);

            $view->with([
                'tipoCambio' => $currencyService->getRateUsdToPen(),
                'monedaActiva' => $currencyService->getActiveCurrency(),
            ]);
        });
    }
}
