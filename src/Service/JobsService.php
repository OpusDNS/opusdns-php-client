<?php

/**
 * This file is generated from the OpenAPI specification by bin/generate.
 * Do not edit it by hand; regenerate it instead.
 */

declare(strict_types=1);

namespace OpusDNS\Client\Service;

use OpusDNS\Client\Client;
use OpusDNS\Client\Endpoint;
use OpusDNS\Client\Enum\BatchSortField;
use OpusDNS\Client\Enum\BatchStatus;
use OpusDNS\Client\Enum\JobStatus;
use OpusDNS\Client\Enum\SortOrder;
use OpusDNS\Client\Model\CreateJobBatchResponse;
use OpusDNS\Client\Model\JobBatchRequest;
use OpusDNS\Client\Model\JobBatchRetryResponse;
use OpusDNS\Client\Model\JobBatchStatusResponse;
use OpusDNS\Client\Model\JobResponse;
use OpusDNS\Client\Model\PageResponseJobBatchMetadataResponse;
use OpusDNS\Client\Model\PageResponseJobResponse;

/**
 * Operations tagged "jobs".
 */
final class JobsService
{
    public function __construct(
        private readonly Client $client,
    ) {
    }

    /**
     * Get individual job details
     *
     * Required permissions: jobs:read
     *
     * @param string $jobId Job ID
     * @param string|null $xDatetimeFormat Accepted for backwards compatibility; has no effect. Response datetimes
     *     are always normalized to UTC and serialized as RFC 3339 with a `Z` suffix, whether or not this header is
     *     sent.
     */
    public function getJob(string $jobId, ?string $xDatetimeFormat = null): JobResponse
    {
        $response = $this->client->request(
            'GET',
            Endpoint::JOB_BY_JOB_ID,
            path: ['job_id' => $jobId],
            headers: ['X-Datetime-Format' => $xDatetimeFormat],
        );

        return JobResponse::fromArray($this->client->decodeArray($response));
    }

    /**
     * Delete (cancel) a queued job
     *
     * Required permissions: jobs:manage
     *
     * @param string $jobId Job ID
     * @param string|null $xDatetimeFormat Accepted for backwards compatibility; has no effect. Response datetimes
     *     are always normalized to UTC and serialized as RFC 3339 with a `Z` suffix, whether or not this header is
     *     sent.
     */
    public function deleteJob(string $jobId, ?string $xDatetimeFormat = null): void
    {
        $this->client->request(
            'DELETE',
            Endpoint::JOB_BY_JOB_ID,
            path: ['job_id' => $jobId],
            headers: ['X-Datetime-Format' => $xDatetimeFormat],
        );
    }

    /**
     * Pause a job
     *
     * Required permissions: jobs:manage
     *
     * @param string $jobId Job ID
     * @param string|null $xDatetimeFormat Accepted for backwards compatibility; has no effect. Response datetimes
     *     are always normalized to UTC and serialized as RFC 3339 with a `Z` suffix, whether or not this header is
     *     sent.
     */
    public function pauseJob(string $jobId, ?string $xDatetimeFormat = null): void
    {
        $this->client->request(
            'POST',
            Endpoint::JOB_BY_JOB_ID_PAUSE,
            path: ['job_id' => $jobId],
            headers: ['X-Datetime-Format' => $xDatetimeFormat],
        );
    }

    /**
     * Resume a paused job
     *
     * Required permissions: jobs:manage
     *
     * @param string $jobId Job ID
     * @param string|null $xDatetimeFormat Accepted for backwards compatibility; has no effect. Response datetimes
     *     are always normalized to UTC and serialized as RFC 3339 with a `Z` suffix, whether or not this header is
     *     sent.
     */
    public function resumeJob(string $jobId, ?string $xDatetimeFormat = null): JobResponse
    {
        $response = $this->client->request(
            'POST',
            Endpoint::JOB_BY_JOB_ID_RESUME,
            path: ['job_id' => $jobId],
            headers: ['X-Datetime-Format' => $xDatetimeFormat],
        );

        return JobResponse::fromArray($this->client->decodeArray($response));
    }

