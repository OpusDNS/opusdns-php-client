<?php

/**
 * This file is generated from the OpenAPI specification by bin/generate.
 * Do not edit it by hand; regenerate it instead.
 */

declare(strict_types=1);

namespace OpusDNS\Client\Model;

use OpusDNS\Client\ApiModel;
use OpusDNS\Client\Serializer;

final readonly class BrandingDesignVariant implements ApiModel
{
    /**
     * @param array<string, mixed> $document
     * @param list<string>|null $changedSeeds
     * @param list<BrandingDesignPhrase>|null $rationaleParts
     */
    public function __construct(
        public array $document,
        public string $variantId,
        public ?array $changedSeeds = null,
        public ?BrandingDesignContrast $contrast = null,
        public string $label = '',
        public ?BrandingDesignPreview $preview = null,
        public string $rationale = '',
        public ?array $rationaleParts = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): static
    {
        return new self(
            document: (array) $data['document'],
            variantId: $data['variant_id'],
            changedSeeds: $data['changed_seeds'] ?? null,
            contrast: isset($data['contrast']) ? BrandingDesignContrast::fromArray($data['contrast']) : null,
            label: $data['label'] ?? '',
            preview: isset($data['preview']) ? BrandingDesignPreview::fromArray($data['preview']) : null,
            rationale: $data['rationale'] ?? '',
            rationaleParts: isset($data['rationale_parts']) ? array_map(static fn (array $item): BrandingDesignPhrase => BrandingDesignPhrase::fromArray($item), $data['rationale_parts']) : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return Serializer::normalize([
            'document' => ($this->document === [] ? new \stdClass() : $this->document),
            'variant_id' => $this->variantId,
            'changed_seeds' => $this->changedSeeds,
            'contrast' => $this->contrast,
            'label' => $this->label,
            'preview' => $this->preview,
            'rationale' => $this->rationale,
            'rationale_parts' => $this->rationaleParts,
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
