<?php

/**
 * This file is generated from the OpenAPI specification by bin/generate.
 * Do not edit it by hand; regenerate it instead.
 */

declare(strict_types=1);

namespace OpusDNS\Client\Model;

use OpusDNS\Client\ApiModel;
use OpusDNS\Client\Serializer;

final readonly class DomainSummaryData implements ApiModel
{
    /**
     * @param array<string, int> $byOrganization Domain counts by organization name (name: count)
     * @param array<string, int> $byStatus Domain counts by status (status: count)
     * @param array<string, int> $byStatusTag Domain counts by status tag (status_tag: count), only status tags with
     *     at least one domain
     * @param array<string, int> $byTld Domain counts by TLD (tld: count)
     * @param DomainsExpiringSoon $expiringSoon Domains expiring soon
     * @param int $totalCount Total number of domains including sub-organizations
     */
    public function __construct(
        public array $byOrganization,
        public array $byStatus,
        public array $byStatusTag,
        public array $byTld,
        public DomainsExpiringSoon $expiringSoon,
        public int $totalCount,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): static
    {
        return new self(
            byOrganization: $data['by_organization'],
            byStatus: $data['by_status'],
            byStatusTag: $data['by_status_tag'],
            byTld: $data['by_tld'],
            expiringSoon: DomainsExpiringSoon::fromArray($data['expiring_soon']),
            totalCount: $data['total_count'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return Serializer::normalize([
            'by_organization' => $this->byOrganization,
            'by_status' => $this->byStatus,
            'by_status_tag' => $this->byStatusTag,
            'by_tld' => $this->byTld,
            'expiring_soon' => $this->expiringSoon,
            'total_count' => $this->totalCount,
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
