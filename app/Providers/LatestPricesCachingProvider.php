<?php

namespace App\Providers;

use App\Services\LatestPricesCaching;
use Illuminate\Support\ServiceProvider;

class LatestPricesCachingProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(LatestPricesCaching::class, function () {
            return new LatestPricesCaching(
                config('services.alphaVantage.latestPriceCacheTtl'),
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
