<?php

namespace Tests\Feature\Services\AlphaVantage;


use App\Services\AlphaVantage\LatestPriceClient;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LatestPriceClientTest extends TestCase
{
    use LazilyRefreshDatabase;


    #[Test]
    public function client_is_instantiable_by_provider_with_valid_config(): void
    {
        $this->assertInstanceOf(
            LatestPriceClient::class,
            app(LatestPriceClient::class)
        );
    }

    #[Test]
    public function rate_limit_null_returned(): void
    {
        $this->setApiRespondAsRateLimited();
        $candle = $this->getLatestPriceClient()->getLatestPrice(self::TEST_SYMBOL);

        $this->assertNull($candle);
    }

    protected function setApiRespondAsRateLimited(): void
    {
        Config::set('services.alphaVantage.apiHost', 'http://localhost/avserver/rate-limit');
    }
}
