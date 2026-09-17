<?php

declare(strict_types=1);

namespace OpusDNS\Generator\Emitter;

use OpusDNS\Generator\PhpType;

final class MethodParam
{
    public const IN_PATH = 'path';
    public const IN_QUERY = 'query';
    public const IN_HEADER = 'header';
    public const IN_BODY = 'body';

    /** @param list<string> $uses */
    public function __construct(
        public readonly string $name,
        public readonly string $key,
        public readonly string $in,
        public readonly PhpType $type,
        public readonly bool $required,
        public readonly bool $hasDefault,
        public readonly mixed $default,
        public readonly string $description,
        public readonly ?string $declaration = null,
        public readonly ?string $doc = null,
        public readonly ?string $contentType = null,
        public readonly array $uses = [],
    ) {
    }

    public function declaration(): string
    {
        return $this->declaration ?? $this->type->declaration();
    }

    public function doc(): string
    {
        return $this->doc ?? $this->type->docDeclaration();
    }

    /** Whether the PHPDoc type says more than the declaration, for example generics or a union of arrays. */
    public function docAddsInfo(): bool
    {
        $short = (string) preg_replace('/(?:\\\\?[A-Za-z0-9_]+\\\\)+/', '', $this->declaration());
        if (str_starts_with($short, '?')) {
            $short = substr($short, 1) . '|null';
        }

        return $this->doc() !== $short;
    }
}
