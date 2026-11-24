<?php

/**
 * This file is generated from the OpenAPI specification by bin/generate.
 * Do not edit it by hand; regenerate it instead.
 */

declare(strict_types=1);

namespace OpusDNS\Client\Model;

use OpusDNS\Client\ApiModel;
use OpusDNS\Client\Enum\DomainModificationPartStatus;
use OpusDNS\Client\Serializer;

/**
 * One part of an update: the fields a single registrar call carries, and how it ended.
 */
final class DomainModificationPart implements ApiModel
{
    /**
     * @param list<string> $fields
     */
    public function __construct(
        public readonly array $fields,
        public readonly DomainModificationPartStatus|string $status,
        public readonly mixed $after = null,
        public readonly mixed $before = null,
        public readonly ?string $message = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): static
    {
        return new self(
            fields: $data['fields'],
            status: DomainModificationPartStatus::tryFrom($data['status']) ?? $data['status'],
            after: $data['after'] ?? null,
            before: $data['before'] ?? null,
            message: $data['message'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return Serializer::normalize([
            'fields' => $this->fields,
            'status' => $this->status,
            'after' => $this->after,
            'before' => $this->before,
            'message' => $this->message,
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
