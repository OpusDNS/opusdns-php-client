<?php

/**
 * This file is generated from the OpenAPI specification by bin/generate.
 * Do not edit it by hand; regenerate it instead.
 */

declare(strict_types=1);

namespace OpusDNS\Client\Model;

use OpusDNS\Client\ApiModel;
use OpusDNS\Client\Serializer;

final readonly class ZoneStatisticsTotalsResponse implements ApiModel
{
    /**
     * @param int $nxdomainCount Of those, queries answered with NXDOMAIN
     * @param int $queryCount Queries answered for the zone in the window
     */
    public function __construct(
        public int $nxdomainCount,
        public int $queryCount,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): static
    {
        return new self(
            nxdomainCount: $data['nxdomain_count'],
            queryCount: $data['query_count'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return Serializer::normalize([
            'nxdomain_count' => $this->nxdomainCount,
            'query_count' => $this->queryCount,
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
