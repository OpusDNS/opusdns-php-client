<?php

declare(strict_types=1);

namespace OpusDNS\Client\Tests\Support;

use GuzzleHttp\Psr7\HttpFactory;
use OpusDNS\Client\Client;
use OpusDNS\Client\Config;

final class ClientFactory
{
    /** @return array{Client, FakeHttpClient} */
    public static function create(?Config $config = null): array
    {
        $http = new FakeHttpClient();
        $factory = new HttpFactory();

        return [new Client($config ?? Config::sandbox('test-key'), $http, $factory, $factory), $http];
    }
}
