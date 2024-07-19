<?php

namespace Tests\Feature\Services;


use App\Models\LatestPriceCandle;
use App\Services\LatestPricesService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LatestPricesServiceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function db_is_empty_cache_is_empty_results_empty_latest_prices_collection(): void
    {
        $this->makeEmptyCacheAndDb();

        $collection = $this->getLatestPricesService()->getLatestPrices();

        $this->assertEmpty($collection);
    }

    #[Test]
    public function db_has_data_for_tracked_symbol_and_cache_is_empty_so_service_loads_tracked_from_db(): void
    {
        $this->makeEmptyCacheAndDb();

        $symbolsForDataGeneration = [self::TEST_SYMBOL];
        $this->generateDataForSymbols($symbolsForDataGeneration);

        $collection = $this->getLatestPricesService()->getLatestPrices();

        $this->assertNotEmpty($collection);
        $this->assertCount(count($symbolsForDataGeneration), $collection);
        $this->assertTrue($collection->has(self::TEST_SYMBOL));
    }

    #[Test]
    public function db_has_no_data_for_tracked_symbol_and_cache_is_empty_so_service_not_loads_from_db(): void
    {
        $this->makeEmptyCacheAndDb();
        $this->setApiRespondAsPerfect();
        $this->generateDataForSymbols(['NOT.TRACKED']);
        Cache::clear();

        $this->assertDatabaseHas(LatestPriceCandle::TABLE_NAME, ['symbol' => 'NOT.TRACKED']);
        $this->assertEmpty($this->getLatestPricesService()->getLatestPrices());
    }


    protected function getLatestPricesService(): LatestPricesService
    {
        return app(LatestPricesService::class);
    }

    protected function makeEmptyCacheAndDb(): void
    {
        LatestPriceCandle::truncate();
        Cache::clear();
        $this->assertDatabaseEmpty(LatestPriceCandle::TABLE_NAME);
    }

}
