<?php

/**
 * This file is generated from the OpenAPI specification by bin/generate.
 * Do not edit it by hand; regenerate it instead.
 */

declare(strict_types=1);

namespace OpusDNS\Client\Model;

use OpusDNS\Client\ApiModel;
use OpusDNS\Client\Enum\LegalRequirementOperationType;
use OpusDNS\Client\Enum\LegalRequirementType;
use OpusDNS\Client\Serializer;

final readonly class LegalRequirementBase implements ApiModel
{
    /**
     * @param string $key Unique identifier for the legal requirement
     * @param list<LegalRequirementOperationType> $operations Operations this requirement applies to
     * @param LegalRequirementType $type Whether this is an informational notice or requires explicit confirmation
     * @param string|null $url Link to the legal document
     */
    public function __construct(
        public string $key,
        public array $operations,
        public LegalRequirementType $type,
        public ?string $url = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): static
    {
        return new self(
            key: $data['key'],
            operations: array_map(static fn (string $item): LegalRequirementOperationType => LegalRequirementOperationType::from($item), $data['operations']),
            type: LegalRequirementType::from($data['type']),
            url: $data['url'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return Serializer::normalize([
            'key' => $this->key,
            'operations' => $this->operations,
            'type' => $this->type,
            'url' => $this->url,
        ]);
    }
}
