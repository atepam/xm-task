<?php

namespace Tests\Feature\Api;

use Illuminate\Support\Facades\Cache;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class GetLatestPricesApiTest extends TestCase
{
    #[Test]
    public function unauthenticated_call_401(): void
    {
        $response = $this->callLatestPricesApiRoute();
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    #[Test]
    public function authenticated_call_200(): void
    {
        $this->actAsAuthenticated();

        $this->generateDataForSymbols([self::TEST_SYMBOL]);
        $this->callLatestPricesApiRoute()
            ->assertStatus(Response::HTTP_OK);
    }

    #[Test]
    public function cache_not_empty_response_has_data_array_for_each_symbol(): void
    {
        Cache::clear();
        $this->actAsAuthenticated();
        $symbols = $this->getSymbols();

        $this->generateDataForSymbols($symbols);

        $response = $this->callLatestPricesApiRoute();

        foreach ($symbols as $symbol) {
            $this->assertIsArray($response->json()[$symbol]);
            $this->assertNotEmpty($response->json()[$symbol]);
        }
    }

    #[Test]
    public function cache_is_empty_action_fills_cache_for_each_symbol(): void
    {
        $this->actAsAuthenticated();
        $symbols = $this->getSymbols();

        Cache::clear();

        $response = $this->callLatestPricesApiRoute();

        $this->assertEquals(count($symbols), count($response->json()));

        foreach ($symbols as $symbol) {
            $this->assertIsArray($response->json()[$symbol]);
        }
    }

    protected function callLatestPricesApiRoute(): TestResponse
    {
        return $this->getJson(route('api-latest-prices'));
    }
}