    /**
     * Retry a failed or dead-lettered job
     *
     * Required permissions: jobs:manage
     *
     * @param string $jobId Job ID
     * @param string|null $xDatetimeFormat Accepted for backwards compatibility; has no effect. Response datetimes
     *     are always normalized to UTC and serialized as RFC 3339 with a `Z` suffix, whether or not this header is
     *     sent.
     */
    public function retryJob(string $jobId, ?string $xDatetimeFormat = null): JobResponse
    {
        $response = $this->client->request(
            'POST',
            Endpoint::JOB_BY_JOB_ID_RETRY,
            path: ['job_id' => $jobId],
            headers: ['X-Datetime-Format' => $xDatetimeFormat],
        );

        return JobResponse::fromArray($this->client->decodeArray($response));
    }

    /**
     * List batches for organization
     *
     * Required permissions: jobs:read
     *
     * @param BatchStatus|null $status Filter by batch status (pending or complete)
     * @param BatchSortField $sortBy Sort field
     * @param SortOrder $sortOrder Sort order
     * @param int $page Page number (1-indexed)
     * @param int $pageSize Number of batches per page
     * @param string|null $xDatetimeFormat Accepted for backwards compatibility; has no effect. Response datetimes
     *     are always normalized to UTC and serialized as RFC 3339 with a `Z` suffix, whether or not this header is
     *     sent.
     */
    public function listBatches(
        ?BatchStatus $status = null,
        BatchSortField $sortBy = BatchSortField::CREATED_ON,
        SortOrder $sortOrder = SortOrder::DESC,
        int $page = 1,
        int $pageSize = 50,
        ?string $xDatetimeFormat = null,
    ): PageResponseJobBatchMetadataResponse {
        $response = $this->client->request(
            'GET',
            Endpoint::JOBS,
            query: ['status' => $status, 'sort_by' => $sortBy, 'sort_order' => $sortOrder, 'page' => $page, 'page_size' => $pageSize],
            headers: ['X-Datetime-Format' => $xDatetimeFormat],
        );

        return PageResponseJobBatchMetadataResponse::fromArray($this->client->decodeArray($response));
    }

    /**
     * Create a batch of commands for async execution
     *
     * Required permissions: contacts:manage, dns:manage, domains:manage, jobs:manage, parking:manage, vanity_ns:manage
     *
     * @param JobBatchRequest|array<string, mixed> $body
     * @param string|null $xDatetimeFormat Accepted for backwards compatibility; has no effect. Response datetimes
     *     are always normalized to UTC and serialized as RFC 3339 with a `Z` suffix, whether or not this header is
     *     sent.
     */
    public function createBatch(JobBatchRequest|array $body, ?string $xDatetimeFormat = null): CreateJobBatchResponse
    {
        $response = $this->client->request(
            'POST',
            Endpoint::JOBS,
            body: $body,
            headers: ['X-Datetime-Format' => $xDatetimeFormat],
        );

        return CreateJobBatchResponse::fromArray($this->client->decodeArray($response));
    }

    /**
     * Get batch details and execution status
     *
     * Required permissions: jobs:read
     *
     * @param string $batchId Batch ID
     * @param string|null $xDatetimeFormat Accepted for backwards compatibility; has no effect. Response datetimes
     *     are always normalized to UTC and serialized as RFC 3339 with a `Z` suffix, whether or not this header is
     *     sent.
     */
    public function getBatch(string $batchId, ?string $xDatetimeFormat = null): JobBatchStatusResponse
    {
        $response = $this->client->request(
            'GET',
            Endpoint::JOBS_BY_BATCH_ID,
            path: ['batch_id' => $batchId],
            headers: ['X-Datetime-Format' => $xDatetimeFormat],
        );

        return JobBatchStatusResponse::fromArray($this->client->decodeArray($response));
    }

    /**
     * Delete (cancel) all queued jobs in a batch
     *
     * Required permissions: jobs:manage
     *
     * @param string $batchId Batch ID
     * @param string|null $xDatetimeFormat Accepted for backwards compatibility; has no effect. Response datetimes
     *     are always normalized to UTC and serialized as RFC 3339 with a `Z` suffix, whether or not this header is
     *     sent.
     */
    public function deleteBatch(string $batchId, ?string $xDatetimeFormat = null): void
    {
        $this->client->request(
            'DELETE',
            Endpoint::JOBS_BY_BATCH_ID,
            path: ['batch_id' => $batchId],
            headers: ['X-Datetime-Format' => $xDatetimeFormat],
        );
    }

