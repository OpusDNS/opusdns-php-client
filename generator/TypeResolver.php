<?php

declare(strict_types=1);

namespace OpusDNS\Generator;

/**
 * Maps OpenAPI schemas to PhpType instances.
 *
 * Nullability comes from anyOf/oneOf members of type null, references are resolved to enums, models or aliases,
 * and unions are hydrated by discriminator when the specification declares one.
 */
final class TypeResolver
{
    public const NAMESPACE = 'OpusDNS\\Client';
    public const MODEL_NAMESPACE = self::NAMESPACE . '\\Model';
    public const ENUM_NAMESPACE = self::NAMESPACE . '\\Enum';
    public const UNION_CLASS = self::NAMESPACE . '\\Union';

    /** @var array<string, string> */
    private array $classNames = [];

    /** @var array<string, array<string|int, string>> */
    private array $enumCases = [];

    /** @var list<string> */
    private array $resolving = [];

    public function __construct(private readonly Spec $spec)
    {
        $used = [];
        foreach (array_keys($spec->schemas()) as $name) {
            $this->classNames[$name] = Naming::unique(Naming::className($name), $used);
        }
    }

    public function className(string $schemaName): string
    {
        return $this->classNames[$schemaName] ?? throw new \RuntimeException("Unknown schema: {$schemaName}");
    }

    /** @param array<string, mixed> $schema */
    public static function isEnumSchema(array $schema): bool
    {
        if (!isset($schema['enum']) || !is_array($schema['enum'])) {
            return false;
        }
        $type = $schema['type'] ?? 'string';
        $values = array_filter($schema['enum'], static fn (mixed $value): bool => $value !== null);

        return $values !== [] && in_array($type, ['string', 'integer'], true);
    }

    /** @param array<string, mixed> $schema */
    public static function isModelSchema(array $schema): bool
    {
        return !empty($schema['properties']) && is_array($schema['properties']) && ($schema['type'] ?? 'object') === 'object';
    }

    /**
     * Case names of an enum schema keyed by value.
     *
     * @return array<string|int, string>
     */
    public function enumCases(string $schemaName): array
    {
        if (!isset($this->enumCases[$schemaName])) {
            $schema = $this->spec->schema($schemaName);
            $isInt = ($schema['type'] ?? 'string') === 'integer';
            $used = [];
            $cases = [];
            foreach ($schema['enum'] as $value) {
                if ($value === null) {
                    continue;
                }
                $key = $isInt ? (int) $value : (string) $value;
                $cases[$key] = Naming::unique(Naming::enumCase($key), $used, '_');
            }
            $this->enumCases[$schemaName] = $cases;
        }

        return $this->enumCases[$schemaName];
    }

    /**
     * @param array<string, mixed> $schema
     * @param array<string, mixed>|null $discriminator A discriminator declared by the parent, for example on array items
     */
    public function resolve(array $schema, ?array $discriminator = null): PhpType
    {
        if (isset($schema['$ref'])) {
            return $this->resolveRef((string) $schema['$ref']);
        }

        $members = $schema['anyOf'] ?? $schema['oneOf'] ?? null;
        if (is_array($members)) {
            return $this->resolveUnion($members, $schema['discriminator'] ?? $discriminator);
        }

        if (isset($schema['allOf']) && is_array($schema['allOf']) && count($schema['allOf']) === 1) {
            return $this->resolve($schema['allOf'][0]);
        }

        $type = $schema['type'] ?? null;
        if (is_array($type)) {
            $nullable = in_array('null', $type, true);
            $rest = array_values(array_diff($type, ['null']));
            if (count($rest) === 1) {
                return $this->resolve(['type' => $rest[0]] + $schema)->withNullable($nullable);
            }

            return PhpType::mixed()->withNullable(true);
        }

        return match ($type) {
            'string' => $this->resolveString($schema),
            'integer' => new PhpType('int', 'int', 'int'),
            'number' => new PhpType('float', 'float', 'float'),
            'boolean' => new PhpType('bool', 'bool', 'bool'),
            'array' => $this->resolveList($schema),
            'object' => $this->resolveObject($schema),
            'null' => PhpType::mixed()->withNullable(true),
            default => PhpType::mixed(),
        };
    }

