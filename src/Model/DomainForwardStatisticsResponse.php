<?php

/**
 * This file is generated from the OpenAPI specification by bin/generate.
 * Do not edit it by hand; regenerate it instead.
 */

declare(strict_types=1);

namespace OpusDNS\Client\Model;

use OpusDNS\Client\ApiModel;
use OpusDNS\Client\Enum\DomainForwardStatisticsGranularity;
use OpusDNS\Client\Serializer;

final readonly class DomainForwardStatisticsResponse implements ApiModel
{
    /**
     * @param list<DomainForwardStatisticsBucket> $buckets One entry per bucket the window touches, oldest first,
     *     including buckets without redirects (0)
     * @param \DateTimeImmutable $end End of the window as an instant (UTC), exclusive: the day after `end_date` at
     *     00:00, or `end` as requested
     * @param \DateTimeImmutable $endDate Last day of the window, inclusive (UTC); for a window given as `start` and
     *     `end`, the day of its last instant
     * @param DomainForwardStatisticsGranularity|string $granularity Time-bucket size of the series
     * @param \DateTimeImmutable $start Start of the window as an instant (UTC), inclusive: `start_date` at 00:00, or
     *     `start` as requested
     * @param \DateTimeImmutable $startDate First day of the window, inclusive (UTC); for a window given as `start`
     *     and `end`, the day `start` falls in
     * @param DomainForwardStatisticsBreakdownResponse|null $breakdown The top list requested with `breakdown`; null
     *     otherwise
     * @param \DateTimeImmutable|null $dataAvailableFrom Earliest instant the data behind the answer holds: the later
     *     of the retention limit and the first ingested redirect. A window reaching further back is empty before it,
     *     which is not the same as zero redirects. Null when there is no data
     * @param \DateTimeImmutable|null $dataAvailableTo Time of the latest ingested redirect; a bucket reaching it is
     *     partial. Null when there is no data
     */
    public function __construct(
        public array $buckets,
        public \DateTimeImmutable $end,
        public \DateTimeImmutable $endDate,
        public DomainForwardStatisticsGranularity|string $granularity,
        public \DateTimeImmutable $start,
        public \DateTimeImmutable $startDate,
        public DomainForwardStatisticsTotals $totals,
        public ?DomainForwardStatisticsBreakdownResponse $breakdown = null,
        public ?\DateTimeImmutable $dataAvailableFrom = null,
        public ?\DateTimeImmutable $dataAvailableTo = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): static
    {
        return new self(
            buckets: array_map(static fn (array $item): DomainForwardStatisticsBucket => DomainForwardStatisticsBucket::fromArray($item), $data['buckets']),
            end: new \DateTimeImmutable($data['end']),
            endDate: Serializer::parseDate($data['end_date']),
            granularity: DomainForwardStatisticsGranularity::tryFrom($data['granularity']) ?? $data['granularity'],
            start: new \DateTimeImmutable($data['start']),
            startDate: Serializer::parseDate($data['start_date']),
            totals: DomainForwardStatisticsTotals::fromArray($data['totals']),
            breakdown: isset($data['breakdown']) ? DomainForwardStatisticsBreakdownResponse::fromArray($data['breakdown']) : null,
            dataAvailableFrom: isset($data['data_available_from']) ? new \DateTimeImmutable($data['data_available_from']) : null,
            dataAvailableTo: isset($data['data_available_to']) ? new \DateTimeImmutable($data['data_available_to']) : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return Serializer::normalize([
            'buckets' => $this->buckets,
            'end' => $this->end,
            'end_date' => $this->endDate->format('Y-m-d'),
            'granularity' => $this->granularity,
            'start' => $this->start,
            'start_date' => $this->startDate->format('Y-m-d'),
            'totals' => $this->totals,
            'breakdown' => $this->breakdown,
            'data_available_from' => $this->dataAvailableFrom,
            'data_available_to' => $this->dataAvailableTo,
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
