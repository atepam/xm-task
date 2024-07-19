<?php

namespace App\Jobs;

use App\Models\LatestPriceCandle;
use Atepam\AlphavantageClient\Services\LatestPrice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class GetLatestPricesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $symbol;

    /**
     * Create a new job instance.
     */
    public function __construct(string $symbol)
    {
        $this->symbol = $symbol;
    }

    /**
     * Execute the job.
     */
    public function handle(LatestPrice $avLatestPrice): void
    {
        try {
            $latestPriceData = $avLatestPrice->getLatestPrice($this->symbol);
            if ($latestPriceData) {
                $candle = new LatestPriceCandle($latestPriceData);
                $candle->save();
            }

        } catch (Throwable $exception) {
            $this->fail($exception);
        }
    }
}
