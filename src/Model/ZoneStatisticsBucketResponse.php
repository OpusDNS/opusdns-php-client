<?php

/**
 * This file is generated from the OpenAPI specification by bin/generate.
 * Do not edit it by hand; regenerate it instead.
 */

declare(strict_types=1);

namespace OpusDNS\Client\Model;

use OpusDNS\Client\ApiModel;
use OpusDNS\Client\Serializer;

final readonly class ZoneStatisticsBucketResponse implements ApiModel
{
    /**
     * @param int $nxdomainCount Of those, queries answered with NXDOMAIN
     * @param bool $partial The bucket counts only part of its span: it reaches outside the window (a week or month
     *     the window cuts), part of it lies before `data_available_from` or after `data_available_to`, or the zone
     *     was created during it. A partial bucket must not be read as a rise or fall against its neighbours.
     * @param \DateTimeImmutable $periodStart First day of the bucket (UTC)
     * @param int $queryCount Queries answered for the zone in this bucket, within the window
     */
    public function __construct(
        public int $nxdomainCount,
        public bool $partial,
        public \DateTimeImmutable $periodStart,
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
            partial: $data['partial'],
            periodStart: Serializer::parseDate($data['period_start']),
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
            'partial' => $this->partial,
            'period_start' => $this->periodStart->format('Y-m-d'),
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
