<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// This is a local AlphaVantage latest price server
if (isRunningInLocal()) {
    require __DIR__ . '/../tests/alpha_vantage_test_server.php';
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__ . '/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
(require_once __DIR__ . '/../bootstrap/app.php')
    ->handleRequest(Request::capture());


function isRunningInLocal(): bool
{
    return (
            isset($_SERVER['HTTP_HOST'])
            && $_SERVER['HTTP_HOST'] === 'localhost:8099'
        )
        && $_SERVER['REQUEST_METHOD'] === 'GET'
        && isset($_SERVER['PATH_INFO'])
        && stristr($_SERVER['PATH_INFO'], '/avserver');
}
