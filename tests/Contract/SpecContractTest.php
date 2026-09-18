<?php

declare(strict_types=1);

namespace OpusDNS\Client\Tests\Contract;

use OpusDNS\Client\Endpoint;
use OpusDNS\Client\Page;
use OpusDNS\Client\Permission;
use OpusDNS\Generator\Emitter\EndpointEmitter;
use OpusDNS\Generator\Emitter\ModelEmitter;
use OpusDNS\Generator\Emitter\ServiceEmitter;
use OpusDNS\Generator\Naming;
use OpusDNS\Generator\Operation;
use OpusDNS\Generator\Spec;
use OpusDNS\Generator\TypeResolver;
use PHPUnit\Framework\TestCase;

/**
 * Checks the generated services, endpoints and permissions against every operation of the specification, so a
 * drift between spec/openapi.yaml and src/ fails the build.
 */
final class SpecContractTest extends TestCase
{
    private static Spec $spec;

    private static TypeResolver $types;

    public static function setUpBeforeClass(): void
    {
        self::$spec = Spec::load(__DIR__ . '/../../spec/openapi.yaml');
        self::$types = new TypeResolver(self::$spec);
    }

    public function testEveryOperationHasAMatchingServiceMethod(): void
    {
        $groups = [];
        foreach (self::$spec->operations() as $operation) {
            $groups[$operation->tag()][] = $operation;
        }
        $emitter = new ServiceEmitter(self::$spec, self::$types, EndpointEmitter::constants(self::$spec));

        $failures = [];
        $checked = 0;
        foreach ($groups as $tag => $operations) {
            $class = TypeResolver::NAMESPACE . '\\Service\\' . Naming::serviceClassName($tag);
            if (!class_exists($class)) {
                $failures[] = "Service {$class} for tag '{$tag}' is missing";
                continue;
            }
            $reflection = new \ReflectionClass($class);
            foreach ($emitter->methodNames($operations) as $index => $name) {
                $operation = $operations[$index];
                $label = "{$operation->httpMethod()} {$operation->path} -> {$reflection->getShortName()}::{$name}()";
                if (!$reflection->hasMethod($name)) {
                    $failures[] = "{$label}: method is missing";
                    continue;
                }
                try {
                    $this->assertMethodMatches($reflection->getMethod($name), $operation);
                    $checked++;
                } catch (\Throwable $throwable) {
                    $failures[] = "{$label}: {$throwable->getMessage()}";
                }
            }
        }

        self::assertSame([], $failures, implode("\n", $failures));
        self::assertSame(count(self::$spec->operations()), $checked);
    }

    public function testNoServiceMethodIsWithoutAnOperation(): void
    {
        $methods = 0;
        $services = 0;
        foreach (glob(__DIR__ . '/../../src/Service/*Service.php') ?: [] as $file) {
            $class = TypeResolver::NAMESPACE . '\\Service\\' . basename($file, '.php');
            $services++;
            foreach ((new \ReflectionClass($class))->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                if (!$method->isConstructor()) {
                    $methods++;
                }
            }
        }

        $tags = array_unique(array_map(static fn (Operation $o): string => $o->tag(), self::$spec->operations()));
        self::assertSame(count($tags), $services);
        self::assertSame(count(self::$spec->operations()), $methods);
    }

    public function testEveryPaginatedResponseImplementsPage(): void
    {
        $pages = 0;
        foreach (self::$spec->schemas() as $name => $schema) {
            $properties = $schema['properties'] ?? [];
            $keys = array_keys($properties);
            sort($keys);
            $isPage = $keys === ['pagination', 'results']
                && isset($properties['results']['items']['$ref'], $properties['pagination']['$ref'])
                && Spec::refName($properties['pagination']['$ref']) === ModelEmitter::PAGE_METADATA_SCHEMA;
            $class = TypeResolver::MODEL_NAMESPACE . '\\' . self::$types->className($name);
            self::assertSame($isPage, is_subclass_of($class, Page::class), "{$class} Page implementation does not match its schema");
            $pages += (int) $isPage;
        }

        self::assertGreaterThan(20, $pages);
    }

    public function testEndpointsAndPermissionsMatchTheSpecification(): void
    {
        $constants = EndpointEmitter::constants(self::$spec);
        $endpoint = new \ReflectionClass(Endpoint::class);
        self::assertSame(count(self::$spec->paths()), count($endpoint->getConstants()));

        foreach ($constants as $path => $constant) {
            self::assertTrue($endpoint->hasConstant($constant), "Endpoint::{$constant} is missing for {$path}");
            self::assertSame($path, $endpoint->getConstant($constant));
        }

        foreach (self::$spec->operations() as $operation) {
            self::assertSame(
                $operation->permissions(),
                Permission::required($operation->httpMethod(), $operation->path),
                "Permissions differ for {$operation->httpMethod()} {$operation->path}",
            );
        }
    }

