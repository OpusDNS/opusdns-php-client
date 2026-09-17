<?php

/**
 * This file is generated from the OpenAPI specification by bin/generate.
 * Do not edit it by hand; regenerate it instead.
 */

declare(strict_types=1);

namespace OpusDNS\Client\Service;

use OpusDNS\Client\Client;
use OpusDNS\Client\Endpoint;
use OpusDNS\Client\Enum\EmailForwardLogSortField;
use OpusDNS\Client\Enum\EmailForwardLogStatus;
use OpusDNS\Client\Enum\ExecutingEntity;
use OpusDNS\Client\Enum\HTTPMethod;
use OpusDNS\Client\Enum\ObjectEventType;
use OpusDNS\Client\Enum\ObjectLogSortField;
use OpusDNS\Client\Enum\RequestHistorySortField;
use OpusDNS\Client\Enum\SortOrder;
use OpusDNS\Client\Model\PageResponseEmailForwardLog;
use OpusDNS\Client\Model\PageResponseObjectLog;
use OpusDNS\Client\Model\PageResponseRequestHistory;

/**
 * Operations tagged "archive".
 */
final class ArchiveService
{
    public function __construct(
        private readonly Client $client,
    ) {
    }

    /**
     * Retrieve email forward logs by alias
     *
     * Retrieves a paginated list of email forward logs for a specific email forward alias. Only returns
     * logs created after the email forward was created.
     *
     * Required permissions: email_forwards:read
     *
     * @param string|null $xDatetimeFormat Accepted for backwards compatibility; has no effect. Response datetimes
     *     are always normalized to UTC and serialized as RFC 3339 with a `Z` suffix, whether or not this header is
     *     sent.
     */
    public function getEmailForwardLogsByAlias(
        string $emailForwardAliasId,
        string $emailForwardId,
        EmailForwardLogSortField $sortBy = EmailForwardLogSortField::CREATED_ON,
        SortOrder $sortOrder = SortOrder::DESC,
        int $pageSize = 50,
        int $page = 1,
        ?EmailForwardLogStatus $finalStatus = null,
        ?\DateTimeImmutable $startTime = null,
        ?\DateTimeImmutable $endTime = null,
        ?string $xDatetimeFormat = null,
    ): PageResponseEmailForwardLog {
        $response = $this->client->request(
            'GET',
            Endpoint::ARCHIVE_EMAIL_FORWARD_LOGS_ALIASES_BY_EMAIL_FORWARD_ALIAS_ID,
            path: ['email_forward_alias_id' => $emailForwardAliasId],
            query: ['email_forward_id' => $emailForwardId, 'sort_by' => $sortBy, 'sort_order' => $sortOrder, 'page_size' => $pageSize, 'page' => $page, 'final_status' => $finalStatus, 'start_time' => $startTime, 'end_time' => $endTime],
            headers: ['X-Datetime-Format' => $xDatetimeFormat],
        );

        return PageResponseEmailForwardLog::fromArray($this->client->decodeArray($response));
    }

    /**
     * Retrieve email forward logs
     *
     * Retrieves a paginated list of email forward logs for a specific email forward. Only returns logs
     * created after the email forward was created.
     *
     * Required permissions: email_forwards:read
     *
     * @param string|null $xDatetimeFormat Accepted for backwards compatibility; has no effect. Response datetimes
     *     are always normalized to UTC and serialized as RFC 3339 with a `Z` suffix, whether or not this header is
     *     sent.
     */
    public function getEmailForwardLogs(
        string $emailForwardId,
        EmailForwardLogSortField $sortBy = EmailForwardLogSortField::CREATED_ON,
        SortOrder $sortOrder = SortOrder::DESC,
        int $pageSize = 50,
        int $page = 1,
        ?EmailForwardLogStatus $finalStatus = null,
        ?\DateTimeImmutable $startTime = null,
        ?\DateTimeImmutable $endTime = null,
        ?string $xDatetimeFormat = null,
    ): PageResponseEmailForwardLog {
        $response = $this->client->request(
            'GET',
            Endpoint::ARCHIVE_EMAIL_FORWARD_LOGS_BY_EMAIL_FORWARD_ID,
            path: ['email_forward_id' => $emailForwardId],
            query: ['sort_by' => $sortBy, 'sort_order' => $sortOrder, 'page_size' => $pageSize, 'page' => $page, 'final_status' => $finalStatus, 'start_time' => $startTime, 'end_time' => $endTime],
            headers: ['X-Datetime-Format' => $xDatetimeFormat],
        );

        return PageResponseEmailForwardLog::fromArray($this->client->decodeArray($response));
    }

