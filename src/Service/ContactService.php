<?php

/**
 * This file is generated from the OpenAPI specification by bin/generate.
 * Do not edit it by hand; regenerate it instead.
 */

declare(strict_types=1);

namespace OpusDNS\Client\Service;

use OpusDNS\Client\Client;
use OpusDNS\Client\Endpoint;
use OpusDNS\Client\Enum\ContactAttributeSetSortField;
use OpusDNS\Client\Enum\ContactIncludeField;
use OpusDNS\Client\Enum\ContactSortField;
use OpusDNS\Client\Enum\ContactStatusTagType;
use OpusDNS\Client\Enum\SortOrder;
use OpusDNS\Client\Enum\TagFilterMode;
use OpusDNS\Client\Enum\VerificationType;
use OpusDNS\Client\Model\ContactAttestReq;
use OpusDNS\Client\Model\ContactAttestRes;
use OpusDNS\Client\Model\ContactAttributeLinkResponse;
use OpusDNS\Client\Model\ContactAttributeSetCreate;
use OpusDNS\Client\Model\ContactAttributeSetResponse;
use OpusDNS\Client\Model\ContactAttributeSetUpdate;
use OpusDNS\Client\Model\ContactCreate;
use OpusDNS\Client\Model\ContactResponse;
use OpusDNS\Client\Model\ContactVerificationApiResponse;
use OpusDNS\Client\Model\ContactVerificationEmailResponse;
use OpusDNS\Client\Model\ContactVerificationResponse;
use OpusDNS\Client\Model\PaginationContactAttributeSetResponse;
use OpusDNS\Client\Model\PaginationContactResponse;
use OpusDNS\Client\Union;

/**
 * Operations tagged "contact".
 */
final class ContactService
{
    public function __construct(
        private readonly Client $client,
    ) {
    }

    /**
     * List all contacts
     *
     * Retrieves a paginated list of all contacts
     *
     * Required permissions: contacts:read
     *
     * @param int|null $page Server default: 1.
     * @param int|null $pageSize Server default: 10.
     * @param ContactSortField|string|null $sortBy Field to sort by Server default: created_on.
     * @param SortOrder|string|null $sortOrder Sort order direction Server default: desc.
     * @param list<ContactStatusTagType|string>|null $statusTags Filter by contact status tag types. Can be specified
     *     multiple times.
     * @param TagFilterMode|string|null $statusTagMode How to combine status_tags: 'match_any' returns objects with
     *     at least one of the tags, 'match_all' returns objects with every tag, 'match_none' returns objects with
     *     none of the tags, including objects that carry no status tags at all. Has no effect unless one or more
     *     status_tags are supplied. Server default: match_any.
     * @param list<string>|null $tagIds Filter by user tag IDs. Can be specified multiple times.
     * @param TagFilterMode|string|null $tagMode How to combine tag_ids: 'match_any' returns objects with at least
     *     one of the tags, 'match_all' returns objects with every tag, 'match_none' returns objects with none of the
     *     tags, including objects that carry no user tags at all. Has no effect unless one or more tag_ids are
     *     supplied; to filter on whether an object has any user tag, use has_tags. Server default: match_any.
     * @param bool|null $hasTags true returns only objects that carry at least one user tag, false only objects that
     *     carry none. Status tags are not counted. Combines with tag_ids and the other filters.
     * @param string|null $firstName Filter by exact first name
     * @param string|null $lastName Filter by exact last name
     * @param string|null $email Filter by exact email address
     * @param string|null $search Search contacts by name, email, organization, or contact ID. Multiple
     *     whitespace-separated terms must all match (as substrings), so a full "First Last" name matches across the
     *     separate first- and last-name columns. A contact ID is matched exactly.
     * @param string|null $country Filter by country
     * @param bool|null $inUse Filter by whether the contact is in use by any domain (true = has domains, false =
     *     none)
     * @param \DateTimeImmutable|null $createdAfter Filter contacts created after this date
     * @param \DateTimeImmutable|null $createdBefore Filter contacts created before this date
     * @param list<ContactIncludeField|string>|null $include Include additional data in the response. Can be
     *     specified multiple times.
     * @param string|null $xDatetimeFormat Accepted for backwards compatibility; has no effect. Response datetimes
     *     are always normalized to UTC and serialized as RFC 3339 with a `Z` suffix, whether or not this header is
     *     sent.
     */
    public function getContacts(
        ?int $page = null,
        ?int $pageSize = null,
        ContactSortField|string|null $sortBy = null,
        SortOrder|string|null $sortOrder = null,
        ?array $statusTags = null,
        TagFilterMode|string|null $statusTagMode = null,
        ?array $tagIds = null,
        TagFilterMode|string|null $tagMode = null,
        ?bool $hasTags = null,
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $email = null,
        ?string $search = null,
        ?string $country = null,
        ?bool $inUse = null,
        ?\DateTimeImmutable $createdAfter = null,
        ?\DateTimeImmutable $createdBefore = null,
        ?array $include = null,
        ?string $xDatetimeFormat = null,
    ): PaginationContactResponse {
        $response = $this->client->request(
            'GET',
            Endpoint::CONTACTS,
            query: ['page' => $page, 'page_size' => $pageSize, 'sort_by' => $sortBy, 'sort_order' => $sortOrder, 'status_tags' => $statusTags, 'status_tag_mode' => $statusTagMode, 'tag_ids' => $tagIds, 'tag_mode' => $tagMode, 'has_tags' => $hasTags, 'first_name' => $firstName, 'last_name' => $lastName, 'email' => $email, 'search' => $search, 'country' => $country, 'in_use' => $inUse, 'created_after' => $createdAfter, 'created_before' => $createdBefore, 'include' => $include],
            headers: ['X-Datetime-Format' => $xDatetimeFormat],
        );

        return $this->client->hydrate($response, static fn (array $data): PaginationContactResponse => PaginationContactResponse::fromArray($data));
    }

