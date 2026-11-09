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

final readonly class OrderResponse implements ApiModel
{
    /**
     * @param Currency|string $currency Currency of `total_price` and of every item price
     * @param list<OrderItemResponse> $items
     * @param string $orderId TypeID prefix: order.
     * @param string $status `pending`, `in_progress`, `completed`, `canceled`, `failed`. `pending` and `in_progress`
     *     are open, the others are final. Treat a value you do not know as open: statuses can be added.
     * @param string $totalPrice Sum of the item prices, a decimal string with two decimals
     */
    public function __construct(
        public \DateTimeImmutable $createdOn,
        public Currency|string $currency,
        public array $items,
        public string $orderId,
        public string $status,
        public string $totalPrice,
        public \DateTimeImmutable $updatedOn,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): static
    {
        return new self(
            createdOn: new \DateTimeImmutable($data['created_on']),
            currency: Currency::tryFrom($data['currency']) ?? $data['currency'],
            items: array_map(static fn (array $item): OrderItemResponse => OrderItemResponse::fromArray($item), $data['items']),
            orderId: $data['order_id'],
            status: $data['status'],
            totalPrice: $data['total_price'],
            updatedOn: new \DateTimeImmutable($data['updated_on']),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return Serializer::normalize([
            'created_on' => $this->createdOn,
            'currency' => $this->currency,
            'items' => $this->items,
            'order_id' => $this->orderId,
            'status' => $this->status,
            'total_price' => $this->totalPrice,
            'updated_on' => $this->updatedOn,
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
