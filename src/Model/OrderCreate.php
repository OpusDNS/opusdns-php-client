<?php

/**
 * This file is generated from the OpenAPI specification by bin/generate.
 * Do not edit it by hand; regenerate it instead.
 */

declare(strict_types=1);

namespace OpusDNS\Client\Model;

use OpusDNS\Client\ApiModel;
use OpusDNS\Client\Serializer;
use OpusDNS\Client\Union;

final class OrderCreate implements ApiModel
{
    /**
     * @param string $idempotencyKey Caller-chosen key, unique per organization. Repeating a request with the same
     *     key and body returns the original order.
     * @param list<MarketplaceDomainPurchaseItemCreate|DomainPreRegistrationItemCreate> $items Items to order (one for now)
     */
    public function __construct(
        public readonly string $idempotencyKey,
        public readonly array $items,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): static
    {
        return new self(
            idempotencyKey: $data['idempotency_key'],
            items: array_map(static fn (array $item) => Union::hydrate($item, [DomainPreRegistrationItemCreate::class => ['type', 'domain', 'phase', 'registration'], MarketplaceDomainPurchaseItemCreate::class => ['domain', 'marketplace', 'expected_price']]), $data['items']),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return Serializer::normalize([
            'idempotency_key' => $this->idempotencyKey,
            'items' => $this->items,
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