    /**
     * Create a contact
     *
     * Create a new contact object to use for domain registration
     *
     * Required permissions: contacts:manage
     *
     * @param ContactCreate|array<string, mixed> $body
     * @param string|null $xDatetimeFormat Accepted for backwards compatibility; has no effect. Response datetimes
     *     are always normalized to UTC and serialized as RFC 3339 with a `Z` suffix, whether or not this header is
     *     sent.
     */
    public function createContact(ContactCreate|array $body, ?string $xDatetimeFormat = null): ContactResponse
    {
        $response = $this->client->request(
            'POST',
            Endpoint::CONTACTS,
            body: $body,
            headers: ['X-Datetime-Format' => $xDatetimeFormat],
        );

        return $this->client->hydrate($response, static fn (array $data): ContactResponse => ContactResponse::fromArray($data));
    }

    /**
     * List contact attribute sets
     *
     * Required permissions: contacts:read
     *
     * @param int|null $page Server default: 1.
     * @param int|null $pageSize Server default: 10.
     * @param ContactAttributeSetSortField|string|null $sortBy Field to sort by Server default: created_on.
     * @param SortOrder|string|null $sortOrder Sort order direction Server default: desc.
     * @param string|null $tld Filter by exact TLD (e.g. 'de', '.de', 'DE')
     * @param string|null $label Filter by label (contains, case-insensitive)
     * @param string|null $xDatetimeFormat Accepted for backwards compatibility; has no effect. Response datetimes
     *     are always normalized to UTC and serialized as RFC 3339 with a `Z` suffix, whether or not this header is
     *     sent.
     */
    public function listAttributeSets(
        ?int $page = null,
        ?int $pageSize = null,
        ContactAttributeSetSortField|string|null $sortBy = null,
        SortOrder|string|null $sortOrder = null,
        ?string $tld = null,
        ?string $label = null,
        ?string $xDatetimeFormat = null,
    ): PaginationContactAttributeSetResponse {
        $response = $this->client->request(
            'GET',
            Endpoint::CONTACTS_ATTRIBUTE_SETS,
            query: ['page' => $page, 'page_size' => $pageSize, 'sort_by' => $sortBy, 'sort_order' => $sortOrder, 'tld' => $tld, 'label' => $label],
            headers: ['X-Datetime-Format' => $xDatetimeFormat],
        );

        return $this->client->hydrate($response, static fn (array $data): PaginationContactAttributeSetResponse => PaginationContactAttributeSetResponse::fromArray($data));
    }

    /**
     * Create a contact attribute set
     *
     * Required permissions: contacts:manage
     *
     * @param ContactAttributeSetCreate|array<string, mixed> $body
     * @param string|null $xDatetimeFormat Accepted for backwards compatibility; has no effect. Response datetimes
     *     are always normalized to UTC and serialized as RFC 3339 with a `Z` suffix, whether or not this header is
     *     sent.
     */
    public function createAttributeSet(
        ContactAttributeSetCreate|array $body,
        ?string $xDatetimeFormat = null,
    ): ContactAttributeSetResponse {
        $response = $this->client->request(
            'POST',
            Endpoint::CONTACTS_ATTRIBUTE_SETS,
            body: $body,
            headers: ['X-Datetime-Format' => $xDatetimeFormat],
        );

        return $this->client->hydrate($response, static fn (array $data): ContactAttributeSetResponse => ContactAttributeSetResponse::fromArray($data));
    }

