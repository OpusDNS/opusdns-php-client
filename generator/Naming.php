<?php

declare(strict_types=1);

namespace OpusDNS\Generator;

/**
 * Naming rules shared by every emitter. Endpoint and path names follow the same rules as the constants
 * published by OpusDNS/api-spec, so both packages name an endpoint identically.
 */
final class Naming
{
    private const RESERVED = [
        'abstract', 'and', 'array', 'as', 'break', 'callable', 'case', 'catch', 'class', 'clone', 'const', 'continue',
        'declare', 'default', 'do', 'echo', 'else', 'elseif', 'empty', 'enddeclare', 'endfor', 'endforeach', 'endif',
        'endswitch', 'endwhile', 'enum', 'eval', 'exit', 'extends', 'final', 'finally', 'fn', 'for', 'foreach',
        'function', 'global', 'goto', 'if', 'implements', 'include', 'instanceof', 'insteadof', 'interface', 'isset',
        'list', 'match', 'namespace', 'new', 'or', 'print', 'private', 'protected', 'public', 'readonly', 'require',
        'return', 'static', 'switch', 'throw', 'trait', 'try', 'unset', 'use', 'var', 'while', 'xor', 'yield',
        'bool', 'false', 'float', 'int', 'iterable', 'mixed', 'never', 'null', 'object', 'parent', 'self', 'string',
        'true', 'void',
    ];

    public static function pascal(string $value): string
    {
        $parts = preg_split('/[^A-Za-z0-9]+/', $value, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return implode('', array_map(static fn (string $part): string => ucfirst($part), $parts));
    }

    public static function camel(string $value): string
    {
        return lcfirst(self::pascal($value));
    }

    /** Schema name to class name: PageResponse_JobResponse_ becomes PageResponseJobResponse. */
    public static function className(string $schemaName): string
    {
        $name = self::pascal($schemaName);
        if ($name === '') {
            throw new \InvalidArgumentException("Cannot derive a class name from schema '{$schemaName}'");
        }
        if (ctype_digit($name[0])) {
            $name = '_' . $name;
        }
        if (in_array(strtolower($name), self::RESERVED, true)) {
            $name .= 'Schema';
        }

        return $name;
    }

    /** Keys too short to make a readable variable name and the name used instead. */
    private const SHORT_KEYS = [
        'q' => 'query',
    ];

    /** JSON property key to PHP property name: dnssec_status becomes dnssecStatus. */
    public static function propertyName(string $key): string
    {
        $name = self::camel(self::SHORT_KEYS[$key] ?? $key);
        if ($name === '') {
            throw new \InvalidArgumentException("Cannot derive a property name from key '{$key}'");
        }
        if (ctype_digit($name[0])) {
            $name = '_' . $name;
        }

        return $name;
    }

    /** Enum value to case name: pending-delete becomes PENDING_DELETE, 301 becomes _301. */
    public static function enumCase(string|int|float $value): string
    {
        $name = (string) $value;
        $name = (string) preg_replace('/([a-z0-9])([A-Z])/', '$1_$2', $name);
        $name = (string) preg_replace('/[^A-Za-z0-9]+/', '_', $name);
        $name = strtoupper(trim($name, '_'));
        if ($name === '') {
            return 'EMPTY';
        }
        if (ctype_digit($name[0])) {
            $name = '_' . $name;
        }
        if ($name === 'CLASS') {
            $name = 'CLASS_';
        }

        return $name;
    }

    /** list_zones_v1_dns_get becomes listZones. */
    public static function methodName(string $operationId, string $path, string $httpMethod): string
    {
        $position = strpos($operationId, '_v1_');
        $prefix = $position === false ? '' : substr($operationId, 0, $position);
        if ($prefix === '') {
            $prefix = self::pathName($path) . ucfirst(strtolower($httpMethod));
        }

        return self::camel($prefix);
    }

    /** Tags whose name does not describe the resource they group; the value is the name used instead. */
    private const TAG_OVERRIDES = [
        'nameserver' => 'vanity_nameservers',
    ];

    /** Tag to service class: domain_tld_specific becomes DomainTldSpecificService. */
    public static function serviceClassName(string $tag): string
    {
        return self::pascal(self::TAG_OVERRIDES[$tag] ?? $tag) . 'Service';
    }

    /** Tag to the Client accessor method that returns the service: ai_concierge becomes aiConcierge(). */
    public static function accessorName(string $tag): string
    {
        return self::camel(self::TAG_OVERRIDES[$tag] ?? $tag);
    }

    /** /v1/dns/{zone_name}/rrsets becomes DnsByZoneNameRrsets. */
    public static function pathName(string $path): string
    {
        $segments = array_values(array_filter(explode('/', $path), static fn (string $segment): bool => $segment !== '' && $segment !== 'v1'));

        return implode('', array_map(static function (string $segment): string {
            if (str_starts_with($segment, '{') && str_ends_with($segment, '}')) {
                return 'By' . self::pascal(substr($segment, 1, -1));
            }

            return self::pascal($segment);
        }, $segments));
    }

    /** DnsByZoneNameRrsets becomes DNS_BY_ZONE_NAME_RRSETS. */
    public static function upperSnake(string $name): string
    {
        $name = (string) preg_replace('/[^a-zA-Z0-9_]/', '_', $name);
        $name = (string) preg_replace('/([A-Z]+)(?=[A-Z][a-z]|$)/', '$1_', $name);
        $name = (string) preg_replace('/([a-z])([A-Z])/', '$1_$2', $name);
        $name = strtoupper($name);
        $name = (string) preg_replace('/__+/', '_', $name);

        return (string) preg_replace('/_+$/', '', $name);
    }

    public static function endpointConstant(string $path): string
    {
        $name = self::upperSnake(self::pathName($path));

        return $name === '' ? 'ROOT' : $name;
    }

    /**
     * Makes a name unique within a scope by appending a counter, case-insensitively.
     *
     * @param array<string, true> $used
     */
    public static function unique(string $name, array &$used, string $separator = ''): string
    {
        $candidate = $name;
        $counter = 2;
        while (isset($used[strtolower($candidate)])) {
            $candidate = $name . $separator . $counter;
            $counter++;
        }
        $used[strtolower($candidate)] = true;

        return $candidate;
    }

    /** Collapses whitespace so a description fits on docblock lines. */
    public static function oneLine(?string $text): string
    {
        return trim((string) preg_replace('/\s+/', ' ', (string) $text));
    }
}
