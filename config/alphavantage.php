<?php


return [

    /*
    |--------------------------------------------------------------------------
    | Alpha Vantage
    |--------------------------------------------------------------------------
    |
    |
    */

    'apiHost' => env('ALPHA_VANTAGE_API_HOST', 'https://www.alphavantage.co'),
    'apiKey' => env('ALPHA_VANTAGE_API_KEY', 'demo'),
    'logErrors' => env('ALPHA_VANTAGE_LOG_ERRORS', true),
];
