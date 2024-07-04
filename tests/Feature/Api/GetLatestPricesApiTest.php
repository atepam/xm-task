<?php

namespace Tests\Feature\Api;

use App\Services\LatestPricesService;
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

        $this->assertResponseHasValidDataForEachSymbol($response, $symbols);
    }

    #[Test]
    public function cache_is_empty_action_fills_cache_for_each_symbol(): void
    {
        $this->actAsAuthenticated();
        $symbols = $this->getSymbols();

        Cache::clear();

        $response = $this->callLatestPricesApiRoute();

        $this->assertEquals(count($symbols), count($response->json()));

        $this->assertIsArray($response->json());

        $this->assertResponseHasValidDataForEachSymbol($response, $symbols);
    }

    #[Test]
    public function exception_response_500_empty(): void
    {
        $this->actAsAuthenticated();
        $symbols = $this->getSymbols();

        Cache::clear();

        $this->mock(LatestPricesService::class)
            ->allows('getLatestPrices')
            ->andThrowExceptions([new \Exception('aaaa')]);

        $response = $this->callLatestPricesApiRoute();

        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
        $this->assertIsArray($response->json());
    }

    protected function callLatestPricesApiRoute(): TestResponse
    {
        return $this->getJson(route('api-latest-prices'));
    }

    protected function assertResponseHasValidDataForEachSymbol(TestResponse $response, array $symbols): void
    {
        $responseData = $response->json();

        $this->assertIsArray($responseData);

        $responseCollection = collect($responseData);

        foreach ($symbols as $symbol) {
            $symbolDataInResponse = $responseCollection->where('symbol', $symbol)->first();

            $this->assertIsArray($symbolDataInResponse);
            $this->assertNotEmpty($symbolDataInResponse);
        }
    }
}
