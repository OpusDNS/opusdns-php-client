<?php

/**
 * This file is generated from the OpenAPI specification by bin/generate.
 * Do not edit it by hand; regenerate it instead.
 */

declare(strict_types=1);

namespace OpusDNS\Client\Model;

use OpusDNS\Client\ApiModel;
use OpusDNS\Client\Enum\SyncStatusEnum;
use OpusDNS\Client\Serializer;

final readonly class SyncStatus implements ApiModel
{
    /**
     * @param string $batchId ID of this sync. Empty if the account was never synced
     * @param \DateTimeImmutable $createdOn When the sync started
     * @param int $failed Domains that could not be synced
     * @param float $progressPercentage How far the sync has got, 0-100
     * @param int $queued Domains still waiting to be synced
     * @param int $running Domains being synced right now
     * @param SyncStatusEnum|string $status `pending` (waiting, or never synced), `running`, `completed` (domains
     *     that failed are counted in `failed`), `failed` (the sync itself failed) or `cancelled`
     * @param int $succeeded Domains synced
     * @param int $total Domains in the account. While the sync is still reading the account, this is the registrar's
     *     count
     * @param \DateTimeImmutable $updatedOn When the sync last changed
     * @param \DateTimeImmutable|null $completedOn When the sync ended
     * @param string|null $errorCode Machine-readable reason, such as `ERROR_CREDENTIAL_AUTH`. Null if the sync
     *     didn't fail or has no specific reason
     * @param string|null $errorDetail The registrar's own error message, if it sent one
     * @param string|null $errorMessage What went wrong, in words
     */
    public function __construct(
        public string $batchId,
        public \DateTimeImmutable $createdOn,
        public int $failed,
        public float $progressPercentage,
        public int $queued,
        public int $running,
        public SyncStatusEnum|string $status,
        public int $succeeded,
        public int $total,
        public \DateTimeImmutable $updatedOn,
        public ?\DateTimeImmutable $completedOn = null,
        public ?string $errorCode = null,
        public ?string $errorDetail = null,
        public ?string $errorMessage = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): static
    {
        return new self(
            batchId: $data['batch_id'],
            createdOn: new \DateTimeImmutable($data['created_on']),
            failed: $data['failed'],
            progressPercentage: $data['progress_percentage'],
            queued: $data['queued'],
            running: $data['running'],
            status: SyncStatusEnum::tryFrom($data['status']) ?? $data['status'],
            succeeded: $data['succeeded'],
            total: $data['total'],
            updatedOn: new \DateTimeImmutable($data['updated_on']),
            completedOn: isset($data['completed_on']) ? new \DateTimeImmutable($data['completed_on']) : null,
            errorCode: $data['error_code'] ?? null,
            errorDetail: $data['error_detail'] ?? null,
            errorMessage: $data['error_message'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return Serializer::normalize([
            'batch_id' => $this->batchId,
            'created_on' => $this->createdOn,
            'failed' => $this->failed,
            'progress_percentage' => $this->progressPercentage,
            'queued' => $this->queued,
            'running' => $this->running,
            'status' => $this->status,
            'succeeded' => $this->succeeded,
            'total' => $this->total,
            'updated_on' => $this->updatedOn,
            'completed_on' => $this->completedOn,
            'error_code' => $this->errorCode,
            'error_detail' => $this->errorDetail,
            'error_message' => $this->errorMessage,
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
