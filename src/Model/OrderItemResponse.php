<?php

/**
 * This file is generated from the OpenAPI specification by bin/generate.
 * Do not edit it by hand; regenerate it instead.
 */

declare(strict_types=1);

namespace OpusDNS\Client\Model;

use OpusDNS\Client\ApiModel;
use OpusDNS\Client\Enum\Currency;
use OpusDNS\Client\Serializer;

/**
 * The fields every item type has, plus one optional object named after `type`. A new type adds one
 * field.
 */
final readonly class OrderItemResponse implements ApiModel
{
    /**
     * @param Currency|string $currency Currency of `price`
     * @param string $itemId TypeID prefix: order_item.
     * @param string $price Price of the item, a decimal string with two decimals
     * @param string $status `pending`, `in_progress`, `completed`, `canceled`, `failed`. `pending` and `in_progress`
     *     are open, the others are final. Treat a value you do not know as open: statuses can be added.
     * @param string $type Item type: `marketplace_domain_purchase`
     * @param MarketplaceDomainPurchaseDetail|null $marketplaceDomainPurchase Set when `type` is
     *     `marketplace_domain_purchase`
     */
    public function __construct(
        public Currency|string $currency,
        public string $itemId,
        public string $price,
        public string $status,
        public string $type,
        public ?MarketplaceDomainPurchaseDetail $marketplaceDomainPurchase = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): static
    {
        return new self(
            currency: Currency::tryFrom($data['currency']) ?? $data['currency'],
            itemId: $data['item_id'],
            price: $data['price'],
            status: $data['status'],
            type: $data['type'],
            marketplaceDomainPurchase: isset($data['marketplace_domain_purchase']) ? MarketplaceDomainPurchaseDetail::fromArray($data['marketplace_domain_purchase']) : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return Serializer::normalize([
            'currency' => $this->currency,
            'item_id' => $this->itemId,
            'price' => $this->price,
            'status' => $this->status,
            'type' => $this->type,
            'marketplace_domain_purchase' => $this->marketplaceDomainPurchase,
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
