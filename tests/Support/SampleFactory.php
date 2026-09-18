<?php

declare(strict_types=1);

namespace OpusDNS\Client\Tests\Support;

use OpusDNS\Generator\Spec;
use OpusDNS\Generator\TypeResolver;

/**
 * Builds a sample JSON payload for a schema from the examples, defaults and types the specification declares,
 * together with the array toArray() is expected to produce for it.
 */
final class SampleFactory
{
    private const DATE_TIME = '2026-01-02T03:04:05Z';
    private const DATE = '2026-01-02';
    private const MAX_DEPTH = 6;

    public function __construct(private readonly Spec $spec)
    {
    }

    /**
     * @return array{array<string, mixed>, array<string, mixed>} Payload and expected serialization
     */
    public function model(string $schemaName): array
    {
        return $this->object($this->spec->schema($schemaName), [$schemaName]);
    }

    /**
     * @param array<string, mixed> $schema
     * @param list<string> $stack
     * @return array{array<string, mixed>, array<string, mixed>}
     */
    private function object(array $schema, array $stack): array
    {
        $payload = [];
        $expected = [];
        $required = array_flip(array_map(strval(...), $schema['required'] ?? []));
        foreach ($schema['properties'] ?? [] as $key => $property) {
            $sample = $this->value(is_array($property) ? $property : [], $stack, isset($required[$key]));
            if ($sample === null) {
                continue;
            }
            [$payload[$key], $expected[$key]] = $sample;
        }

        return [$payload, $expected];
    }

    /**
     * @param array<string, mixed> $schema
     * @param list<string> $stack
     * @return array{mixed, mixed}|null Null when no sample can be produced, for example on recursion
     */
    private function value(array $schema, array $stack, bool $required): ?array
    {
        if (isset($schema['$ref'])) {
            $name = Spec::refName((string) $schema['$ref']);
            $target = $this->spec->schema($name);
            if (TypeResolver::isEnumSchema($target)) {
                $first = array_values(array_filter($target['enum'], static fn (mixed $v): bool => $v !== null))[0];

                return [$first, $first];
            }
            if (TypeResolver::isModelSchema($target)) {
                if (in_array($name, $stack, true) || count($stack) > self::MAX_DEPTH) {
                    return null;
                }

                return $this->object($target, [...$stack, $name]);
            }

            return $this->value($target, $stack, $required);
        }

        if (array_key_exists('const', $schema)) {
            return [$schema['const'], $schema['const']];
        }

        $members = $schema['anyOf'] ?? $schema['oneOf'] ?? null;
        if (is_array($members)) {
            $discriminator = $schema['discriminator'] ?? null;
            if (is_array($discriminator) && !empty($discriminator['mapping'])) {
                $value = (string) array_key_first($discriminator['mapping']);
                $sample = $this->value(['$ref' => $discriminator['mapping'][$value]], $stack, $required);
                if ($sample === null) {
                    return null;
                }
                $sample[0][$discriminator['propertyName']] = $value;
                $sample[1][$discriminator['propertyName']] = $value;

                return $sample;
            }
            foreach ($members as $member) {
                if (is_array($member) && ($member['type'] ?? null) !== 'null') {
                    return $this->value($member, $stack, $required);
                }
            }

            return null;
        }

        $type = $schema['type'] ?? null;
        if (is_array($type)) {
            $type = array_values(array_diff($type, ['null']))[0] ?? null;
        }

        return match ($type) {
            'string' => $this->string($schema),
            'integer' => [$this->scalarExample($schema, 'is_int') ?? 1, $this->scalarExample($schema, 'is_int') ?? 1],
            'number' => [$this->scalarExample($schema, 'is_numeric') ?? 1.5, $this->scalarExample($schema, 'is_numeric') ?? 1.5],
            'boolean' => [true, true],
            'array' => $this->list($schema, $stack),
            'object' => $this->map($schema, $stack),
            'null' => null,
            default => ['any', 'any'],
        };
    }

    /**
     * @param array<string, mixed> $schema
     * @return array{mixed, mixed}
     */
    private function string(array $schema): array
    {
        $format = $schema['format'] ?? null;
        if ($format === 'date-time') {
            return [self::DATE_TIME, self::DATE_TIME];
        }
        if ($format === 'date') {
            return [self::DATE, self::DATE];
        }
        if (isset($schema['enum']) && is_array($schema['enum'])) {
            $first = array_values(array_filter($schema['enum'], static fn (mixed $v): bool => $v !== null))[0] ?? 'text';

            return [$first, $first];
        }
        $value = $this->scalarExample($schema, 'is_string') ?? 'text';

        return [$value, $value];
    }

    /**
     * @param array<string, mixed> $schema
     * @param list<string> $stack
     * @return array{list<mixed>, list<mixed>}
     */
    private function list(array $schema, array $stack): array
    {
        $item = is_array($schema['items'] ?? null) ? $this->value($schema['items'], $stack, true) : ['any', 'any'];
        if ($item === null) {
            return [[], []];
        }

        return [[$item[0]], [$item[1]]];
    }

    /**
     * @param array<string, mixed> $schema
     * @param list<string> $stack
     * @return array{array<string, mixed>, array<string, mixed>}
     */
    private function map(array $schema, array $stack): array
    {
        if (!empty($schema['properties'])) {
            return $this->object($schema, $stack);
        }
        $additional = $schema['additionalProperties'] ?? null;
        $value = is_array($additional) && $additional !== [] ? $this->value($additional, $stack, true) : ['value', 'value'];
        if ($value === null) {
            return [[], []];
        }

        return [['key' => $value[0]], ['key' => $value[1]]];
    }

    /** @param array<string, mixed> $schema */
    private function scalarExample(array $schema, string $check): mixed
    {
        $candidates = [];
        if (isset($schema['examples']) && is_array($schema['examples'])) {
            $candidates[] = $schema['examples'][0] ?? null;
        }
        $candidates[] = $schema['example'] ?? null;
        $candidates[] = $schema['default'] ?? null;
        foreach ($candidates as $candidate) {
            if ($candidate !== null && $check($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}
