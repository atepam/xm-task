<?php

namespace App\Services;

use App\Models\LatestPriceCandle;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class LatestPricesCaching
{
    public const LATEST_PRICE_CANDLE_CACHE_COLLECTION_KEY = 'latestPriceCollection';

    public function __construct(
        private readonly int $collectionTtl,
    )
    {
    }

    public function addCandleToCachedCollection(LatestPriceCandle $candle): Collection
    {
        $candles = [
            'latest' => $candle->toArray(),
            'previous' => $candle->toArray(),
        ];

        // If new model instance current latest goes to previous
        if ($this->isNewCandle($candle)) {

            $candles['previous'] = $this->getCollection()->get($candle->symbol())['latest'];
            $candles['latest'] = $candle->toArray();
        }

        $collection = $this->getCollection()->put($candle->symbol(), $candles);
        $this->updateCachedCollection($collection);

        return $collection;
    }

    public function updateCandleToCachedCollection(LatestPriceCandle $candle): Collection
    {
        $candles = $this->getCollection()->get($candle->symbol());

        if ($candle->isDirty('price')
            && !$this->isNewCandle($candle)
        ) {
            $candles['previous'] = $candles['latest'];
            $candles['latest'] = $candle->toArray();
            $collection = $this->getCollection()->put($candle->symbol(), $candles);

            return $this->updateCachedCollection($collection);
        }

        return $this->getCollection();
    }

    public function deleteCandleToCachedCollection(LatestPriceCandle $candle): Collection
    {
        $collection = $this->getCollection()->forget($candle->symbol());
        $this->updateCachedCollection($collection);

        return $collection;
    }

    protected function updateCachedCollection(Collection $collection): Collection
    {
        Cache::put(
            $this->getCacheKey(),
            $collection,
            $this->collectionTtl
        );

        return $collection;
    }

    public function getCollection(): Collection
    {
        $collectionData = Cache::get($this->getCacheKey());

        return $collectionData === null
            ? collect()
            : $collectionData;
    }

    protected function getCacheKey(): string
    {
        return self::LATEST_PRICE_CANDLE_CACHE_COLLECTION_KEY . '_' . app()->environment();
    }

    protected function isNewCandle(LatestPriceCandle $candle): bool
    {
        $cached = $this->getCollection()->get($candle->symbol());

        return $cached
            && $cached['latest']['id'] != $candle->id;
    }
}
