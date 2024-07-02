<?php

namespace App\Services\AlphaVantage;

use App\Exceptions\AlphaVantage\LatestPriceDataException;
use App\Exceptions\AlphaVantage\RateLimitException;
use App\Models\LatestPriceCandle;
use Illuminate\Http\Client\ConnectionException;

class LatestPriceClient extends AbstractAlphaVantageClient
{
    public const FUNCTION = 'GLOBAL_QUOTE';
    public const BASE_URI = '/query';

    public function __construct(
        ClientConfig                              $config,
        private readonly LatestPriceCandleFactory $candleFactory,
    )
    {
        parent::__construct($config);
    }

    /**
     * @throws LatestPriceDataException|RateLimitException|ConnectionException
     */
    public function getLatestPrice(string $symbol): ?LatestPriceCandle
    {
        $response = $this->get(['symbol' => $symbol]);

        return $response
            ? $this->candleFactory->createFromApiResponse($response)
            : null;
    }
}
