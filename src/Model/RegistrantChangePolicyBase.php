<?php

/**
 * This file is generated from the OpenAPI specification by bin/generate.
 * Do not edit it by hand; regenerate it instead.
 */

declare(strict_types=1);

namespace OpusDNS\Client\Model;

use OpusDNS\Client\ApiModel;
use OpusDNS\Client\Enum\RegistrantChangeConfirmationType;
use OpusDNS\Client\Enum\RegistrantChangeFeeType;
use OpusDNS\Client\Serializer;

final readonly class RegistrantChangePolicyBase implements ApiModel
{
    /**
     * @param RegistrantChangeConfirmationType|string $confirmation Who confirms a change of registrant before it is
     *     executed
     * @param bool $designatedAgentAllowed Whether a Designated Agent may confirm the change on behalf of a
     *     registrant
     * @param RegistrantChangeFeeType|string $fee Whether the change is charged as a trade
     * @param bool $priorRegistrantNotice Whether the prior registrant is notified of the change
     * @param bool $renewsDomain Whether the change renews the domain
     * @param string $transferLockAfterChange Inter-registrar transfer lock applied after the change (e.g., '60D'),
     *     '0D' disables it
     * @param string|null $confirmationWindow How long the parties have to confirm the change (e.g., '14D'), at most
     *     60 days
     */
    public function __construct(
        public RegistrantChangeConfirmationType|string $confirmation,
        public bool $designatedAgentAllowed,
        public RegistrantChangeFeeType|string $fee,
        public bool $priorRegistrantNotice,
        public bool $renewsDomain,
        public string $transferLockAfterChange,
        public ?string $confirmationWindow = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): static
    {
        return new self(
            confirmation: RegistrantChangeConfirmationType::tryFrom($data['confirmation']) ?? $data['confirmation'],
            designatedAgentAllowed: $data['designated_agent_allowed'],
            fee: RegistrantChangeFeeType::tryFrom($data['fee']) ?? $data['fee'],
            priorRegistrantNotice: $data['prior_registrant_notice'],
            renewsDomain: $data['renews_domain'],
            transferLockAfterChange: $data['transfer_lock_after_change'],
            confirmationWindow: $data['confirmation_window'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return Serializer::normalize([
            'confirmation' => $this->confirmation,
            'designated_agent_allowed' => $this->designatedAgentAllowed,
            'fee' => $this->fee,
            'prior_registrant_notice' => $this->priorRegistrantNotice,
            'renews_domain' => $this->renewsDomain,
            'transfer_lock_after_change' => $this->transferLockAfterChange,
            'confirmation_window' => $this->confirmationWindow,
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
