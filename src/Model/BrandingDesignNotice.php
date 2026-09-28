<?php

/**
 * This file is generated from the OpenAPI specification by bin/generate.
 * Do not edit it by hand; regenerate it instead.
 */

declare(strict_types=1);

namespace OpusDNS\Client\Model;

use OpusDNS\Client\ApiModel;
use OpusDNS\Client\Serializer;

final readonly class BrandingDesignNotice implements ApiModel
{
    /**
     * @param array<string, string|int|float|bool|list<string>>|null $params
     * @param list<string>|null $variants
     */
    public function __construct(
        public string $code,
        public string $severity,
        public string $message = '',
        public ?array $params = null,
        public ?array $variants = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): static
    {
        return new self(
            code: $data['code'],
            severity: $data['severity'],
            message: $data['message'] ?? '',
            params: isset($data['params']) ? (array) $data['params'] : null,
            variants: $data['variants'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return Serializer::normalize([
            'code' => $this->code,
            'severity' => $this->severity,
            'message' => $this->message,
            'params' => $this->params === null ? null : ($this->params === [] ? new \stdClass() : $this->params),
            'variants' => $this->variants,
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
