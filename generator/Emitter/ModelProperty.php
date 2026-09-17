<?php

declare(strict_types=1);

namespace OpusDNS\Generator\Emitter;

use OpusDNS\Generator\PhpType;

final class ModelProperty
{
    public function __construct(
        public readonly string $name,
        public readonly string $key,
        public readonly PhpType $type,
        public readonly bool $required,
        public readonly bool $hasDefault,
        public readonly mixed $default,
        public readonly string $description,
    ) {
    }

    /** Whether callers must pass the value: no default and not nullable. */
    public function isMandatory(): bool
    {
        return !$this->hasDefault;
    }
}
