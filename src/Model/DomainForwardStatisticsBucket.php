<?php

/**
 * This file is generated from the OpenAPI specification by bin/generate.
 * Do not edit it by hand; regenerate it instead.
 */

declare(strict_types=1);

namespace OpusDNS\Client\Model;

use OpusDNS\Client\ApiModel;
use OpusDNS\Client\Serializer;

final readonly class DomainForwardStatisticsBucket implements ApiModel
{
    /**
     * @param bool $partial True when the counts are not those of the whole bucket: the window cuts the bucket, it
     *     begins before `data_available_from`, or it reaches `data_available_to`, which is still running. A bucket
     *     without any data is partial too. A partial bucket must not be read as a rise or fall against its
     *     neighbours
     * @param \DateTimeImmutable $periodEnd First instant after the bucket (UTC), exclusive: the start of the next
     *     bucket
     * @param \DateTimeImmutable $periodStart First instant of the bucket (UTC), inclusive
     * @param int $totalVisits Number of redirects served in the bucket; 0 when none were
     * @param int $uniqueVisits Estimated number of distinct visitors (distinct client IP addresses, stored only as a
     *     keyed hash), counted with a HyperLogLog sketch and merged over the whole window rather than summed over
     *     buckets, so the figures of buckets do not add up to the total. Counted per bucket; 0 when none were
     */
    public function __construct(
        public bool $partial,
        public \DateTimeImmutable $periodEnd,
        public \DateTimeImmutable $periodStart,
        public int $totalVisits,
        public int $uniqueVisits,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): static
    {
        return new self(
            partial: $data['partial'],
            periodEnd: new \DateTimeImmutable($data['period_end']),
            periodStart: new \DateTimeImmutable($data['period_start']),
            totalVisits: $data['total_visits'],
            uniqueVisits: $data['unique_visits'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return Serializer::normalize([
            'partial' => $this->partial,
            'period_end' => $this->periodEnd,
            'period_start' => $this->periodStart,
            'total_visits' => $this->totalVisits,
            'unique_visits' => $this->uniqueVisits,
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
