<?php

declare(strict_types=1);

namespace OpusDNS\Client\Tests\Support;

use OpusDNS\Client\Model\DnsZoneResponse;
use OpusDNS\Client\Model\PaginationDnsZoneResponse;
use OpusDNS\Client\Model\PaginationMetadata;

/**
 * Serves prepared pages by number and records which numbers were requested.
 */
final class PageFetcher
{
    /** @var list<int> */
    public array $requested = [];

    /** @param array<int, PaginationDnsZoneResponse> $pages */
    public function __construct(private readonly array $pages)
    {
    }

    /** @param list<string> $names */
    public static function page(int $number, int $totalPages, bool $hasNext, array $names): PaginationDnsZoneResponse
    {
        return new PaginationDnsZoneResponse(
            new PaginationMetadata($number, $hasNext, $number > 1, 2, 3, $totalPages),
            array_map(static fn (string $name): DnsZoneResponse => new DnsZoneResponse("zone_{$name}", $name), $names),
        );
    }

    public function __invoke(int $number): PaginationDnsZoneResponse
    {
        $this->requested[] = $number;

        return $this->pages[$number] ?? throw new \LogicException("No page {$number} was prepared");
    }
}
