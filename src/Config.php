<?php

declare(strict_types=1);

namespace OpusDNS\Client;

final readonly class Config
{
    public const PRODUCTION_URL = 'https://api.opusdns.com';
    public const SANDBOX_URL = 'https://sandbox.opusdns.com';
    public const USER_AGENT = 'opusdns-php-client';

    public string $baseUrl;

    /**
     * @param string $apiKey API key sent in the X-Api-Key header
     * @param string $baseUrl Production or sandbox URL, without a trailing slash
     * @param array<string, string> $headers Extra headers sent with every request
     * @param float $timeout Request timeout in seconds, used when Client::create() builds the Guzzle client
     * @param float $connectTimeout Connection timeout in seconds, used when Client::create() builds the Guzzle client
     */
    public function __construct(
        public string $apiKey,
        string $baseUrl = self::PRODUCTION_URL,
        public string $userAgent = self::USER_AGENT,
        public array $headers = [],
        public float $timeout = 60.0,
        public float $connectTimeout = 10.0,
    ) {
        if (trim($apiKey) === '') {
            throw new \InvalidArgumentException('An API key is required.');
        }
        if (filter_var($baseUrl, FILTER_VALIDATE_URL) === false) {
            throw new \InvalidArgumentException("Invalid base URL: {$baseUrl}");
        }
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    public static function production(string $apiKey): self
    {
        return new self($apiKey);
    }

    public static function sandbox(string $apiKey): self
    {
        return new self($apiKey, self::SANDBOX_URL);
    }
}
