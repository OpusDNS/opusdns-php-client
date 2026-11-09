<?php

/**
 * This file is generated from the OpenAPI specification by bin/generate.
 * Do not edit it by hand; regenerate it instead.
 */

declare(strict_types=1);

namespace OpusDNS\Client\Model;

use OpusDNS\Client\ApiModel;
use OpusDNS\Client\Enum\DomainForwardStatisticsDimension;
use OpusDNS\Client\Serializer;

final readonly class DomainForwardStatisticsBreakdownResponse implements ApiModel
{
    /**
     * @param bool $approximate True when the list is an approximation: for `user_agent`, `referrer`, `fqdn`,
     *     `domain`, `forward` and `rule`, a window of more than one day is answered from per-day top lists, so the
     *     counts are sums of those lists and a value that never made a day's top list is missing or undercounted.
     *     `url` is built from the daily top 5 lists of the domain, so it is an approximation as soon as those lists
     *     are read. Always false for `browser`, `platform`, `country` and `status_code`, and for any query filtered
     *     by `hostname` or `protocol` (except `url`, which cannot be)
     * @param DomainForwardStatisticsDimension|string $dimension What the redirects are grouped by. Country data is
     *     derived from IP addresses with GeoLite2 data created by MaxMind (https://www.maxmind.com)
     * @param list<DomainForwardStatisticsBreakdownItem> $items The groups with the most redirects, most first, at
     *     most `breakdown_limit`. For `url` at most 5 values per day went into the list, each with at least 3 hits
     *     that day
     */
    public function __construct(
        public bool $approximate,
        public DomainForwardStatisticsDimension|string $dimension,
        public array $items,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): static
    {
        return new self(
            approximate: $data['approximate'],
            dimension: DomainForwardStatisticsDimension::tryFrom($data['dimension']) ?? $data['dimension'],
            items: array_map(static fn (array $item): DomainForwardStatisticsBreakdownItem => DomainForwardStatisticsBreakdownItem::fromArray($item), $data['items']),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return Serializer::normalize([
            'approximate' => $this->approximate,
            'dimension' => $this->dimension,
            'items' => $this->items,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
