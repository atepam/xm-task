<?php

namespace Tests;

use App\Exceptions\AlphaVantage\ConfigurationException;
use App\Jobs\GetLatestPricesJob;
use App\Models\User;
use App\Services\AlphaVantage\ClientConfig;
use App\Services\AlphaVantage\LatestPriceCandleFactory;
use App\Services\AlphaVantage\LatestPriceClient;
use App\Services\LatestPricesCaching;
use Exception;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Laravel\Sanctum\Sanctum;
use Mockery\MockInterface;

abstract class TestCase extends BaseTestCase
{
//    protected const VALID_LATEST_PRICE_URL = 'https://www.alphavantage.co/query?function=GLOBAL_QUOTE&symbol=' . self::TEST_SYMBOL . '&apikey=' . self::DEMO_API_KEY;
    protected const TEST_SYMBOL = 'IBM';

    protected const DEMO_API_KEY = 'demo';

    protected const SYMBOLS = ['IBM', 'SAIC', 'SAICX', 'BA', 'BAB', 'BABA', 'BAAPX', 'BAAAFX', 'BAAX39.SAO', 'AB3.LON', 'BA3.FRK', 'BA.LON', '600104.SHH'];

    /**
     * @throws ConfigurationException
     */
    protected function getAlphaVantageClientConfigForDemoApi(string $apiKey = self::DEMO_API_KEY): ClientConfig
    {
        return new ClientConfig(
            $apiKey,
            Config::get('services.alphaVantage.apiHost')
        );
    }

    /**
     * Since the free api key has rate limits for several tests
     * we use the publicly communicated demo api call.
     *
     * @throws ConfigurationException
     */
    protected function getLatestPriceClient(
        $latestPriceCandleFactory = null,
        string $apiKey = self::DEMO_API_KEY
    ): LatestPriceClient
    {
        if ($latestPriceCandleFactory === null) {
            $latestPriceCandleFactory = new LatestPriceCandleFactory();
        }

        return new LatestPriceClient($this->getAlphaVantageClientConfigForDemoApi($apiKey), $latestPriceCandleFactory);
    }

    protected function getMockResponseForValidLatestPriceRequest(): MockInterface
    {
        $candleData = [
            "Global Quote" => [
                "01. symbol" => "IBM",
                "02. open" => "175.0000",
                "03. high" => "178.4599",
                "04. low" => "174.1500",
                "05. price" => "175.0100",
                "06. volume" => "4864735",
                "07. latest trading day" => "2024-06-24",
                "08. previous close" => "172.4600",
                "09. change" => "2.5500",
                "10. change percent" => "1.4786%"]
        ];

        $response = $this->mock(Response::class);
        $response->allows('json')->andReturns($candleData);

        return $response;
    }

    protected function getCacheKeyForLatestPricesCollection(): string
    {
        return LatestPricesCaching::LATEST_PRICE_CANDLE_CACHE_COLLECTION_KEY . '_testing';
    }

    protected function runJobForSymbol(string $symbol): void
    {
        $this->getJob($symbol)->handle($this->getLatestPriceClient());
    }

    protected function getJob(string $symbol): GetLatestPricesJob
    {
        return new GetLatestPricesJob($symbol);
    }

    protected function getCachedLatestPriceCollection(): Collection
    {
        $key = $this->getCacheKeyForLatestPricesCollection();

        return Cache::get($key);
    }

    protected function actAsAuthenticated(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['*']
        );
    }

    protected function getSymbols(): array
    {
        $symbols = config('services.alphaVantage.latestPricesSymbols', []);
        if (empty($symbols)) {
            throw new Exception('Testing with empty symbol list!');
        }

        return $symbols;
    }

    protected function setTrackedSymbolsEmpty(): void
    {
        Config::set('services.alphaVantage.latestPricesSymbols', []);
    }

    protected function setSeveralTrackedSymbols(): void
    {
        Config::set('services.alphaVantage.latestPricesSymbols', self::SYMBOLS);
    }

    protected function generateDataForSymbols(array $symbols): void
    {
        foreach ($symbols as $symbol) {
            $this->runJobForSymbol($symbol);
        }
    }
}
