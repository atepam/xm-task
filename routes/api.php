<?php

use App\Http\Controllers\Api\GetLatestPrices;
use App\Http\Controllers\Api\GetPriceReport;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])
    ->prefix('latest-prices')
    ->group(function () {
        Route::get('/', GetLatestPrices::class)
            ->name('api-latest-prices');

        Route::get('/report', GetPriceReport::class)
            ->name('api-latest-prices-report');
    });
