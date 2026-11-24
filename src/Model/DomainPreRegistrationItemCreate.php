<?php

/**
 * This file is generated from the OpenAPI specification by bin/generate.
 * Do not edit it by hand; regenerate it instead.
 */

declare(strict_types=1);

namespace OpusDNS\Client\Model;

use OpusDNS\Client\ApiModel;
use OpusDNS\Client\Enum\DomainPreRegistrationPhase;
use OpusDNS\Client\Serializer;

/**
 * Pre-register a domain in an upcoming TLD launch. Free until the funds are held shortly before
 * submission; an item without its SMD file is accepted and lists it under `needs_action`.
 */
final class DomainPreRegistrationItemCreate implements ApiModel
{
    /**
     * @param string $domain Domain name to pre-register (Unicode or ASCII form)
     * @param DomainPreRegistrationPhase|string $phase The launch phase to submit in: one of the TLD's
     *     `launch_phases.phases[].type` or `general_availability`. A phase may already be open (it is then submitted
     *     once complete); general availability only until it begins.
     * @param DomainPreRegistrationRegistration $registration The registration submitted in the phase
     * @param string|null $expectedPrice The price you expect to pay for the registration period, a decimal string
     *     with two decimals, as on `POST /v1/domains`: the price `GET /v1/domains/check` shows. Required for a
     *     premium name, optional otherwise; a price that differs from the current one is refused with
     *     `ERROR_ORDER_PRICE_CHANGED`. The item is only submitted at this price: if the registry changes it before
     *     the funds are held, the item waits for you to confirm the new one.
     * @param string|null $signedMark The SMD file from the Trademark Clearinghouse (the whole file or its base64
     *     block). Required before a phase with `smd_required` is submitted.
     * @param string $type Item type
     */
    public function __construct(
        public readonly string $domain,
        public readonly DomainPreRegistrationPhase|string $phase,
        public readonly DomainPreRegistrationRegistration $registration,
        public readonly ?string $expectedPrice = null,
        public readonly ?string $signedMark = null,
        public readonly string $type = 'domain_pre_registration',
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): static
    {
        return new self(
            domain: $data['domain'],
            phase: DomainPreRegistrationPhase::tryFrom($data['phase']) ?? $data['phase'],
            registration: DomainPreRegistrationRegistration::fromArray($data['registration']),
            expectedPrice: $data['expected_price'] ?? null,
            signedMark: $data['signed_mark'] ?? null,
            type: $data['type'] ?? 'domain_pre_registration',
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return Serializer::normalize([
            'domain' => $this->domain,
            'phase' => $this->phase,
            'registration' => $this->registration,
            'expected_price' => $this->expectedPrice,
            'signed_mark' => $this->signedMark,
            'type' => $this->type,
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
