<?php

use App\Console\Commands\RefreshLatestPrices;
use Illuminate\Support\Facades\Schedule;

Schedule::command(RefreshLatestPrices::COMMAND)->everyMinute();
