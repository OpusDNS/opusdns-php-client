<?php

/**
 * This file is generated from the OpenAPI specification by bin/generate.
 * Do not edit it by hand; regenerate it instead.
 */

declare(strict_types=1);

namespace OpusDNS\Client\Model;

use OpusDNS\Client\ApiModel;
use OpusDNS\Client\Enum\WhitelabelRenewalMode;
use OpusDNS\Client\Serializer;

/**
 * Public patch body. `label` moves the managed base subdomain to a different label (on plus the custom
 * domain is untouched - it is re-pointed by its hostnames through recheck) and `enabled` starts or
 * stops serving - both applied asynchronously, neither a purchase. `renewal_mode` sets the
 * subscription's auto-renew intent synchronously (EXPIRE = cancel at period end, RENEW = un-cancel);
 * it is not a purchase either - no price change. At least one must be given.
 */
final readonly class WhitelabelBrandingPatch implements ApiModel
{
    /**
     * @param bool|null $enabled Whether this whitelabel should be served
     * @param string|null $label New managed-subdomain label; the base hosts become app.<label>.<suffix> /
     *     auth.<label>.<suffix>. On plus the custom domain keeps serving unchanged.
     * @param WhitelabelRenewalMode|null $renewalMode Set auto-renew intent: expire cancels at period end (serves out
     *     the term), renew un-cancels
     */
    public function __construct(
        public ?bool $enabled = null,
        public ?string $label = null,
        public ?WhitelabelRenewalMode $renewalMode = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): static
    {
        return new self(
            enabled: $data['enabled'] ?? null,
            label: $data['label'] ?? null,
            renewalMode: isset($data['renewal_mode']) ? WhitelabelRenewalMode::from($data['renewal_mode']) : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return Serializer::normalize([
            'enabled' => $this->enabled,
            'label' => $this->label,
            'renewal_mode' => $this->renewalMode,
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