    /**
     * Retrieve a contact attribute set
     *
     * Required permissions: contacts:read
     *
     * @param string|null $xDatetimeFormat Accepted for backwards compatibility; has no effect. Response datetimes
     *     are always normalized to UTC and serialized as RFC 3339 with a `Z` suffix, whether or not this header is
     *     sent.
     */
    public function getAttributeSet(
        string $contactAttributeSetId,
        ?string $xDatetimeFormat = null,
    ): ContactAttributeSetResponse {
        $response = $this->client->request(
            'GET',
            Endpoint::CONTACTS_ATTRIBUTE_SETS_BY_CONTACT_ATTRIBUTE_SET_ID,
            path: ['contact_attribute_set_id' => $contactAttributeSetId],
            headers: ['X-Datetime-Format' => $xDatetimeFormat],
        );

        return $this->client->hydrate($response, static fn (array $data): ContactAttributeSetResponse => ContactAttributeSetResponse::fromArray($data));
    }

    /**
     * Update a contact attribute set
     *
     * Required permissions: contacts:manage
     *
     * @param ContactAttributeSetUpdate|array<string, mixed> $body
     * @param string|null $xDatetimeFormat Accepted for backwards compatibility; has no effect. Response datetimes
     *     are always normalized to UTC and serialized as RFC 3339 with a `Z` suffix, whether or not this header is
     *     sent.
     */
    public function updateAttributeSet(
        string $contactAttributeSetId,
        ContactAttributeSetUpdate|array $body,
        ?string $xDatetimeFormat = null,
    ): ContactAttributeSetResponse {
        $response = $this->client->request(
            'PATCH',
            Endpoint::CONTACTS_ATTRIBUTE_SETS_BY_CONTACT_ATTRIBUTE_SET_ID,
            path: ['contact_attribute_set_id' => $contactAttributeSetId],
            body: $body,
            headers: ['X-Datetime-Format' => $xDatetimeFormat],
        );

        return $this->client->hydrate($response, static fn (array $data): ContactAttributeSetResponse => ContactAttributeSetResponse::fromArray($data));
    }

    /**
     * Delete a contact attribute set
     *
     * Required permissions: contacts:manage
     *
     * @param string|null $xDatetimeFormat Accepted for backwards compatibility; has no effect. Response datetimes
     *     are always normalized to UTC and serialized as RFC 3339 with a `Z` suffix, whether or not this header is
     *     sent.
     */
    public function deleteAttributeSet(string $contactAttributeSetId, ?string $xDatetimeFormat = null): void
    {
        $this->client->request(
            'DELETE',
            Endpoint::CONTACTS_ATTRIBUTE_SETS_BY_CONTACT_ATTRIBUTE_SET_ID,
            path: ['contact_attribute_set_id' => $contactAttributeSetId],
            headers: ['X-Datetime-Format' => $xDatetimeFormat],
        );
    }

    /**
     * Retrieve contact verification by token
     *
     * @param string|null $xDatetimeFormat Accepted for backwards compatibility; has no effect. Response datetimes
     *     are always normalized to UTC and serialized as RFC 3339 with a `Z` suffix, whether or not this header is
     *     sent.
     */
    public function getVerificationByToken(string $token, ?string $xDatetimeFormat = null): ContactResponse
    {
        $response = $this->client->request(
            'GET',
            Endpoint::CONTACTS_VERIFICATION,
            query: ['token' => $token],
            headers: ['X-Datetime-Format' => $xDatetimeFormat],
        );

        return $this->client->hydrate($response, static fn (array $data): ContactResponse => ContactResponse::fromArray($data));
    }

    /**
     * Complete contact verification with token
     *
     * @param string|null $xDatetimeFormat Accepted for backwards compatibility; has no effect. Response datetimes
     *     are always normalized to UTC and serialized as RFC 3339 with a `Z` suffix, whether or not this header is
     *     sent.
     */
    public function updateVerificationByToken(string $token, ?string $xDatetimeFormat = null): void
    {
        $this->client->request(
            'PUT',
            Endpoint::CONTACTS_VERIFICATION,
            query: ['token' => $token],
            headers: ['X-Datetime-Format' => $xDatetimeFormat],
        );
    }

