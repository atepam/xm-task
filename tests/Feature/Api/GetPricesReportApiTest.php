<?php

namespace Tests\Feature\Api;

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

        $symbols = $this->getSymbols();
        $this->generateDataForSymbols($symbols);
        $this->generateDataForSymbols($symbols);
        $response = $this->callLatestPricesReportApiRoute();

        $response->assertStatus(Response::HTTP_OK);

        foreach ($symbols as $symbol) {
            $current = $response->json()[$symbol];
            $this->assertIsArray($current);
            $this->assertArrayHasKey('symbol', $current);
            $this->assertArrayHasKey('price', $current);
            $this->assertArrayHasKey('change_percent', $current);
            $this->assertArrayHasKey('time', $current);
        }
    }



    protected function callLatestPricesReportApiRoute(): TestResponse
    {
        return $this->getJson(route('api-latest-prices-report'));
    }
}
