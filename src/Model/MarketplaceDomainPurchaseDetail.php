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
 * The `marketplace_domain_purchase` part of an item.
 */
final readonly class MarketplaceDomainPurchaseDetail implements ApiModel
{
    /**
     * @param string $domain Domain name (ASCII form)
     * @param string $marketplace Marketplace the domain is bought on: `fruits`. More can be added.
     * @param string $stage Where the purchase stands: `processing`, `purchasing`, `awaiting_code`, `code_ready`,
     *     `completed`, `failed`, `canceled`. Treat a value you do not know as `processing`: stages can be added.
     */
    public function __construct(
        public string $domain,
        public string $marketplace,
        public string $stage,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): static
    {
        return new self(
            domain: $data['domain'],
            marketplace: $data['marketplace'],
            stage: $data['stage'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return Serializer::normalize([
            'domain' => $this->domain,
            'marketplace' => $this->marketplace,
            'stage' => $this->stage,
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
