<?php

/**
 * This file is generated from the OpenAPI specification by bin/generate.
 * Do not edit it by hand; regenerate it instead.
 */

declare(strict_types=1);

namespace OpusDNS\Client\Model;

use OpusDNS\Client\ApiModel;
use OpusDNS\Client\Enum\RenewalMode;
use OpusDNS\Client\Serializer;

/**
 * The `POST /v1/domains` fields a pre-registration submits. The auth code is generated, the price is
 * confirmed through `expected_price`, and a claims notice is accepted later.
 */
final class DomainPreRegistrationRegistration implements ApiModel
{
    /**
     * @param array<string, list<ContactHandle>> $contacts The contacts of the domain
     * @param DomainPeriod $period The registration period of the domain
     * @param RenewalMode|string $renewalMode The renewal mode of the domain
     * @param array<string, string>|null $attributes Additional attributes of the domain, as on `POST /v1/domains`.
     *     Carries the confirmations the TLD's `legal_requirements` ask for, e.g. `https_support_confirmation` =
     *     `true` for `.web`.
     * @param bool $createZone Create a zone for the domain on OpusDNS nameserver infrastructure
     * @param list<Nameserver>|null $nameservers The name servers for the domain
     */
    public function __construct(
        public readonly array $contacts,
        public readonly DomainPeriod $period,
        public readonly RenewalMode|string $renewalMode,
        public readonly ?array $attributes = null,
        public readonly bool $createZone = false,
        public readonly ?array $nameservers = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): static
    {
        return new self(
            contacts: array_map(static fn (array $value): array => array_map(static fn (array $item): ContactHandle => ContactHandle::fromArray($item), $value), (array) $data['contacts']),
            period: DomainPeriod::fromArray($data['period']),
            renewalMode: RenewalMode::tryFrom($data['renewal_mode']) ?? $data['renewal_mode'],
            attributes: isset($data['attributes']) ? (array) $data['attributes'] : null,
            createZone: $data['create_zone'] ?? false,
            nameservers: isset($data['nameservers']) ? array_map(static fn (array $item): Nameserver => Nameserver::fromArray($item), $data['nameservers']) : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return Serializer::normalize([
            'contacts' => ($this->contacts === [] ? new \stdClass() : $this->contacts),
            'period' => $this->period,
            'renewal_mode' => $this->renewalMode,
            'attributes' => $this->attributes === null ? null : ($this->attributes === [] ? new \stdClass() : $this->attributes),
            'create_zone' => $this->createZone,
            'nameservers' => $this->nameservers,
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
