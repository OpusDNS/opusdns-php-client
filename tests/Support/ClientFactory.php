<?php

declare(strict_types=1);

namespace OpusDNS\Client\Tests\Support;

use OpusDNS\Client\Client;
use OpusDNS\Client\Config;
use OpusDNS\Client\Testing\FakeHttpClient;

final class ClientFactory
{
    /** @return array{Client, FakeHttpClient} */
    public static function create(?Config $config = null): array
    {
        $http = new FakeHttpClient();

        return [$http->client($config ?? Config::sandbox('test-key')), $http];
    }
}
