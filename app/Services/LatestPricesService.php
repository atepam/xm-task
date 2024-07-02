<?php

namespace App\Services;

use App\Models\LatestPriceCandle;
use Illuminate\Support\Collection;

class LatestPricesService
{
    private array $symbols;

    public function __construct(
        private readonly LatestPricesCaching $latestPricesCaching,
    )
    {
        $this->symbols = config('services.alphaVantage.latestPricesSymbols', []);
    }

    public function getLatestPrices(): Collection
    {
        $collection = $this->latestPricesCaching->getCollection();

        if ($collection->isNotEmpty()) {
            return $this->filterForLatest($collection);
        }

        $collection = $this->loadDataToCacheFromDb();

        return $this->filterForLatest($collection);
    }

    protected function filterForLatest(Collection $collection): Collection
    {
        return $collection
            ->map(function (array $item) {
                return $item['latest'];
            });
    }

    protected function loadDataToCacheFromDb(): Collection
    {
        foreach ($this->symbols as $symbol) {
            $latest = $this->getLatestForSymbol($symbol);

            if ($latest instanceof LatestPriceCandle) {
                $previous = $this->getPrevious($symbol, $latest);

                if ($previous instanceof LatestPriceCandle) {
                    $this->latestPricesCaching->addCandleToCachedCollection($previous);
                }

                $this->latestPricesCaching->addCandleToCachedCollection($latest);
            }
        }

        return $this->latestPricesCaching->getCollection();
    }

    protected function getLatestForSymbol(string $symbol): ?LatestPriceCandle
    {
        return LatestPriceCandle::where('symbol', $symbol)
            ->orderBy('time', 'desc')
            ->limit(1)
            ->first();
    }

    protected function getPrevious(string $symbol, LatestPriceCandle $latest): ?LatestPriceCandle
    {
        return LatestPriceCandle::where('symbol', $symbol)
            ->where('time', '<', $latest->time)
            ->orderBy('time', 'desc')
            ->limit(1)
            ->first();
    }
}