    /** @param array<string, mixed> $schema */
    private function resolveString(array $schema): PhpType
    {
        $format = $schema['format'] ?? null;
        if ($format === 'date-time' || $format === 'date') {
            return new PhpType(
                '\\DateTimeImmutable',
                '\\DateTimeImmutable',
                'string',
                PhpType::KIND_DATE,
                hydrate: static fn (string $expr): string => "new \\DateTimeImmutable({$expr})",
                closureReturn: '\\DateTimeImmutable',
            );
        }
        if ($format === 'binary') {
            return PhpType::mixed();
        }

        return new PhpType('string', 'string', 'string');
    }

    /** @param array<string, mixed> $schema */
    private function resolveList(array $schema): PhpType
    {
        $items = isset($schema['items']) && is_array($schema['items']) ? $this->resolve($schema['items']) : PhpType::mixed();

        return new PhpType(
            'array',
            'list<' . $items->docDeclaration() . '>',
            'array',
            PhpType::KIND_LIST,
            hydrate: $this->collectionHydrator($items, 'item'),
            uses: $items->uses,
            closureReturn: 'array',
        );
    }

    /** @param array<string, mixed> $schema */
    private function resolveObject(array $schema): PhpType
    {
        if (!empty($schema['properties'])) {
            return new PhpType('array', 'array<string, mixed>', 'array', PhpType::KIND_MAP, closureReturn: 'array');
        }

        $additional = $schema['additionalProperties'] ?? true;
        $values = is_array($additional) && $additional !== [] ? $this->resolve($additional) : PhpType::mixed();

        return new PhpType(
            'array',
            'array<string, ' . $values->docDeclaration() . '>',
            'array',
            PhpType::KIND_MAP,
            hydrate: $this->collectionHydrator($values, 'value'),
            uses: $values->uses,
            closureReturn: 'array',
        );
    }

    /** @return \Closure(string): string|null */
    private function collectionHydrator(PhpType $element, string $variable): ?\Closure
    {
        if (!$element->needsHydration()) {
            return null;
        }

        $paramType = $element->raw === 'mixed' ? 'mixed' : ($element->nullable ? '?' . $element->raw : $element->raw);
        $returnType = '';
        if ($element->closureReturn !== null) {
            $returnType = ': ' . ($element->nullable ? '?' : '') . $element->closureReturn;
        }
        $body = $element->nullable
            ? "\${$variable} === null ? null : " . $element->hydrateExpr("\${$variable}")
            : $element->hydrateExpr("\${$variable}");

        return static fn (string $expr): string => "array_map(static fn ({$paramType} \${$variable}){$returnType} => {$body}, {$expr})";
    }

    private function resolveRef(string $ref): PhpType
    {
        $name = Spec::refName($ref);
        $schema = $this->spec->schema($name);
        $class = $this->className($name);

        if (self::isEnumSchema($schema)) {
            $fqcn = self::ENUM_NAMESPACE . '\\' . $class;

            return new PhpType(
                $fqcn,
                $class,
                ($schema['type'] ?? 'string') === 'integer' ? 'int' : 'string',
                PhpType::KIND_ENUM,
                hydrate: static fn (string $expr): string => "{$class}::from({$expr})",
                uses: [$fqcn],
                schemaName: $name,
                closureReturn: $class,
            );
        }

        if (self::isModelSchema($schema)) {
            $fqcn = self::MODEL_NAMESPACE . '\\' . $class;

            return new PhpType(
                $fqcn,
                $class,
                'array',
                PhpType::KIND_MODEL,
                hydrate: static fn (string $expr): string => "{$class}::fromArray({$expr})",
                uses: [$fqcn],
                schemaName: $name,
                closureReturn: $class,
            );
        }

        if (in_array($name, $this->resolving, true)) {
            return PhpType::mixed();
        }

        $this->resolving[] = $name;
        try {
            return $this->resolve($schema);
        } finally {
            array_pop($this->resolving);
        }
    }