    /**
     * Get individual jobs within a batch
     *
     * Required permissions: jobs:read
     *
     * @param string $batchId Batch ID
     * @param list<JobStatus>|null $status Filter by job status (repeatable)
     * @param BatchSortField|null $sortBy Sort field
     * @param SortOrder|null $sortOrder Sort order
     * @param int $page Page number (1-indexed)
     * @param int $pageSize Number of jobs per page
     * @param string|null $xDatetimeFormat Accepted for backwards compatibility; has no effect. Response datetimes
     *     are always normalized to UTC and serialized as RFC 3339 with a `Z` suffix, whether or not this header is
     *     sent.
     */
    public function getBatchJobs(
        string $batchId,
        ?array $status = null,
        ?BatchSortField $sortBy = null,
        ?SortOrder $sortOrder = null,
        int $page = 1,
        int $pageSize = 100,
        ?string $xDatetimeFormat = null,
    ): PageResponseJobResponse {
        $response = $this->client->request(
            'GET',
            Endpoint::JOBS_BY_BATCH_ID_JOBS,
            path: ['batch_id' => $batchId],
            query: ['status' => $status, 'sort_by' => $sortBy, 'sort_order' => $sortOrder, 'page' => $page, 'page_size' => $pageSize],
            headers: ['X-Datetime-Format' => $xDatetimeFormat],
        );

        return PageResponseJobResponse::fromArray($this->client->decodeArray($response));
    }

    /**
     * Pause all eligible jobs in a batch
     *
     * Required permissions: jobs:manage
     *
     * @param string $batchId Batch ID
     * @param string|null $xDatetimeFormat Accepted for backwards compatibility; has no effect. Response datetimes
     *     are always normalized to UTC and serialized as RFC 3339 with a `Z` suffix, whether or not this header is
     *     sent.
     */
    public function pauseBatch(string $batchId, ?string $xDatetimeFormat = null): void
    {
        $this->client->request(
            'POST',
            Endpoint::JOBS_BY_BATCH_ID_PAUSE,
            path: ['batch_id' => $batchId],
            headers: ['X-Datetime-Format' => $xDatetimeFormat],
        );
    }

    /**
     * Resume all paused jobs in a batch
     *
     * Required permissions: jobs:manage
     *
     * @param string $batchId Batch ID
     * @param string|null $xDatetimeFormat Accepted for backwards compatibility; has no effect. Response datetimes
     *     are always normalized to UTC and serialized as RFC 3339 with a `Z` suffix, whether or not this header is
     *     sent.
     */
    public function resumeBatch(string $batchId, ?string $xDatetimeFormat = null): void
    {
        $this->client->request(
            'POST',
            Endpoint::JOBS_BY_BATCH_ID_RESUME,
            path: ['batch_id' => $batchId],
            headers: ['X-Datetime-Format' => $xDatetimeFormat],
        );
    }

    /**
     * Retry failed and dead-lettered jobs in a batch
     *
     * Required permissions: jobs:manage
     *
     * @param string $batchId Batch ID
     * @param list<string>|null $errorClass Optional repeatable filter: only retry jobs whose error_class matches one
     *     of these values. Example: `?error_class=BillingInsufficientFundsError` to retry only insufficient-funds
     *     failures. Omit to retry all failed/dead-lettered jobs in the batch.
     * @param string|null $xDatetimeFormat Accepted for backwards compatibility; has no effect. Response datetimes
     *     are always normalized to UTC and serialized as RFC 3339 with a `Z` suffix, whether or not this header is
     *     sent.
     */
    public function retryBatch(
        string $batchId,
        ?array $errorClass = null,
        ?string $xDatetimeFormat = null,
    ): JobBatchRetryResponse {
        $response = $this->client->request(
            'POST',
            Endpoint::JOBS_BY_BATCH_ID_RETRY,
            path: ['batch_id' => $batchId],
            query: ['error_class' => $errorClass],
            headers: ['X-Datetime-Format' => $xDatetimeFormat],
        );

        return JobBatchRetryResponse::fromArray($this->client->decodeArray($response));
    }
}
