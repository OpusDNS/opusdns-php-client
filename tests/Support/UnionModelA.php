<?php

declare(strict_types=1);

namespace OpusDNS\Client\Tests\Support;

use OpusDNS\Client\ApiModel;

final class UnionModelA implements ApiModel
{
    public function __construct(public readonly string $kind, public readonly string $left)
    {
    }

    public static function fromArray(array $data): static
    {
        return new self($data['kind'], $data['left']);
    }

    public function toArray(): array
    {
        return ['kind' => $this->kind, 'left' => $this->left];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
