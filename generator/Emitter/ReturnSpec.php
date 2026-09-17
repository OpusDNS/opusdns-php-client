<?php

declare(strict_types=1);

namespace OpusDNS\Generator\Emitter;

final class ReturnSpec
{
    /**
     * @param \Closure(string): string $body Builds the method body from the request expression
     * @param list<string> $uses
     */
    public function __construct(
        public readonly string $declaration,
        public readonly ?string $doc,
        public readonly \Closure $body,
        public readonly ?string $accept = null,
        public readonly array $uses = [],
    ) {
    }
}
