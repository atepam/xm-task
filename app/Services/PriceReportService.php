<?php

namespace App\Services;

use App\Models\LatestPriceCandle;
use App\Models\PriceReportItem;
use Illuminate\Support\Collection;

class PriceReportService
{
    public function __construct(
        private readonly LatestPricesCaching $latestPricesCaching,
    )
    {
    }

    public function getPriceReport(): Collection
    {
        $collection = $this->latestPricesCaching->getCollection();

        return $collection
            ->map(function (array $item) {
                return $this->getPriceReportItem(
                    new LatestPriceCandle($item['latest']),
                    new LatestPriceCandle($item['previous'])
                )->toArray();
            });
    }

    protected function getPriceReportItem(LatestPriceCandle $latest, LatestPriceCandle $previous): PriceReportItem
    {
        return new PriceReportItem([
            'symbol' => $latest->symbol(),
            'price' => $latest->price(),
            'change_percent' => $latest->calcChange($previous),
            'time' => $latest->time(),
        ]);

    }
}