    /**
     * Email Verify Contact
     *
     * @param string|null $xDatetimeFormat Accepted for backwards compatibility; has no effect. Response datetimes
     *     are always normalized to UTC and serialized as RFC 3339 with a `Z` suffix, whether or not this header is
     *     sent.
     */
    public function emailVerifyContact(string $token, ?string $xDatetimeFormat = null): mixed
    {
        $response = $this->client->request(
            'GET',
            Endpoint::CONTACTS_VERIFY,
            query: ['token' => $token],
            headers: ['X-Datetime-Format' => $xDatetimeFormat],
        );

        return $this->client->decode($response);
    }

    /**
     * Retrieve a contact
     *
     * Required permissions: contacts:read
     *
     * @param list<ContactIncludeField|string>|null $include
     * @param string|null $xDatetimeFormat Accepted for backwards compatibility; has no effect. Response datetimes
     *     are always normalized to UTC and serialized as RFC 3339 with a `Z` suffix, whether or not this header is
     *     sent.
     */
    public function getContact(
        string $contactId,
        ?array $include = null,
        ?string $xDatetimeFormat = null,
    ): ContactResponse {
        $response = $this->client->request(
            'GET',
            Endpoint::CONTACTS_BY_CONTACT_ID,
            path: ['contact_id' => $contactId],
            query: ['include' => $include],
            headers: ['X-Datetime-Format' => $xDatetimeFormat],
        );

        return $this->client->hydrate($response, static fn (array $data): ContactResponse => ContactResponse::fromArray($data));
    }

    /**
     * Delete a contact
     *
     * Deletes a contact object; only possible if the contact is not in use
     *
     * Required permissions: contacts:delete
     *
     * @param string|null $xDatetimeFormat Accepted for backwards compatibility; has no effect. Response datetimes
     *     are always normalized to UTC and serialized as RFC 3339 with a `Z` suffix, whether or not this header is
     *     sent.
     */
    public function deleteContact(string $contactId, ?string $xDatetimeFormat = null): void
    {
        $this->client->request(
            'DELETE',
            Endpoint::CONTACTS_BY_CONTACT_ID,
            path: ['contact_id' => $contactId],
            headers: ['X-Datetime-Format' => $xDatetimeFormat],
        );
    }

    /**
     * Link a contact to a contact attribute set
     *
     * Required permissions: contacts:manage
     *
     * @param string|null $xDatetimeFormat Accepted for backwards compatibility; has no effect. Response datetimes
     *     are always normalized to UTC and serialized as RFC 3339 with a `Z` suffix, whether or not this header is
     *     sent.
     */
    public function createAttributeLink(
        string $contactId,
        string $contactAttributeSetId,
        ?string $xDatetimeFormat = null,
    ): ContactAttributeLinkResponse {
        $response = $this->client->request(
            'PATCH',
            Endpoint::CONTACTS_BY_CONTACT_ID_LINK_BY_CONTACT_ATTRIBUTE_SET_ID,
            path: ['contact_id' => $contactId, 'contact_attribute_set_id' => $contactAttributeSetId],
            headers: ['X-Datetime-Format' => $xDatetimeFormat],
        );

        return $this->client->hydrate($response, static fn (array $data): ContactAttributeLinkResponse => ContactAttributeLinkResponse::fromArray($data));
    }

    /**
     * Retrieve contact verification by contact ID
     *
     * Required permissions: contacts:manage
     *
     * @param string|null $xDatetimeFormat Accepted for backwards compatibility; has no effect. Response datetimes
     *     are always normalized to UTC and serialized as RFC 3339 with a `Z` suffix, whether or not this header is
     *     sent.
     */
    public function getVerificationStatus(
        string $contactId,
        ?string $xDatetimeFormat = null,
    ): ContactVerificationResponse {
        $response = $this->client->request(
            'GET',
            Endpoint::CONTACTS_BY_CONTACT_ID_VERIFICATION,
            path: ['contact_id' => $contactId],
            headers: ['X-Datetime-Format' => $xDatetimeFormat],
        );

        return $this->client->hydrate($response, static fn (array $data): ContactVerificationResponse => ContactVerificationResponse::fromArray($data));
    }

