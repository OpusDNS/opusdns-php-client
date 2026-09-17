<?php

/**
 * This file is generated from the OpenAPI specification by bin/generate.
 * Do not edit it by hand; regenerate it instead.
 */

declare(strict_types=1);

namespace OpusDNS\Client\Model;

use OpusDNS\Client\ApiModel;
use OpusDNS\Client\Enum\UsageGranularity;
use OpusDNS\Client\Serializer;

final readonly class AiInferenceUsageSeriesResponse implements ApiModel
{
    /**
     * @param list<AiInferenceUsageBucket> $buckets
     */
    public function __construct(
        public array $buckets,
        public \DateTimeImmutable $endDate,
        public UsageGranularity $granularity,
        public \DateTimeImmutable $startDate,
        public string $product = 'ai_inference',
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): static
    {
        return new self(
            buckets: array_map(static fn (array $item): AiInferenceUsageBucket => AiInferenceUsageBucket::fromArray($item), $data['buckets']),
            endDate: new \DateTimeImmutable($data['end_date']),
            granularity: UsageGranularity::from($data['granularity']),
            startDate: new \DateTimeImmutable($data['start_date']),
            product: $data['product'] ?? 'ai_inference',
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return Serializer::normalize([
            'buckets' => $this->buckets,
            'end_date' => $this->endDate,
            'granularity' => $this->granularity,
            'start_date' => $this->startDate,
            'product' => $this->product,
        ]);
    }
}
