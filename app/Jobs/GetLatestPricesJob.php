<?php

namespace App\Jobs;

use App\Services\AlphaVantage\LatestPriceClient;
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
    public function handle(LatestPriceClient $latestPriceClient): void
    {
        try {

            $candle = $latestPriceClient->getLatestPrice($this->symbol);
            $candle?->save();

        } catch (Throwable $exception) {
            $this->fail($exception);
        }
    }
}