    /**
     * @param list<array<string, mixed>> $members
     * @param array<string, mixed>|null $discriminator
     */
    private function resolveUnion(array $members, ?array $discriminator): PhpType
    {
        $nullable = false;
        $types = [];
        foreach ($members as $member) {
            if (($member['type'] ?? null) === 'null' || (isset($member['enum']) && $member['enum'] === [null])) {
                $nullable = true;
                continue;
            }
            $resolved = $this->resolve($member);
            $types[$resolved->native] ??= $resolved;
        }
        $types = array_values($types);

        if ($types === []) {
            return PhpType::mixed()->withNullable(true);
        }
        if (count($types) === 1) {
            return $types[0]->withNullable($nullable || $types[0]->nullable);
        }
        foreach ($types as $type) {
            if ($type->isMixed()) {
                return PhpType::mixed()->withNullable(true);
            }
            $nullable = $nullable || $type->nullable;
        }

        [$hydrate, $extraUses] = $this->unionHydrator($types, $discriminator);
        $allModels = array_reduce($types, static fn (bool $c, PhpType $t): bool => $c && $t->kind === PhpType::KIND_MODEL, true);
        $uses = $extraUses;
        foreach ($types as $type) {
            $uses = [...$uses, ...$type->uses];
        }

        return new PhpType(
            implode('|', array_map(static fn (PhpType $t): string => $t->native, $types)),
            implode('|', array_map(static fn (PhpType $t): string => $t->doc, $types)),
            $allModels ? 'array' : 'mixed',
            PhpType::KIND_UNION,
            $nullable,
            $hydrate,
            array_values(array_unique($uses)),
        );
    }

    /**
     * Source of an array literal, one entry per line when there are more than two.
     *
     * @param list<string> $entries
     */
    private static function arrayLiteral(array $entries): string
    {
        if (count($entries) <= 2) {
            return '[' . implode(', ', $entries) . ']';
        }

        return "[\n\t" . implode(",\n\t", $entries) . ",\n]";
    }

    /**
     * @param list<PhpType> $types
     * @param array<string, mixed>|null $discriminator
     * @return array{\Closure(string): string|null, list<string>}
     */
    private function unionHydrator(array $types, ?array $discriminator): array
    {
        $models = array_values(array_filter($types, static fn (PhpType $t): bool => $t->kind === PhpType::KIND_MODEL));
        $enums = array_values(array_filter($types, static fn (PhpType $t): bool => $t->kind === PhpType::KIND_ENUM));
        $others = array_values(array_filter($types, static fn (PhpType $t): bool => !in_array($t->kind, [PhpType::KIND_MODEL, PhpType::KIND_ENUM], true)));

        if (count($models) === count($types)) {
            if (isset($discriminator['propertyName'])) {
                $property = (string) $discriminator['propertyName'];
                $mapping = [];
                foreach ($discriminator['mapping'] ?? [] as $value => $ref) {
                    $mapping[(string) $value] = $this->className(Spec::refName((string) $ref));
                }
                if ($mapping === []) {
                    foreach ($models as $model) {
                        $mapping[(string) $model->schemaName] = $model->shortName();
                    }
                }
                $map = self::arrayLiteral(array_map(
                    static fn (string $value, string $class): string => var_export($value, true) . " => {$class}::class",
                    array_keys($mapping),
                    $mapping,
                ));
                $expr = static fn (string $e): string => "Union::discriminate({$e}, " . var_export($property, true) . ", {$map})";

                return [$expr, [self::UNION_CLASS]];
            }

            $candidates = [];
            foreach ($models as $model) {
                $candidates[$model->shortName()] = array_values(array_map(strval(...), $this->spec->schema((string) $model->schemaName)['required'] ?? []));
            }
            uasort($candidates, static fn (array $a, array $b): int => count($b) <=> count($a));
            $list = self::arrayLiteral(array_map(
                static fn (string $class, array $required): string => "{$class}::class => [" . implode(', ', array_map(static fn (string $k): string => var_export($k, true), $required)) . ']',
                array_keys($candidates),
                $candidates,
            ));

            return [static fn (string $e): string => "Union::hydrate({$e}, {$list})", [self::UNION_CLASS]];
        }

        if (count($enums) === 1 && count($models) === 0 && $others !== [] && array_reduce($others, static fn (bool $c, PhpType $t): bool => $c && $t->native === 'string', true)) {
            $enum = $enums[0]->shortName();

            return [static fn (string $e): string => "{$enum}::tryFrom({$e}) ?? {$e}", []];
        }

        if (count($models) === 1 && count($enums) === 0) {
            $model = $models[0];

            return [static fn (string $e): string => "is_array({$e}) ? {$model->hydrateExpr($e)} : {$e}", []];
        }

        return [null, []];
    }
}
