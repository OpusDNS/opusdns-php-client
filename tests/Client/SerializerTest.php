<?php

declare(strict_types=1);

namespace OpusDNS\Client\Tests\Client;

use OpusDNS\Client\Enum\DnssecStatus;
use OpusDNS\Client\Model\DnsZoneCreate;
use OpusDNS\Client\Serializer;
use PHPUnit\Framework\TestCase;

final class SerializerTest extends TestCase
{
    public function testNormalizeConvertsModelsEnumsAndDates(): void
    {
        $date = new \DateTimeImmutable('2026-05-06T07:08:09+02:00');

        self::assertSame([
            'zone' => ['name' => 'example.com', 'dnssec_status' => 'enabled'],
            'status' => 'disabled',
            'when' => '2026-05-06T05:08:09Z',
            'list' => [1, null, 'x'],
            'nested' => ['keep' => 0, 'inner' => ['x' => false]],
        ], Serializer::normalize([
            'zone' => new DnsZoneCreate('example.com', DnssecStatus::ENABLED),
            'status' => DnssecStatus::DISABLED,
            'when' => $date,
            'drop' => null,
            'list' => [1, null, 'x'],
            'nested' => ['keep' => 0, 'gone' => null, 'inner' => ['x' => false]],
        ]));
    }

    public function testNormalizeRejectsUnknownObjects(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Serializer::normalize(new \DateTimeZone('UTC'));
    }

    public function testNormalizePassesEmptyObjectsThrough(): void
    {
        $normalized = Serializer::normalize(['map' => new \stdClass(), 'list' => []]);

        self::assertSame('{"map":{},"list":[]}', json_encode($normalized, JSON_THROW_ON_ERROR));
    }

    public function testQueryAndPath(): void
    {
        self::assertSame('', Serializer::query(['a' => null]));
        self::assertSame('?a=1&b=x%2Fy&c=false&d=enabled', Serializer::query(['a' => 1, 'b' => 'x/y', 'c' => false, 'd' => DnssecStatus::ENABLED]));
        self::assertSame('/v1/dns/a%2Fb/x', Serializer::path('/v1/dns/{zone}/x', ['zone' => 'a/b']));
        self::assertSame('/v1/x/enabled', Serializer::path('/v1/x/{s}', ['s' => DnssecStatus::ENABLED]));
    }

    public function testParseDateIsStrictAndTimezoneIndependent(): void
    {
        $previous = date_default_timezone_get();
        date_default_timezone_set('America/Los_Angeles');
        try {
            $date = Serializer::parseDate('2026-09-01');
            self::assertSame('2026-09-01T00:00:00+00:00', $date->format(DATE_ATOM));
        } finally {
            date_default_timezone_set($previous);
        }

        $this->expectException(\UnexpectedValueException::class);
        Serializer::parseDate('2026-09-01T00:00:00Z');
    }

    public function testQueryRejectsNestedArrays(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Serializer::query(['a' => [['nested' => 1]]]);
    }
}