    /**
     * Start contact verification
     *
     * Required permissions: contacts:manage
     *
     * @param string|null $xDatetimeFormat Accepted for backwards compatibility; has no effect. Response datetimes
     *     are always normalized to UTC and serialized as RFC 3339 with a `Z` suffix, whether or not this header is
     *     sent.
     * @return ContactVerificationEmailResponse|ContactVerificationApiResponse
     */
    public function startContactVerification(
        string $contactId,
        VerificationType|string $type,
        ?string $xDatetimeFormat = null,
    ): ContactVerificationEmailResponse|ContactVerificationApiResponse {
        $response = $this->client->request(
            'POST',
            Endpoint::CONTACTS_BY_CONTACT_ID_VERIFICATION,
            path: ['contact_id' => $contactId],
            query: ['type' => $type],
            headers: ['X-Datetime-Format' => $xDatetimeFormat],
        );

        return $this->client->hydrate($response, static fn (array $data): ContactVerificationEmailResponse|ContactVerificationApiResponse => Union::hydrate($data, [ContactVerificationApiResponse::class => ['token', 'type'], ContactVerificationEmailResponse::class => ['type']]));
    }

    /**
     * Complete contact verification by contact ID
     *
     * Required permissions: contacts:manage
     *
     * @param string|null $xDatetimeFormat Accepted for backwards compatibility; has no effect. Response datetimes
     *     are always normalized to UTC and serialized as RFC 3339 with a `Z` suffix, whether or not this header is
     *     sent.
     */
    public function updateVerification(string $contactId, string $token, ?string $xDatetimeFormat = null): void
    {
        $this->client->request(
            'PUT',
            Endpoint::CONTACTS_BY_CONTACT_ID_VERIFICATION,
            path: ['contact_id' => $contactId],
            query: ['token' => $token],
            headers: ['X-Datetime-Format' => $xDatetimeFormat],
        );
    }

    /**
     * Delete contact verification
     *
     * Required permissions: contacts:manage
     *
     * @param string|null $xDatetimeFormat Accepted for backwards compatibility; has no effect. Response datetimes
     *     are always normalized to UTC and serialized as RFC 3339 with a `Z` suffix, whether or not this header is
     *     sent.
     */
    public function cancelVerification(string $contactId, ?string $xDatetimeFormat = null): void
    {
        $this->client->request(
            'DELETE',
            Endpoint::CONTACTS_BY_CONTACT_ID_VERIFICATION,
            path: ['contact_id' => $contactId],
            headers: ['X-Datetime-Format' => $xDatetimeFormat],
        );
    }

    /**
     * Get contact verification status
     *
     * Retrieve the current verification state for a contact from the contact-verification service.
     *
     * Required permissions: contacts:read
     *
     * @param string|null $xDatetimeFormat Accepted for backwards compatibility; has no effect. Response datetimes
     *     are always normalized to UTC and serialized as RFC 3339 with a `Z` suffix, whether or not this header is
     *     sent.
     */
    public function getContactVerificationStatus(string $contactId, ?string $xDatetimeFormat = null): ContactAttestRes
    {
        $response = $this->client->request(
            'GET',
            Endpoint::CONTACTS_BY_CONTACT_ID_VERIFICATIONS,
            path: ['contact_id' => $contactId],
            headers: ['X-Datetime-Format' => $xDatetimeFormat],
        );

        return $this->client->hydrate($response, static fn (array $data): ContactAttestRes => ContactAttestRes::fromArray($data));
    }

    /**
     * Attest a contact verification
     *
     * Submit one or more contact-verification attestations. Returns the per-claim verification state.
     *
     * Required permissions: contacts:manage
     *
     * @param ContactAttestReq|array<string, mixed> $body
     * @param string|null $xDatetimeFormat Accepted for backwards compatibility; has no effect. Response datetimes
     *     are always normalized to UTC and serialized as RFC 3339 with a `Z` suffix, whether or not this header is
     *     sent.
     */
    public function attestContactVerification(
        string $contactId,
        ContactAttestReq|array $body,
        ?string $xDatetimeFormat = null,
    ): ContactAttestRes {
        $response = $this->client->request(
            'POST',
            Endpoint::CONTACTS_BY_CONTACT_ID_VERIFICATIONS_ATTEST,
            path: ['contact_id' => $contactId],
            body: $body,
            headers: ['X-Datetime-Format' => $xDatetimeFormat],
        );

        return $this->client->hydrate($response, static fn (array $data): ContactAttestRes => ContactAttestRes::fromArray($data));
    }
}
