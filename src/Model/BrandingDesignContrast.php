<?php

/**
 * This file is generated from the OpenAPI specification by bin/generate.
 * Do not edit it by hand; regenerate it instead.
 */

declare(strict_types=1);

namespace OpusDNS\Client\Model;

use OpusDNS\Client\ApiModel;
use OpusDNS\Client\Serializer;

final readonly class BrandingDesignContrast implements ApiModel
{
    /**
     * @param list<BrandingDesignContrastCheck>|null $checks
     */
    public function __construct(
        public ?bool $allPass = null,
        public ?array $checks = null,
        public ?float $minRatio = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): static
    {
        return new self(
            allPass: $data['all_pass'] ?? null,
            checks: isset($data['checks']) ? array_map(static fn (array $item): BrandingDesignContrastCheck => BrandingDesignContrastCheck::fromArray($item), $data['checks']) : null,
            minRatio: $data['min_ratio'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return Serializer::normalize([
            'all_pass' => $this->allPass,
            'checks' => $this->checks,
            'min_ratio' => $this->minRatio,
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
