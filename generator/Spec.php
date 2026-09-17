<?php

declare(strict_types=1);

namespace OpusDNS\Generator;

use Symfony\Component\Yaml\Yaml;

/**
 * Read-only access to the OpenAPI document.
 */
final class Spec
{
    public const HTTP_METHODS = ['get', 'post', 'put', 'patch', 'delete'];

    /** @param array<string, mixed> $doc */
    private function __construct(private readonly array $doc)
    {
    }

    public static function load(string $path): self
    {
        if (!is_file($path)) {
            throw new \RuntimeException("Specification file not found: {$path}");
        }

        $doc = str_ends_with($path, '.json')
            ? json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR)
            : Yaml::parseFile($path);

        if (!is_array($doc) || !isset($doc['openapi'])) {
            throw new \RuntimeException("Not an OpenAPI document: {$path}");
        }

        return new self($doc);
    }

    public function version(): string
    {
        return (string) ($this->doc['info']['version'] ?? '');
    }

    public function title(): string
    {
        return (string) ($this->doc['info']['title'] ?? '');
    }

    /** @return array<string, array<string, mixed>> */
    public function schemas(): array
    {
        return $this->doc['components']['schemas'] ?? [];
    }

    /** @return array<string, array<string, mixed>> */
    public function paths(): array
    {
        return $this->doc['paths'] ?? [];
    }

    public function hasSchema(string $name): bool
    {
        return isset($this->doc['components']['schemas'][$name]);
    }

    /** @return array<string, mixed> */
    public function schema(string $name): array
    {
        return $this->doc['components']['schemas'][$name] ?? throw new \RuntimeException("Unknown schema: {$name}");
    }

    /** @return array<string, mixed> */
    public function resolveRef(string $ref): array
    {
        if (!str_starts_with($ref, '#/')) {
            throw new \RuntimeException("Only local references are supported, got: {$ref}");
        }

        $node = $this->doc;
        foreach (explode('/', substr($ref, 2)) as $segment) {
            $segment = strtr($segment, ['~1' => '/', '~0' => '~']);
            if (!is_array($node) || !array_key_exists($segment, $node)) {
                throw new \RuntimeException("Unresolvable reference: {$ref}");
            }
            $node = $node[$segment];
        }

        if (!is_array($node)) {
            throw new \RuntimeException("Reference does not point to an object: {$ref}");
        }

        return $node;
    }

    /**
     * Returns the referenced object when the node is a $ref, the node itself otherwise.
     *
     * @param array<string, mixed> $node
     * @return array<string, mixed>
     */
    public function deref(array $node): array
    {
        return isset($node['$ref']) ? $this->resolveRef($node['$ref']) : $node;
    }

    public static function refName(string $ref): string
    {
        return substr($ref, (int) strrpos($ref, '/') + 1);
    }

    /**
     * Every operation in the document, in path order.
     *
     * @return list<Operation>
     */
    public function operations(): array
    {
        $operations = [];
        foreach ($this->paths() as $path => $item) {
            foreach (self::HTTP_METHODS as $method) {
                if (!isset($item[$method])) {
                    continue;
                }
                $parameters = array_map($this->deref(...), array_merge($item['parameters'] ?? [], $item[$method]['parameters'] ?? []));
                $operations[] = new Operation($path, $method, $item[$method], $parameters);
            }
        }

        return $operations;
    }
}
