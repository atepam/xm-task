<?php

namespace App\Console\Commands;

use App\Jobs\GetLatestPricesJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RefreshLatestPrices extends Command
{
    const COMMAND = 'app:refresh-latest-prices';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gets latest prices for the configured stocks';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $symbols = config('services.alphaVantage.latestPricesSymbols', []);

        if (empty($symbols)) {
            Log::warning('No symbols configured for latest prices');
        }

        foreach ($symbols as $symbol) {
            GetLatestPricesJob::dispatch($symbol);
        }
    }
}
