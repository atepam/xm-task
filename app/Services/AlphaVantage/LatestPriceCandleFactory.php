<?php

namespace App\Services\AlphaVantage;

use App\Exceptions\AlphaVantage\LatestPriceDataException;
use App\Models\LatestPriceCandle;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class LatestPriceCandleFactory
{
    /**
     * @throws LatestPriceDataException
     */
    public function createFromApiResponse(Response $response): LatestPriceCandle
    {
        $this->validateResponseData($response);

        $data = $response->json()['Global Quote'];

        return new LatestPriceCandle([
            'symbol' => $data['01. symbol'],
            'open' => $data['02. open'],
            'high' => $data['03. high'],
            'low' => $data['04. low'],
            'price' => $data['05. price'],
            'volume' => $data['06. volume'],
            'latest_trading_date' => $data['07. latest trading day'],
            'prev_close' => $data['08. previous close'],
            'change' => $data['09. change'],
            'change_percent' => $data['10. change percent'],
            'time' => Carbon::now()->toDateTimeString(),
        ]);
    }

    /**
     * @throws LatestPriceDataException
     */
    public function validateResponseData(Response $response): void
    {
        $data = $response->json();

        $validator = Validator::make($data, [
            'Global Quote.01\. symbol' => 'required|string',
            "Global Quote.02\. open" => "required|string",
            "Global Quote.03\. high" => "required|string",
            "Global Quote.04\. low" => "required|string",
            "Global Quote.05\. price" => "required|string",
            "Global Quote.06\. volume" => "required|int",
            "Global Quote.07\. latest trading day" => "date",
            "Global Quote.08\. previous close" => "required|string",
            "Global Quote.09\. change" => "required|string",
            "Global Quote.10\. change percent" => "required|string",
        ]);

        if ($validator->fails()) {
            Log::critical('Invalid latest price candle data', [
                'host' => $response->effectiveUri()->getHost(),
                'query' => $response->effectiveUri()->getQuery(),
                'errors' => $validator->errors()->toArray(),
            ]);

            throw new LatestPriceDataException('Invalid latest price candle data');
        }
    }
}
