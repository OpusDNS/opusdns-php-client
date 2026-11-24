<?php

/**
 * This file is generated from the OpenAPI specification by bin/generate.
 * Do not edit it by hand; regenerate it instead.
 */

declare(strict_types=1);

namespace OpusDNS\Client\Model;

use OpusDNS\Client\ApiModel;
use OpusDNS\Client\Serializer;

final class DomainUpdatePendingResponse implements ApiModel
{
    /**
     * @param string $name The domain name the update was requested for
     * @param string $requestId Identifier of this update, carried by its outcome events
     * @param string $status The registrar holding the domain has taken the update and is still completing it. The
     *     outcome is reported as domain events carrying `request_id`.
     */
    public function __construct(
        public readonly string $name,
        public readonly string $requestId,
        public readonly string $status = 'update_pending',
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): static
    {
        return new self(
            name: $data['name'],
            requestId: $data['request_id'],
            status: $data['status'] ?? 'update_pending',
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return Serializer::normalize([
            'name' => $this->name,
            'request_id' => $this->requestId,
            'status' => $this->status,
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
