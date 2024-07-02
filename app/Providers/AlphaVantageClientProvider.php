<?php

namespace App\Providers;


use App\Services\AlphaVantage\LatestPriceClient;
use App\Services\AlphaVantage\ClientConfig;
use App\Services\AlphaVantage\LatestPriceCandleFactory;
use Illuminate\Support\ServiceProvider;

class AlphaVantageClientProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(LatestPriceClient::class, function () {
            $config = new ClientConfig(
                (string)config('services.alphaVantage.apiKey', ''),
                (string)config('services.alphaVantage.apiHost', ''),
            );

            return new LatestPriceClient($config, new LatestPriceCandleFactory());
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
