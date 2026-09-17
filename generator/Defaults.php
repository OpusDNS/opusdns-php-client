<?php

declare(strict_types=1);

namespace OpusDNS\Generator;

use Nette\PhpGenerator\Literal;

/**
 * Turns a schema default (or const) into a PHP default value when the type allows it.
 */
final class Defaults
{
    /**
     * @param array<string, mixed> $schema
     * @return array{bool, mixed} Whether a default exists, and the value (scalars, [] or a Literal)
     */
    public static function for(array $schema, PhpType $type, TypeResolver $types): array
    {
        if (array_key_exists('const', $schema) && is_scalar($schema['const'])) {
            return [true, $schema['const']];
        }
        if (!array_key_exists('default', $schema) || $schema['default'] === null) {
            return [false, null];
        }

        $default = $schema['default'];

        return match ($type->kind) {
            PhpType::KIND_ENUM => self::enumCase($type, $default, $types),
            PhpType::KIND_SCALAR => is_scalar($default) ? [true, $default] : [false, null],
            PhpType::KIND_LIST, PhpType::KIND_MAP => $default === [] ? [true, []] : [false, null],
            default => [false, null],
        };
    }

    /** @return array{bool, mixed} */
    private static function enumCase(PhpType $type, mixed $value, TypeResolver $types): array
    {
        if ($type->schemaName === null || !is_scalar($value)) {
            return [false, null];
        }
        $cases = $types->enumCases($type->schemaName);
        $key = $type->raw === 'int' ? (int) $value : (string) $value;
        if (!isset($cases[$key])) {
            return [false, null];
        }

        return [true, new Literal($type->shortName() . '::' . $cases[$key])];
    }

    /** Source code for a default value in a hydration expression. */
    public static function code(mixed $value): string
    {
        if ($value instanceof Literal) {
            return (string) $value;
        }
        if ($value === []) {
            return '[]';
        }

        return var_export($value, true);
    }
}
