<?php

declare(strict_types=1);

namespace OpusDNS\Generator;

final class Operation
{
    /**
     * @param array<string, mixed> $raw
     * @param list<array<string, mixed>> $parameters Path-item and operation parameters with $refs resolved
     */
    public function __construct(
        public readonly string $path,
        public readonly string $method,
        public readonly array $raw,
        public readonly array $parameters,
    ) {
    }

    public function id(): string
    {
        return (string) ($this->raw['operationId'] ?? '');
    }

    public function tag(): string
    {
        return (string) ($this->raw['tags'][0] ?? 'default');
    }

    public function httpMethod(): string
    {
        return strtoupper($this->method);
    }

    /** @return list<string> */
    public function permissions(): array
    {
        return array_values(array_map(strval(...), $this->raw['x-required-permissions'] ?? []));
    }
}
