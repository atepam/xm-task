<?php

namespace App\Observers;

use App\Models\LatestPriceCandle;
use App\Services\LatestPricesCaching;

class LatestPriceCandleObserver
{

    public function __construct(
        private readonly LatestPricesCaching $latestPricesCaching,
    )
    {
    }

    public function created(LatestPriceCandle $candle): void
    {
        $this->latestPricesCaching->addCandleToCachedCollection($candle);
    }

    public function updated(LatestPriceCandle $candle): void
    {
        $this->latestPricesCaching->updateCandleToCachedCollection($candle);
    }

    public function deleted(LatestPriceCandle $candle): void
    {
        $this->latestPricesCaching->deleteCandleToCachedCollection($candle);
    }

}
