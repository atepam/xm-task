<?php

namespace App\Services\AlphaVantage;

use App\Exceptions\AlphaVantage\ConfigurationException;

readonly class ClientConfig
{
    public const API_HOST_NOT_PROVIDED = 'API host not provided';
    public const API_KEY_NOT_PROVIDED = 'API key not provided';

    /**
     * @throws ConfigurationException
     *
     */
    public function __construct(
        public string $apiKey,
        public string $apiHost,
    )
    {
        $this->validateApiKey();
        $this->validateApiHost();
    }

    /**
     * @throws ConfigurationException
     */
    protected function validateApiKey(): void
    {
        if (empty(trim($this->apiKey))) {
            throw new ConfigurationException(self::API_KEY_NOT_PROVIDED);
        }
    }

    /**
     * @throws ConfigurationException
     */
    protected function validateApiHost(): void
    {
        if (empty(trim($this->apiHost))) {
            throw new ConfigurationException(self::API_HOST_NOT_PROVIDED);
        }
    }
}
