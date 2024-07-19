<?php

namespace Tests\Feature\Jobs;


use App\Models\LatestPriceCandle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetLatestPricesJobTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function job_persists_candle(): void
    {
        $this->assertDatabaseEmpty(LatestPriceCandle::TABLE_NAME);
        $this->setApiRespondAsPerfect();
        foreach ($this->getSymbols() as $symbol) {
            $this->runJobForSymbol($symbol);

            $this->assertDatabaseHas(LatestPriceCandle::TABLE_NAME, ['symbol' => $symbol,]);
        }
    }

    #[Test]
    public function not_persists_invalid_candle_and_logs_critical_for_each(): void
    {
        $this->setApiRespondAsInvalidData();

        $symbols = [self::TEST_SYMBOL];
        $symbols = ['lofasz'];

        Log::shouldReceive('critical')->times(count($symbols));

        foreach ($symbols as $symbol) {
            $this->runJobForSymbol($symbol);
            $this->assertDatabaseMissing(LatestPriceCandle::TABLE_NAME, ['symbol' => $symbol,]);
        }
    }

    #[Test]
    public function latest_and_previous_candle_cached_when_persisted(): void
    {
        Cache::clear();

        $this->runJobForSymbol(self::TEST_SYMBOL);

        $collection = $this->getCachedLatestPriceCollection();

        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertCount(1, $collection);

        $cache = $collection->get(self::TEST_SYMBOL);

        $this->assertIsArray($cache['latest']);
        $this->assertIsArray($cache['previous']);

        $this->assertSame($cache['latest'], $cache['previous']);
    }

    #[Test]
    public function latest_moved_to_previous_when_persisted_again(): void
    {
        Cache::clear();
        $this->assertDatabaseEmpty(LatestPriceCandle::TABLE_NAME);

        $this->runJobForSymbol(self::TEST_SYMBOL);
        $this->runJobForSymbol(self::TEST_SYMBOL);
        $this->runJobForSymbol(self::TEST_SYMBOL);

        $cache = $this->getCachedLatestPriceCollection()->get(self::TEST_SYMBOL);

        $this->assertNotSame($cache['latest'], $cache['previous']);

        $this->assertTrue($cache['latest']['id'] > $cache['previous']['id']);
    }

    #[Test]
    public function cache_removed_when_model_deleted(): void
    {
        Cache::clear();
        $this->assertDatabaseEmpty(LatestPriceCandle::TABLE_NAME);
        $this->setApiRespondAsPerfect();

        $symbolToDelete = self::TEST_SYMBOL;
        $symbols = [$symbolToDelete, 'AAPL'];
        $expectedCountAfterDelete = count($symbols) - 1;

        foreach ($symbols as $symbol) {
            $this->runJobForSymbol($symbol);
        }

        LatestPriceCandle::find($this->getLatestCandleFromCache($symbolToDelete)['id'])
            ->delete();

        $this->assertNull($this->getCachedLatestPriceCollection()->get($symbolToDelete));

        $this->assertCount($expectedCountAfterDelete, $this->getCachedLatestPriceCollection());
        $this->assertDatabaseMissing(LatestPriceCandle::TABLE_NAME, ['symbol' => $symbolToDelete]);
    }

    #[Test]
    public function cache_updated_when_price_updated(): void
    {
        Cache::clear();
        $this->assertDatabaseEmpty(LatestPriceCandle::TABLE_NAME);
        $this->setApiRespondAsPerfect();

        $symbolToUpdate = self::TEST_SYMBOL;
        $symbols = [$symbolToUpdate, 'AAPL'];

        foreach ($symbols as $symbol) {
            $this->runJobForSymbol($symbol);
        }

        $candleToUpdate = $this->getLatestCandleFromCache($symbolToUpdate);
        $expectedLatestPrice = 333;
        LatestPriceCandle::find($candleToUpdate['id'])
            ->update(['price' => $expectedLatestPrice]);

        $collectionAfterUpdate = $this->getCachedLatestPriceCollection();

        $this->assertSame(floatval($expectedLatestPrice), $collectionAfterUpdate->get($symbolToUpdate)['latest']['price']);
        $this->assertNotSame($candleToUpdate, $collectionAfterUpdate->get($symbolToUpdate)['latest']);
        $this->assertCount(count($symbols), $collectionAfterUpdate);
    }

    #[Test]
    public function latest_cache_not_updated_when_not_price_updated(): void
    {
        Cache::clear();
        $this->assertDatabaseEmpty(LatestPriceCandle::TABLE_NAME);
        $this->setApiRespondAsPerfect();

        $symbolToUpdate = self::TEST_SYMBOL;
        $symbols = [$symbolToUpdate, 'AAPL'];

        foreach ($symbols as $symbol) {
            $this->runJobForSymbol($symbol);
        }

        $candleToUpdate = $this->getLatestCandleFromCache($symbolToUpdate);
        LatestPriceCandle::find($candleToUpdate['id'])->update(['volume' => 333]);

        $cachedLatestPriceCollection = $this->getCachedLatestPriceCollection();

        $this->assertSame($candleToUpdate, $cachedLatestPriceCollection->get($symbolToUpdate)['latest']);

        $this->assertCount(count($symbols), $this->getCachedLatestPriceCollection());
    }

    protected function getLatestCandleFromCache(string $symbol): array
    {
        return $this->getCachedLatestPriceCollection()->get($symbol)['latest'];
    }

    protected function getPreviousCandleFromCache(string $symbol): array
    {
        return $this->getCachedLatestPriceCollection()->get($symbol)['previous'];
    }

    protected function setApiRespondAsInvalidData(): void
    {
        Config::set('alphavantage.apiHost', 'http://localhost/avserver/invalid');
    }
}
