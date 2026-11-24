<?php

/**
 * This file is generated from the OpenAPI specification by bin/generate.
 * Do not edit it by hand; regenerate it instead.
 */

declare(strict_types=1);

namespace OpusDNS\Client\Model;

use OpusDNS\Client\ApiModel;
use OpusDNS\Client\Enum\DomainAvailabilityStatus;
use OpusDNS\Client\Serializer;

final class DomainAvailability implements ApiModel
{
    /**
     * @param string $domainAce The checked name in its ASCII form.
     * @param string $domainUnicode The checked name in Unicode.
     * @param DomainAvailabilityStatus|string $status `unavailable` also covers reserved and invalid names (see
     *     `error`). Retry `error` and `unknown` later. `tmch_claim` is not returned at present.
     * @param DomainAvailabilityError|null $error Why the domain could not be checked: a `validation_error` (the name
     *     is invalid; status `unavailable`) or a `check_error` (status `error`). Null otherwise; a status `error`
     *     may also come without one.
     * @param bool|null $isPremium True if the name, registered or not, is on its registry's premium list; null when
     *     not known.
     * @param Marketplace|null $marketplace The marketplace a taken domain can be bought on; set on
     *     `market_available` results of `POST /v1/availability/stream` with `marketplaces`; null otherwise, or when
     *     the marketplace is not known yet.
     */
    public function __construct(
        public readonly string $domain,
        public readonly string $domainAce,
        public readonly string $domainUnicode,
        public readonly DomainAvailabilityStatus|string $status,
        public readonly ?DomainAvailabilityError $error = null,
        public readonly ?bool $isPremium = null,
        public readonly ?Marketplace $marketplace = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): static
    {
        return new self(
            domain: $data['domain'],
            domainAce: $data['domain_ace'],
            domainUnicode: $data['domain_unicode'],
            status: DomainAvailabilityStatus::tryFrom($data['status']) ?? $data['status'],
            error: isset($data['error']) ? DomainAvailabilityError::fromArray($data['error']) : null,
            isPremium: $data['is_premium'] ?? null,
            marketplace: isset($data['marketplace']) ? Marketplace::fromArray($data['marketplace']) : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return Serializer::normalize([
            'domain' => $this->domain,
            'domain_ace' => $this->domainAce,
            'domain_unicode' => $this->domainUnicode,
            'status' => $this->status,
            'error' => $this->error,
            'is_premium' => $this->isPremium,
            'marketplace' => $this->marketplace,
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
