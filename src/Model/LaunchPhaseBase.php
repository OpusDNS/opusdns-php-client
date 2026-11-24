<?php

/**
 * This file is generated from the OpenAPI specification by bin/generate.
 * Do not edit it by hand; regenerate it instead.
 */

declare(strict_types=1);

namespace OpusDNS\Client\Model;

use OpusDNS\Client\ApiModel;
use OpusDNS\Client\Enum\AllocationMethodType;
use OpusDNS\Client\Enum\LaunchPhaseSubmitMode;
use OpusDNS\Client\Enum\LaunchPhaseType;
use OpusDNS\Client\Serializer;

final class LaunchPhaseBase implements ApiModel
{
    /**
     * @param bool $supported Whether this phase is supported
     * @param LaunchPhaseType|string $type Type of launch phase
     * @param AllocationMethodType|string|null $allocation Allocation method
     * @param \DateTimeImmutable|null $endDate End date of the phase
     * @param bool|null $smdRequired Whether an SMD file is required for participation
     * @param \DateTimeImmutable|null $startDate Start date of the phase
     * @param LaunchPhaseSubmitMode|string|null $submitMode How pre-registrations are submitted: `at_open` at the
     *     phase start in first-come, first-served order, `on_demand` as soon as each one is complete and the phase
     *     is open
     */
    public function __construct(
        public readonly bool $supported,
        public readonly LaunchPhaseType|string $type,
        public readonly AllocationMethodType|string|null $allocation = null,
        public readonly ?\DateTimeImmutable $endDate = null,
        public readonly ?bool $smdRequired = null,
        public readonly ?\DateTimeImmutable $startDate = null,
        public readonly LaunchPhaseSubmitMode|string|null $submitMode = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): static
    {
        return new self(
            supported: $data['supported'],
            type: LaunchPhaseType::tryFrom($data['type']) ?? $data['type'],
            allocation: isset($data['allocation']) ? AllocationMethodType::tryFrom($data['allocation']) ?? $data['allocation'] : null,
            endDate: isset($data['end_date']) ? new \DateTimeImmutable($data['end_date']) : null,
            smdRequired: $data['smd_required'] ?? null,
            startDate: isset($data['start_date']) ? new \DateTimeImmutable($data['start_date']) : null,
            submitMode: isset($data['submit_mode']) ? LaunchPhaseSubmitMode::tryFrom($data['submit_mode']) ?? $data['submit_mode'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return Serializer::normalize([
            'supported' => $this->supported,
            'type' => $this->type,
            'allocation' => $this->allocation,
            'end_date' => $this->endDate,
            'smd_required' => $this->smdRequired,
            'start_date' => $this->startDate,
            'submit_mode' => $this->submitMode,
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