    private function assertMethodMatches(\ReflectionMethod $method, Operation $operation): void
    {
        [$expectedNames, $mandatory] = $this->expectedParameters($operation);
        $actualNames = array_map(static fn (\ReflectionParameter $p): string => $p->getName(), $method->getParameters());
        self::assertSame($expectedNames, $actualNames, 'parameter list differs');

        foreach ($method->getParameters() as $parameter) {
            $name = $parameter->getName();
            if (in_array($name, $mandatory, true)) {
                self::assertFalse($parameter->isDefaultValueAvailable(), "\${$name} should be mandatory");
            } else {
                self::assertTrue($parameter->isDefaultValueAvailable(), "\${$name} should be optional");
            }
        }

        $returnType = $method->getReturnType();
        self::assertNotNull($returnType, 'return type is missing');
        [$expectedReturn, $nullable] = $this->expectedReturn($operation);
        if ($expectedReturn !== null) {
            self::assertSame($expectedReturn, (string) preg_replace('/\|null$/', '', ltrim((string) $returnType, '?')), 'return type differs');
            if ($expectedReturn !== 'void' && $expectedReturn !== 'mixed') {
                self::assertSame($nullable, $returnType->allowsNull(), 'return nullability differs');
            }
        }

        $permissions = $operation->permissions();
        if ($permissions !== []) {
            self::assertStringContainsString('Required permissions: ' . implode(', ', $permissions), (string) $method->getDocComment());
        }
    }

    /**
     * Independent derivation of the parameter order: path, required query, required body, optional query,
     * optional body, headers.
     *
     * @return array{list<string>, list<string>} All parameter names in order, and the mandatory ones
     */
    private function expectedParameters(Operation $operation): array
    {
        $path = [];
        $requiredQuery = [];
        $optionalQuery = [];
        $headers = [];
        foreach ($operation->parameters as $parameter) {
            $key = (string) $parameter['name'];
            $name = Naming::propertyName($key);
            $schema = is_array($parameter['schema'] ?? null) ? $parameter['schema'] : [];
            switch ($parameter['in']) {
                case 'path':
                    $path[] = $name;
                    break;
                case 'header':
                    $headers[] = $name;
                    break;
                case 'query':
                    $required = ($parameter['required'] ?? false) && !$this->hasDefault($schema) && !$this->isNullable($schema);
                    if ($required) {
                        $requiredQuery[] = $name;
                    } else {
                        $optionalQuery[] = $name;
                    }
                    break;
            }
        }

        $body = null;
        $bodyRequired = false;
        $requestBody = $operation->raw['requestBody'] ?? null;
        if (is_array($requestBody)) {
            $requestBody = self::$spec->deref($requestBody);
            if (!empty($requestBody['content'])) {
                $body = 'body';
                $content = $requestBody['content'];
                $schema = ($content['application/json'] ?? $content[array_key_first($content)])['schema'] ?? [];
                $bodyRequired = (bool) ($requestBody['required'] ?? false) && !$this->isNullable(is_array($schema) ? $schema : []);
            }
        }

        $names = [
            ...$path,
            ...$requiredQuery,
            ...($body !== null && $bodyRequired ? [$body] : []),
            ...$optionalQuery,
            ...($body !== null && !$bodyRequired ? [$body] : []),
            ...$headers,
        ];
        $mandatory = [...$path, ...$requiredQuery, ...($body !== null && $bodyRequired ? [$body] : [])];

        return [$names, $mandatory];
    }

    /**
     * @return array{string|null, bool} Expected return type without nullability (null when not derivable), and nullability
     */
    private function expectedReturn(Operation $operation): array
    {
        $responses = $operation->raw['responses'] ?? [];
        $hasEmpty = false;
        $json = null;
        $jsonFound = false;
        $other = false;
        foreach ($responses as $code => $response) {
            if (!str_starts_with((string) $code, '2')) {
                continue;
            }
            $content = $response['content'] ?? [];
            if ($content === []) {
                $hasEmpty = true;
            } elseif (isset($content['application/json'])) {
                if (!$jsonFound) {
                    $jsonFound = true;
                    $json = $content['application/json']['schema'] ?? null;
                }
            } else {
                $other = true;
            }
        }

        if (!$jsonFound) {
            return [$other ? 'Psr\\Http\\Message\\StreamInterface' : 'void', false];
        }
        if (!is_array($json) || $json === []) {
            return ['mixed', true];
        }
        if (isset($json['$ref'])) {
            $name = Spec::refName((string) $json['$ref']);
            $target = self::$spec->schema($name);
            if (TypeResolver::isEnumSchema($target)) {
                $backing = ($target['type'] ?? 'string') === 'integer' ? 'int' : 'string';

                return [TypeResolver::ENUM_NAMESPACE . '\\' . self::$types->className($name) . '|' . $backing, $hasEmpty];
            }
            if (TypeResolver::isModelSchema($target)) {
                return [TypeResolver::MODEL_NAMESPACE . '\\' . self::$types->className($name), $hasEmpty];
            }

            return [null, $hasEmpty];
        }
        if (($json['type'] ?? null) === 'array' || ($json['type'] ?? null) === 'object') {
            return ['array', $hasEmpty];
        }

        return [null, $hasEmpty];
    }

    /** @param array<string, mixed> $schema */
    private function hasDefault(array $schema): bool
    {
        return array_key_exists('const', $schema) || (array_key_exists('default', $schema) && $schema['default'] !== null);
    }

    /** @param array<string, mixed> $schema */
    private function isNullable(array $schema): bool
    {
        foreach ($schema['anyOf'] ?? $schema['oneOf'] ?? [] as $member) {
            if (is_array($member) && ($member['type'] ?? null) === 'null') {
                return true;
            }
        }

        return false;
    }
}
