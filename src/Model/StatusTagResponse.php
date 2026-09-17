<?php

/**
 * This file is generated from the OpenAPI specification by bin/generate.
 * Do not edit it by hand; regenerate it instead.
 */

declare(strict_types=1);

namespace OpusDNS\Client\Model;

use OpusDNS\Client\ApiModel;
use OpusDNS\Client\Enum\StatusTagType;
use OpusDNS\Client\Enum\TagColor;
use OpusDNS\Client\Serializer;

final readonly class StatusTagResponse implements ApiModel
{
    /**
     * @param TagColor $color The color of the tag
     * @param string $label The label of the tag
     * @param StatusTagType $tagType The status tag type identifier
     * @param string|null $description Additional information about this status tag
     */
    public function __construct(
        public TagColor $color,
        public string $label,
        public StatusTagType $tagType,
        public ?string $description = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): static
    {
        return new self(
            color: TagColor::from($data['color']),
            label: $data['label'],
            tagType: StatusTagType::from($data['tag_type']),
            description: $data['description'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return Serializer::normalize([
            'color' => $this->color,
            'label' => $this->label,
            'tag_type' => $this->tagType,
            'description' => $this->description,
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
