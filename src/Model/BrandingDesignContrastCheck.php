<?php

/**
 * This file is generated from the OpenAPI specification by bin/generate.
 * Do not edit it by hand; regenerate it instead.
 */

declare(strict_types=1);

namespace OpusDNS\Client\Model;

use OpusDNS\Client\ApiModel;
use OpusDNS\Client\Serializer;

final readonly class BrandingDesignContrastCheck implements ApiModel
{
    public function __construct(
        public ?string $bg = null,
        public ?string $fg = null,
        public ?bool $hard = null,
        public ?string $kind = null,
        public ?float $max = null,
        public ?float $min = null,
        public ?string $mode = null,
        public ?bool $ok = null,
        public ?float $ratio = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): static
    {
        return new self(
            bg: $data['bg'] ?? null,
            fg: $data['fg'] ?? null,
            hard: $data['hard'] ?? null,
            kind: $data['kind'] ?? null,
            max: $data['max'] ?? null,
            min: $data['min'] ?? null,
            mode: $data['mode'] ?? null,
            ok: $data['ok'] ?? null,
            ratio: $data['ratio'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return Serializer::normalize([
            'bg' => $this->bg,
            'fg' => $this->fg,
            'hard' => $this->hard,
            'kind' => $this->kind,
            'max' => $this->max,
            'min' => $this->min,
            'mode' => $this->mode,
            'ok' => $this->ok,
            'ratio' => $this->ratio,
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
