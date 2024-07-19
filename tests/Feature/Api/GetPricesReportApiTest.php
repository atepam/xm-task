<?php

namespace Tests\Feature\Api;

use App\Services\PriceReportService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class GetPricesReportApiTest extends TestCase
{
    #[Test]
    public function api_unauthenticated_call_401(): void
    {
        $response = $this->callLatestPricesReportApiRoute();
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    #[Test]
    public function api_response_has_proper_data(): void
    {
        $this->actAsAuthenticated();
        $this->setApiRespondAsPerfect();

        $symbols = $this->getSymbols();
        $this->generateDataForSymbols($symbols);
        $this->generateDataForSymbols($symbols);
        $response = $this->callLatestPricesReportApiRoute();

        $response->assertStatus(Response::HTTP_OK);

        $responseData = $response->json();

        $this->assertIsArray($responseData);

        $responseCollection = collect($responseData);
        foreach ($symbols as $symbol) {
            $current = $responseCollection->where('symbol', $symbol)->first();

            $this->assertIsArray($current);
            $this->assertArrayHasKey('symbol', $current);
            $this->assertArrayHasKey('price', $current);
            $this->assertArrayHasKey('change_percent', $current);
            $this->assertArrayHasKey('time', $current);
        }
    }

    #[Test]
    public function exception_response_500_empty(): void
    {
        $this->actAsAuthenticated();
        $symbols = $this->getSymbols();

        Cache::clear();

        $this->mock(PriceReportService::class)
            ->allows('getPriceReport')
            ->andThrowExceptions([new \Exception('aaaa')]);

        $response = $this->callLatestPricesReportApiRoute();


        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
        $this->assertIsArray($response->json());
    }

    protected function callLatestPricesReportApiRoute(): TestResponse
    {
        return $this->getJson(route('api-latest-prices-report'));
    }
}
