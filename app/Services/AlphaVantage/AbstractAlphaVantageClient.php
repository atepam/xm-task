<?php

namespace App\Services\AlphaVantage;

use App\Exceptions\AlphaVantage\RateLimitException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

abstract class AbstractAlphaVantageClient
{
    public const FUNCTION = '';
    public const BASE_URI = '/';

    public function __construct(
        protected readonly ClientConfig $config,
    )
    {
    }

    /**
     * @throws RateLimitException|ConnectionException
     */
    protected function get(array $parameters): ?Response
    {
        return rescue(
        // The core logic that call the API, handles retries.
            function () use ($parameters) {

                $response = Http::retry(2, 100)
                    ->get($this->buildApiUrl($parameters));

                $this->checkRateLimit($response);

                return $response;

            },

            // If any exception occurs during the call
            function ($e) {

                $response = property_exists($e, 'response')
                    ? $e->response
                    : null;

                Log::critical('Alpha Vantage API - GET - error', [
                    'exception' => $e,
                    'api_response' => $response,
                ]);

                return null;
            },

            // We do not report RateLimitExceptions to the ExceptionHandler to prevent duplicated log entries
            report: function (Throwable $throwable) {
                return !($throwable instanceof RateLimitException);
            });
    }

    protected function buildApiUrl(array $params): string
    {
        return sprintf(
            "%s%s?%s",
            $this->config->apiHost,
            static::BASE_URI,
            $this->buildQueryParams($params)
        );
    }

    protected function buildQueryParams(array $params): string
    {
        // This '+' operator is REQUIRED because the API is sensitive for the query param order. (for 'demo' apiKey for sure)
        // OK Example:  https://www.alphavantage.co/query?function=GLOBAL_QUOTE&symbol=IBM&apikey=demo
        // BAD Example: https://www.alphavantage.co/query?symbol=IBM&function=GLOBAL_QUOTE&apikey=demo
        // Note param order!

        return http_build_query(
            ['function' => static::FUNCTION,] + $params + ['apikey' => $this->config->apiKey,]
        );
    }

    /**
     * @throws RateLimitException
     */
    protected function checkRateLimit(Response $response): void
    {
        $data = $response->json();

        if (
            array_key_exists('Information', $data)
            && Str::contains($data['Information'], 'API rate limit')
        ) {
            throw new RateLimitException();
        }
    }
}
