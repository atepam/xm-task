<?php

namespace Tests\Unit;
//use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

use App\Console\Commands\RefreshLatestPrices;
use App\Jobs\GetLatestPricesJob;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RefreshLatestPricesCommandTest extends TestCase
{
   protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
    }

    #[Test]
    public function no_job_dispatched_when_empty_symbol_list(): void
    {
        $this->setTrackedSymbolsEmpty();

        $this->artisan(RefreshLatestPrices::COMMAND);

        Queue::assertNothingPushed();
    }

    #[Test]
    public function dispatches_one_get_latest_prices_job_for_each_symbol(): void
    {
        $this->setSeveralTrackedSymbols();

        $this->artisan(RefreshLatestPrices::COMMAND);

        Queue::assertPushed(GetLatestPricesJob::class, count(self::SYMBOLS));
    }
}
