<?php

/**
 * This file is generated from the OpenAPI specification by bin/generate.
 * Do not edit it by hand; regenerate it instead.
 */

declare(strict_types=1);

namespace OpusDNS\Client\Service;

use OpusDNS\Client\Client;
use OpusDNS\Client\Endpoint;
use OpusDNS\Client\Enum\SortOrder;
use OpusDNS\Client\Enum\TagSortField;
use OpusDNS\Client\Enum\TagType;
use OpusDNS\Client\Model\BulkObjectTagChanges;
use OpusDNS\Client\Model\ObjectTagChanges;
use OpusDNS\Client\Model\ObjectTagChangesResponse;
use OpusDNS\Client\Model\PaginationTagResponse;
use OpusDNS\Client\Model\TagCreate;
use OpusDNS\Client\Model\TagResponse;
use OpusDNS\Client\Model\TagUpdate;

/**
 * Operations tagged "tag".
 */
final class TagService
{
    public function __construct(
        private readonly Client $client,
    ) {
    }

    /**
     * List tags
     *
     * Retrieves a paginated list of tags
     *
     * Required permissions: tags:read
     *
     * @param list<TagType>|null $tagTypes Filter by tag types (OR semantics)
     * @param string|null $xDatetimeFormat Accepted for backwards compatibility; has no effect. Response datetimes
     *     are always normalized to UTC and serialized as RFC 3339 with a `Z` suffix, whether or not this header is
     *     sent.
     */
    public function listTags(
        int $page = 1,
        int $pageSize = 10,
        TagSortField $sortBy = TagSortField::LABEL,
        SortOrder $sortOrder = SortOrder::ASC,
        ?array $tagTypes = null,
        ?string $search = null,
        ?string $xDatetimeFormat = null,
    ): PaginationTagResponse {
        $response = $this->client->request(
            'GET',
            Endpoint::TAGS,
            query: ['page' => $page, 'page_size' => $pageSize, 'sort_by' => $sortBy, 'sort_order' => $sortOrder, 'tag_types' => $tagTypes, 'search' => $search],
            headers: ['X-Datetime-Format' => $xDatetimeFormat],
        );

        return PaginationTagResponse::fromArray($this->client->decodeArray($response));
    }

    /**
     * Create a tag
     *
     * Create a new tag
     *
     * Required permissions: tags:manage
     *
     * @param TagCreate|array<string, mixed> $body
     * @param string|null $xDatetimeFormat Accepted for backwards compatibility; has no effect. Response datetimes
     *     are always normalized to UTC and serialized as RFC 3339 with a `Z` suffix, whether or not this header is
     *     sent.
     */
    public function createTag(TagCreate|array $body, ?string $xDatetimeFormat = null): TagResponse
    {
        $response = $this->client->request(
            'POST',
            Endpoint::TAGS,
            body: $body,
            headers: ['X-Datetime-Format' => $xDatetimeFormat],
        );

        return TagResponse::fromArray($this->client->decodeArray($response));
    }

    /**
     * Bulk tag or untag objects
     *
     * Add, remove, or replace tags on multiple objects at once. 'replace' is mutually exclusive with 'add'
     * and 'remove'.
     *
     * Required permissions: tags:manage
     *
     * @param BulkObjectTagChanges|array<string, mixed> $body
     * @param string|null $xDatetimeFormat Accepted for backwards compatibility; has no effect. Response datetimes
     *     are always normalized to UTC and serialized as RFC 3339 with a `Z` suffix, whether or not this header is
     *     sent.
     */
    public function bulkUpdateObjectTags(
        BulkObjectTagChanges|array $body,
        ?string $xDatetimeFormat = null,
    ): ObjectTagChangesResponse {
        $response = $this->client->request(
            'POST',
            Endpoint::TAGS_OBJECTS,
            body: $body,
            headers: ['X-Datetime-Format' => $xDatetimeFormat],
        );

        return ObjectTagChangesResponse::fromArray($this->client->decodeArray($response));
    }

    /**
     * Get a tag
     *
     * Retrieve a single tag by its ID
     *
     * Required permissions: tags:read
     *
     * @param string|null $xDatetimeFormat Accepted for backwards compatibility; has no effect. Response datetimes
     *     are always normalized to UTC and serialized as RFC 3339 with a `Z` suffix, whether or not this header is
     *     sent.
     */
    public function getTag(string $tagId, ?string $xDatetimeFormat = null): TagResponse
    {
        $response = $this->client->request(
            'GET',
            Endpoint::TAGS_BY_TAG_ID,
            path: ['tag_id' => $tagId],
            headers: ['X-Datetime-Format' => $xDatetimeFormat],
        );

        return TagResponse::fromArray($this->client->decodeArray($response));
    }

    /**
     * Update a tag
     *
     * Update a tag's label, description, or color
     *
     * Required permissions: tags:manage
     *
     * @param TagUpdate|array<string, mixed> $body
     * @param string|null $xDatetimeFormat Accepted for backwards compatibility; has no effect. Response datetimes
     *     are always normalized to UTC and serialized as RFC 3339 with a `Z` suffix, whether or not this header is
     *     sent.
     */
    public function updateTag(string $tagId, TagUpdate|array $body, ?string $xDatetimeFormat = null): TagResponse
    {
        $response = $this->client->request(
            'PATCH',
            Endpoint::TAGS_BY_TAG_ID,
            path: ['tag_id' => $tagId],
            body: $body,
            headers: ['X-Datetime-Format' => $xDatetimeFormat],
        );

        return TagResponse::fromArray($this->client->decodeArray($response));
    }

    /**
     * Delete a tag
     *
     * Required permissions: tags:delete
     *
     * @param string|null $xDatetimeFormat Accepted for backwards compatibility; has no effect. Response datetimes
     *     are always normalized to UTC and serialized as RFC 3339 with a `Z` suffix, whether or not this header is
     *     sent.
     */
    public function deleteTag(string $tagId, ?string $xDatetimeFormat = null): void
    {
        $this->client->request(
            'DELETE',
            Endpoint::TAGS_BY_TAG_ID,
            path: ['tag_id' => $tagId],
            headers: ['X-Datetime-Format' => $xDatetimeFormat],
        );
    }

    /**
     * Tag or untag objects
     *
     * Add or remove objects from a tag. Objects are matched by the tag's type (e.g. a DOMAIN tag only
     * accepts domain IDs).
     *
     * Required permissions: tags:manage
     *
     * @param ObjectTagChanges|array<string, mixed> $body
     * @param string|null $xDatetimeFormat Accepted for backwards compatibility; has no effect. Response datetimes
     *     are always normalized to UTC and serialized as RFC 3339 with a `Z` suffix, whether or not this header is
     *     sent.
     */
    public function updateTagObjects(
        string $tagId,
        ObjectTagChanges|array $body,
        ?string $xDatetimeFormat = null,
    ): ObjectTagChangesResponse {
        $response = $this->client->request(
            'POST',
            Endpoint::TAGS_BY_TAG_ID_OBJECTS,
            path: ['tag_id' => $tagId],
            body: $body,
            headers: ['X-Datetime-Format' => $xDatetimeFormat],
        );

        return ObjectTagChangesResponse::fromArray($this->client->decodeArray($response));
    }
}
