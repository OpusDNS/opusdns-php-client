<?php

declare(strict_types=1);

namespace OpusDNS\Generator;

/**
 * A schema mapped to PHP: the native type declaration, the PHPDoc type, and how to turn decoded JSON into it.
 */
final class PhpType
{
    public const KIND_SCALAR = 'scalar';
    public const KIND_DATE = 'date';
    public const KIND_DATE_ONLY = 'date-only';
    public const KIND_ENUM = 'enum';
    public const KIND_MODEL = 'model';
    public const KIND_LIST = 'list';
    public const KIND_MAP = 'map';
    public const KIND_UNION = 'union';
    public const KIND_MIXED = 'mixed';

    /**
     * @param string $native PHP type declaration without nullability, fully qualified for classes
     * @param string $doc PHPDoc type using short class names (imports are added by the emitters)
     * @param string $raw PHP type of the decoded JSON value before hydration
     * @param \Closure(string): string|null $hydrate Builds the expression that hydrates a decoded value
     * @param list<string> $uses Fully qualified class names the expressions rely on
     * @param string|null $closureReturn Short return type usable in array_map closures
     * @param \Closure(string): string|null $dehydrate Builds the expression that serializes a value when
     *     Serializer::normalize() cannot, for example dates without a time part
     */
    public function __construct(
        public readonly string $native,
        public readonly string $doc,
        public readonly string $raw = 'mixed',
        public readonly string $kind = self::KIND_SCALAR,
        public readonly bool $nullable = false,
        public readonly ?\Closure $hydrate = null,
        public readonly array $uses = [],
        public readonly ?string $schemaName = null,
        public readonly ?string $closureReturn = null,
        public readonly ?\Closure $dehydrate = null,
    ) {
    }

    public static function mixed(): self
    {
        return new self('mixed', 'mixed', 'mixed', self::KIND_MIXED);
    }

    public function isMixed(): bool
    {
        return $this->kind === self::KIND_MIXED;
    }

    public function withNullable(bool $nullable): self
    {
        return new self(
            $this->native,
            $this->doc,
            $this->raw,
            $this->kind,
            $nullable,
            $this->hydrate,
            $this->uses,
            $this->schemaName,
            $this->closureReturn,
            $this->dehydrate,
        );
    }

    public function needsHydration(): bool
    {
        return $this->hydrate !== null;
    }

    public function hydrateExpr(string $expr): string
    {
        return $this->hydrate === null ? $expr : ($this->hydrate)($expr);
    }

    public function needsDehydration(): bool
    {
        return $this->dehydrate !== null;
    }

    /** Serialization expression for a value; null-safe when the type is nullable. */
    public function dehydrateExpr(string $expr): string
    {
        if ($this->dehydrate === null) {
            return $expr;
        }
        $serialized = ($this->dehydrate)($expr);

        return $this->nullable ? "{$expr} === null ? null : {$serialized}" : $serialized;
    }

    /** Type declaration including nullability, for parameters, properties and return types. */
    public function declaration(): string
    {
        if ($this->isMixed()) {
            return 'mixed';
        }
        if (!$this->nullable) {
            return $this->native;
        }

        return str_contains($this->native, '|') ? $this->native . '|null' : '?' . $this->native;
    }

    /** PHPDoc type including nullability. */
    public function docDeclaration(): string
    {
        if ($this->isMixed()) {
            return 'mixed';
        }

        return $this->nullable ? $this->doc . '|null' : $this->doc;
    }

    /** Short class name for a model or enum type. */
    public function shortName(): string
    {
        $position = strrpos($this->native, '\\');

        return $position === false ? $this->native : substr($this->native, $position + 1);
    }
}
