<?php

/**
 * This file is generated from the OpenAPI specification by bin/generate.
 * Do not edit it by hand; regenerate it instead.
 */

declare(strict_types=1);

namespace OpusDNS\Client\Model;

use OpusDNS\Client\ApiModel;
use OpusDNS\Client\Serializer;

/**
 * The outcome of one part of an update that completed after the request was answered with 202.
 *
 * One event per part, each carrying the request id the 202 returned, so an update made of several
 * parts reports each one without the shape changing.
 */
final class DomainModificationDetails implements ApiModel
{
    public function __construct(
        public readonly DomainModificationPart $part,
        public readonly string $requestId,
        public readonly string $detailType = 'domain_modification',
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): static
    {
        return new self(
            part: DomainModificationPart::fromArray($data['part']),
            requestId: $data['request_id'],
            detailType: $data['detail_type'] ?? 'domain_modification',
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return Serializer::normalize([
            'part' => $this->part,
            'request_id' => $this->requestId,
            'detail_type' => $this->detailType,
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
