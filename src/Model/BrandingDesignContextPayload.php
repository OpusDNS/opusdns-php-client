<?php

/**
 * This file is generated from the OpenAPI specification by bin/generate.
 * Do not edit it by hand; regenerate it instead.
 */

declare(strict_types=1);

namespace OpusDNS\Client\Model;

use OpusDNS\Client\ApiModel;
use OpusDNS\Client\Serializer;

final readonly class BrandingDesignContextPayload implements ApiModel
{
    /**
     * @param list<BrandingDesignVariant> $results
     * @param list<BrandingDesignNotice>|null $notices
     * @param list<BrandingDesignPhrase>|null $summaryParts
     * @param list<string>|null $warnings
     */
    public function __construct(
        public array $results,
        public ?BrandingDesignActions $actions = null,
        public ?BrandingDesignDiff $diff = null,
        public ?array $notices = null,
        public ?BrandingDesignSource $source = null,
        public string $summary = '',
        public ?array $summaryParts = null,
        public ?array $warnings = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): static
    {
        return new self(
            results: array_map(static fn (array $item): BrandingDesignVariant => BrandingDesignVariant::fromArray($item), $data['results']),
            actions: isset($data['actions']) ? BrandingDesignActions::fromArray($data['actions']) : null,
            diff: isset($data['diff']) ? BrandingDesignDiff::fromArray($data['diff']) : null,
            notices: isset($data['notices']) ? array_map(static fn (array $item): BrandingDesignNotice => BrandingDesignNotice::fromArray($item), $data['notices']) : null,
            source: isset($data['source']) ? BrandingDesignSource::fromArray($data['source']) : null,
            summary: $data['summary'] ?? '',
            summaryParts: isset($data['summary_parts']) ? array_map(static fn (array $item): BrandingDesignPhrase => BrandingDesignPhrase::fromArray($item), $data['summary_parts']) : null,
            warnings: $data['warnings'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return Serializer::normalize([
            'results' => $this->results,
            'actions' => $this->actions,
            'diff' => $this->diff,
            'notices' => $this->notices,
            'source' => $this->source,
            'summary' => $this->summary,
            'summary_parts' => $this->summaryParts,
            'warnings' => $this->warnings,
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
