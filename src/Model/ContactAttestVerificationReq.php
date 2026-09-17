<?php

/**
 * This file is generated from the OpenAPI specification by bin/generate.
 * Do not edit it by hand; regenerate it instead.
 */

declare(strict_types=1);

namespace OpusDNS\Client\Model;

use OpusDNS\Client\ApiModel;
use OpusDNS\Client\Enum\ContactVerificationClaim;
use OpusDNS\Client\Enum\ContactVerificationMethod;
use OpusDNS\Client\Enum\ContactVerificationProof;
use OpusDNS\Client\Serializer;

final readonly class ContactAttestVerificationReq implements ApiModel
{
    public function __construct(
        public string $attestationReference,
        public ContactVerificationClaim $claim,
        public ContactVerificationMethod $method,
        public ContactVerificationProof $proof,
        public ?ContactVerificationEidInformation $eid = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): static
    {
        return new self(
            attestationReference: $data['attestation_reference'],
            claim: ContactVerificationClaim::from($data['claim']),
            method: ContactVerificationMethod::from($data['method']),
            proof: ContactVerificationProof::from($data['proof']),
            eid: isset($data['eid']) ? ContactVerificationEidInformation::fromArray($data['eid']) : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return Serializer::normalize([
            'attestation_reference' => $this->attestationReference,
            'claim' => $this->claim,
            'method' => $this->method,
            'proof' => $this->proof,
            'eid' => $this->eid,
        ]);
    }
}
