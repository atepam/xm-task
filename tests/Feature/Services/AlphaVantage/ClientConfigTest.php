<?php

namespace Tests\Feature\Services\AlphaVantage;


// use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Exceptions\AlphaVantage\ConfigurationException;
use App\Services\AlphaVantage\ClientConfig;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ClientConfigTest extends TestCase
{
    use LazilyRefreshDatabase;

    #[Test]
    public function config_can_be_instantiated_with_proper_config(): void
    {
        $this->assertInstanceOf(
            ClientConfig::class,
            new ClientConfig(fake()->word(), fake()->word())
        );
    }

    #[Test]
    public function config_throws_exception_when_missing_api_key(): void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage(ClientConfig::API_KEY_NOT_PROVIDED);

        new ClientConfig('', fake()->word());
    }

    #[Test]
    public function config_throws_exception_when_missing_api_host(): void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage(ClientConfig::API_HOST_NOT_PROVIDED);

        new ClientConfig(fake()->word(), '');
    }
}