    /**
     * Retrieve all object history
     *
     * Retrieve all paginated audit logs with optional filtering and sorting
     *
     * Required permissions: organization:read
     *
     * @param string|null $xDatetimeFormat Accepted for backwards compatibility; has no effect. Response datetimes
     *     are always normalized to UTC and serialized as RFC 3339 with a `Z` suffix, whether or not this header is
     *     sent.
     */
    public function getObjectLogs(
        ObjectLogSortField $sortBy = ObjectLogSortField::CREATED_ON,
        SortOrder $sortOrder = SortOrder::DESC,
        int $pageSize = 50,
        int $page = 1,
        ?string $objectLogId = null,
        ?string $objectType = null,
        ?ObjectEventType $action = null,
        ?string $serverRequestId = null,
        ?ExecutingEntity $performedByType = null,
        ?string $performedById = null,
        ?\DateTimeImmutable $createdBefore = null,
        ?\DateTimeImmutable $createdAfter = null,
        ?string $objectId = null,
        ?string $xDatetimeFormat = null,
    ): PageResponseObjectLog {
        $response = $this->client->request(
            'GET',
            Endpoint::ARCHIVE_OBJECT_LOGS,
            query: ['sort_by' => $sortBy, 'sort_order' => $sortOrder, 'page_size' => $pageSize, 'page' => $page, 'object_log_id' => $objectLogId, 'object_type' => $objectType, 'action' => $action, 'server_request_id' => $serverRequestId, 'performed_by_type' => $performedByType, 'performed_by_id' => $performedById, 'created_before' => $createdBefore, 'created_after' => $createdAfter, 'object_id' => $objectId],
            headers: ['X-Datetime-Format' => $xDatetimeFormat],
        );

        return PageResponseObjectLog::fromArray($this->client->decodeArray($response));
    }

    /**
     * Retrieve object history
     *
     * Retrieve paginated audit logs for a specific object with optional filtering and sorting
     *
     * Required permissions: organization:read
     *
     * @param string|null $xDatetimeFormat Accepted for backwards compatibility; has no effect. Response datetimes
     *     are always normalized to UTC and serialized as RFC 3339 with a `Z` suffix, whether or not this header is
     *     sent.
     */
    public function getObjectLogsByObjectId(
        string $objectId,
        ObjectLogSortField $sortBy = ObjectLogSortField::CREATED_ON,
        SortOrder $sortOrder = SortOrder::DESC,
        int $pageSize = 50,
        int $page = 1,
        ?string $objectLogId = null,
        ?string $objectType = null,
        ?ObjectEventType $action = null,
        ?string $serverRequestId = null,
        ?ExecutingEntity $performedByType = null,
        ?string $performedById = null,
        ?\DateTimeImmutable $createdBefore = null,
        ?\DateTimeImmutable $createdAfter = null,
        ?string $xDatetimeFormat = null,
    ): PageResponseObjectLog {
        $response = $this->client->request(
            'GET',
            Endpoint::ARCHIVE_OBJECT_LOGS_BY_OBJECT_ID,
            path: ['object_id' => $objectId],
            query: ['sort_by' => $sortBy, 'sort_order' => $sortOrder, 'page_size' => $pageSize, 'page' => $page, 'object_log_id' => $objectLogId, 'object_type' => $objectType, 'action' => $action, 'server_request_id' => $serverRequestId, 'performed_by_type' => $performedByType, 'performed_by_id' => $performedById, 'created_before' => $createdBefore, 'created_after' => $createdAfter],
            headers: ['X-Datetime-Format' => $xDatetimeFormat],
        );

        return PageResponseObjectLog::fromArray($this->client->decodeArray($response));
    }

    /**
     * Retrieve request history logs
     *
     * Retrieves a paginated list of request history logs
     *
     * Required permissions: organization:read
     *
     * @param string|null $xDatetimeFormat Accepted for backwards compatibility; has no effect. Response datetimes
     *     are always normalized to UTC and serialized as RFC 3339 with a `Z` suffix, whether or not this header is
     *     sent.
     */
    public function getRequestHistory(
        RequestHistorySortField $sortBy = RequestHistorySortField::CREATED_ON,
        SortOrder $sortOrder = SortOrder::DESC,
        int $pageSize = 50,
        int $page = 1,
        ?HTTPMethod $method = null,
        ?string $path = null,
        ?int $statusCode = null,
        ?int $minStatusCode = null,
        ?int $maxStatusCode = null,
        ?float $minDuration = null,
        ?float $maxDuration = null,
        ?string $clientIp = null,
        ?string $serverRequestId = null,
        ?ExecutingEntity $performedByType = null,
        ?string $performedById = null,
        ?\DateTimeImmutable $requestStartedBefore = null,
        ?\DateTimeImmutable $requestStartedAfter = null,
        ?string $xDatetimeFormat = null,
    ): PageResponseRequestHistory {
        $response = $this->client->request(
            'GET',
            Endpoint::ARCHIVE_REQUEST_HISTORY,
            query: ['sort_by' => $sortBy, 'sort_order' => $sortOrder, 'page_size' => $pageSize, 'page' => $page, 'method' => $method, 'path' => $path, 'status_code' => $statusCode, 'min_status_code' => $minStatusCode, 'max_status_code' => $maxStatusCode, 'min_duration' => $minDuration, 'max_duration' => $maxDuration, 'client_ip' => $clientIp, 'server_request_id' => $serverRequestId, 'performed_by_type' => $performedByType, 'performed_by_id' => $performedById, 'request_started_before' => $requestStartedBefore, 'request_started_after' => $requestStartedAfter],
            headers: ['X-Datetime-Format' => $xDatetimeFormat],
        );

        return PageResponseRequestHistory::fromArray($this->client->decodeArray($response));
    }
}
