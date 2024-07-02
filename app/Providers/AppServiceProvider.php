<?php

namespace App\Providers;

use App\Models\LatestPriceCandle;
use App\Observers\LatestPriceCandleObserver;
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
        LatestPriceCandle::observe(LatestPriceCandleObserver::class);
    }
}
