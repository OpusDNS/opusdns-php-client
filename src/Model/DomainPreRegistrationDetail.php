<?php

/**
 * This file is generated from the OpenAPI specification by bin/generate.
 * Do not edit it by hand; regenerate it instead.
 */

declare(strict_types=1);

namespace OpusDNS\Client\Model;

use OpusDNS\Client\ApiModel;
use OpusDNS\Client\Enum\DomainPreRegistrationNeedsAction;
use OpusDNS\Client\Enum\DomainPreRegistrationPhase;
use OpusDNS\Client\Enum\DomainPreRegistrationPublicStage;
use OpusDNS\Client\Serializer;

/**
 * The `domain_pre_registration` part of an item.
 */
final class DomainPreRegistrationDetail implements ApiModel
{
    /**
     * @param string $domain Domain name (ASCII form)
     * @param \DateTimeImmutable $holdOn When the funds are held at the then current price; what `needs_action` lists
     *     must be provided by then
     * @param list<string> $missingConfirmations The keys of the TLD's `legal_requirements` confirmations not yet
     *     given (with `confirmation_required`), e.g. `HTTPS_SUPPORT_CONFIRMATION`
     * @param list<DomainPreRegistrationNeedsAction|string> $needsAction What the customer must still provide. Treat
     *     a value you do not know as an open action: codes can be added.
     * @param DomainPreRegistrationPhase|string $phase The launch phase the item is submitted in. Treat a value you
     *     do not know as a phase: phases can be added.
     * @param \DateTimeImmutable $phaseStartsOn When the phase opens
     * @param bool $premium Whether the domain is premium; `price` is then the registry's premium price
     * @param DomainPreRegistrationPublicStage|string $stage Where the pre-registration stands. Treat a value you do
     *     not know as `requested`: stages can be added.
     */
    public function __construct(
        public readonly string $domain,
        public readonly \DateTimeImmutable $holdOn,
        public readonly array $missingConfirmations,
        public readonly array $needsAction,
        public readonly DomainPreRegistrationPhase|string $phase,
        public readonly \DateTimeImmutable $phaseStartsOn,
        public readonly bool $premium,
        public readonly DomainPreRegistrationPublicStage|string $stage,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): static
    {
        return new self(
            domain: $data['domain'],
            holdOn: new \DateTimeImmutable($data['hold_on']),
            missingConfirmations: $data['missing_confirmations'],
            needsAction: array_map(static fn (string $item): DomainPreRegistrationNeedsAction|string => DomainPreRegistrationNeedsAction::tryFrom($item) ?? $item, $data['needs_action']),
            phase: DomainPreRegistrationPhase::tryFrom($data['phase']) ?? $data['phase'],
            phaseStartsOn: new \DateTimeImmutable($data['phase_starts_on']),
            premium: $data['premium'],
            stage: DomainPreRegistrationPublicStage::tryFrom($data['stage']) ?? $data['stage'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return Serializer::normalize([
            'domain' => $this->domain,
            'hold_on' => $this->holdOn,
            'missing_confirmations' => $this->missingConfirmations,
            'needs_action' => $this->needsAction,
            'phase' => $this->phase,
            'phase_starts_on' => $this->phaseStartsOn,
            'premium' => $this->premium,
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
