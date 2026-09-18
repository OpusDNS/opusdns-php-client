<?php

declare(strict_types=1);

namespace OpusDNS\Generator\Emitter;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PhpNamespace;
use OpusDNS\Generator\Doc;
use OpusDNS\Generator\FileFactory;
use OpusDNS\Generator\Naming;
use OpusDNS\Generator\Operation;
use OpusDNS\Generator\PhpType;
use OpusDNS\Generator\Spec;
use OpusDNS\Generator\TypeResolver;

/**
 * Emits one service class per tag with a typed method per operation, plus the accessor trait used by Client.
 */
final class ServiceEmitter
{
    public const NAMESPACE = TypeResolver::NAMESPACE . '\\Service';

    private const BODY_CONTENT_TYPES = ['application/json', 'application/x-www-form-urlencoded', 'multipart/form-data'];
    private const STREAM_INTERFACE = '\\Psr\\Http\\Message\\StreamInterface';

    /** @param array<string, string> $endpointConstants Path template to Endpoint constant name */
    public function __construct(
        private readonly Spec $spec,
        private readonly TypeResolver $types,
        private readonly array $endpointConstants,
    ) {
    }

    /** @return array<string, PhpFile> */
    public function emit(): array
    {
        $groups = [];
        foreach ($this->spec->operations() as $operation) {
            $groups[$operation->tag()][] = $operation;
        }
        ksort($groups);

        $files = [];
        $accessors = [];
        foreach ($groups as $tag => $operations) {
            $class = Naming::serviceClassName($tag);
            $files["Service/{$class}.php"] = $this->emitClass($class, $tag, $operations);
            $accessors[Naming::accessorName($tag)] = $class;
        }
        $files['Service/ServiceAccessors.php'] = $this->emitAccessors($accessors);

        return $files;
    }

    /** @param list<Operation> $operations */
    private function emitClass(string $className, string $tag, array $operations): PhpFile
    {
        [$file, $namespace] = FileFactory::create(self::NAMESPACE);
        $namespace->addUse(TypeResolver::NAMESPACE . '\\Client');
        $namespace->addUse(TypeResolver::NAMESPACE . '\\Endpoint');

        $class = $namespace->addClass($className)->setFinal();
        $class->addComment("Operations tagged \"{$tag}\".");
        $constructor = $class->addMethod('__construct');
        $constructor->addPromotedParameter('client')
            ->setVisibility('private')
            ->setReadOnly()
            ->setType(TypeResolver::NAMESPACE . '\\Client');

        foreach ($this->methodNames($operations) as $index => $name) {
            $this->emitMethod($class, $namespace, $name, $operations[$index]);
        }

        return $file;
    }

    /**
     * Method names per operation of one service; collisions get a suffix built from the path segments that differ.
     *
     * @param list<Operation> $operations
     * @return list<string>
     */
    public function methodNames(array $operations): array
    {
        $names = [];
        foreach ($operations as $index => $operation) {
            $names[$index] = Naming::methodName($operation->id(), $operation->path, $operation->method);
        }

        foreach (array_count_values($names) as $name => $count) {
            if ($count < 2) {
                continue;
            }
            $indexes = array_keys($names, $name, true);
            $segmentLists = array_map(
                static fn (int $index): array => array_values(array_filter(explode('/', $operations[$index]->path), static fn (string $segment): bool => $segment !== '')),
                $indexes,
            );
            $common = array_intersect(...$segmentLists);
            foreach ($indexes as $position => $index) {
                $distinct = array_diff($segmentLists[$position], $common);
                $suffix = Naming::pascal(implode('-', array_map(static fn (string $segment): string => trim($segment, '{}'), $distinct)));
                $names[$index] = $name . ($suffix !== '' ? $suffix : ucfirst($operations[$index]->method));
            }
        }

        $used = [];
        foreach ($names as $index => $name) {
            $names[$index] = Naming::unique($name, $used);
        }

        return $names;
    }

    private function emitMethod(ClassType $class, PhpNamespace $namespace, string $name, Operation $operation): void
    {
        $method = $class->addMethod($name);
        $params = $this->parameters($operation);
        $body = $this->bodyParam($operation);
        $return = $this->returnSpec($operation);

        $byIn = static fn (string $in, ?bool $required = null): array => array_values(array_filter(
            $params,
            static fn (MethodParam $param): bool => $param->in === $in && ($required === null || $param->required === $required),
        ));
        $ordered = [
            ...$byIn(MethodParam::IN_PATH),
            ...$byIn(MethodParam::IN_QUERY, true),
            ...($body !== null && $body->required ? [$body] : []),
            ...$byIn(MethodParam::IN_QUERY, false),
            ...($body !== null && !$body->required ? [$body] : []),
            ...$byIn(MethodParam::IN_HEADER),
        ];

        foreach ($ordered as $param) {
            $parameter = $method->addParameter($param->name)->setType($param->declaration());
            if ($param->hasDefault) {
                $parameter->setDefaultValue($param->default);
            }
            $uses = array_filter($param->type->uses, static fn (string $use): bool => $use !== TypeResolver::SERIALIZER_CLASS);
            FileFactory::addUses($namespace, [...$uses, ...$param->uses]);
        }
        FileFactory::addUses($namespace, $return->uses);

        $method->setReturnType($return->declaration);
        $method->addComment($this->docblock($operation, $ordered, $return));
        $method->setBody(($return->body)($this->requestCall($operation, $byIn, $body, $return->accept)));
    }

    /** @return list<MethodParam> */
    private function parameters(Operation $operation): array
    {
        $used = ['body' => true];
        $params = [];
        foreach ($operation->parameters as $raw) {
            $in = (string) ($raw['in'] ?? '');
            $key = (string) ($raw['name'] ?? '');
            if ($key === '' || !in_array($in, [MethodParam::IN_PATH, MethodParam::IN_QUERY, MethodParam::IN_HEADER], true)) {
                continue;
            }

            $schema = is_array($raw['schema'] ?? null) ? $raw['schema'] : ['type' => 'string'];
            $type = $this->types->resolve($schema);
            if ($type->isMixed()) {
                $type = new PhpType('string', 'string', 'string');
            }
            $description = Naming::oneLine($raw['description'] ?? $schema['description'] ?? null);
            $phpName = Naming::unique(Naming::propertyName($key), $used);

            if ($in === MethodParam::IN_PATH) {
                $params[] = new MethodParam($phpName, $key, $in, $type->withNullable(false), true, false, null, $description);
                continue;
            }

            $mandatory = (bool) ($raw['required'] ?? false) && !$type->nullable;
            $default = $schema['default'] ?? null;
            if (is_scalar($default) && $default !== 'None') {
                $description = trim($description . ' Server default: ' . (is_bool($default) ? var_export($default, true) : (string) $default) . '.');
            }
            $params[] = new MethodParam($phpName, $key, $in, $type->withNullable(!$mandatory), $mandatory, !$mandatory, null, $description);
        }

        return $params;
    }

    private function bodyParam(Operation $operation): ?MethodParam
    {
        $requestBody = $operation->raw['requestBody'] ?? null;
        if (!is_array($requestBody)) {
            return null;
        }
        $requestBody = $this->spec->deref($requestBody);
        $content = is_array($requestBody['content'] ?? null) ? $requestBody['content'] : [];

        $contentType = null;
        foreach (self::BODY_CONTENT_TYPES as $candidate) {
            if (isset($content[$candidate])) {
                $contentType = $candidate;
                break;
            }
        }
        $contentType ??= array_key_first($content);
        if ($contentType === null) {
            return null;
        }

        $schema = $content[$contentType]['schema'] ?? null;
        $type = is_array($schema) && $schema !== [] ? $this->types->resolve($schema) : PhpType::mixed();
        $required = (bool) ($requestBody['required'] ?? false) && !$type->nullable;

        [$declaration, $doc] = match ($type->kind) {
            PhpType::KIND_MODEL, PhpType::KIND_UNION => [$type->native . '|array', $type->doc . '|array<string, mixed>'],
            PhpType::KIND_LIST => ['array', $type->doc . '|list<array<string, mixed>>'],
            PhpType::KIND_ENUM, PhpType::KIND_SCALAR, PhpType::KIND_DATE => [$type->native, $type->doc],
            PhpType::KIND_MIXED => ['mixed', 'mixed'],
            default => ['array', 'array<string, mixed>'],
        };
        if (!$required && $declaration !== 'mixed') {
            $declaration .= '|null';
            $doc .= '|null';
        }

        return new MethodParam(
            'body',
            'body',
            MethodParam::IN_BODY,
            $type,
            $required,
            !$required,
            null,
            Naming::oneLine($requestBody['description'] ?? null),
            $declaration,
            $doc,
            (string) $contentType,
        );
    }

    private function returnSpec(Operation $operation): ReturnSpec
    {
        $responses = is_array($operation->raw['responses'] ?? null) ? $operation->raw['responses'] : [];
        $codes = array_values(array_filter(array_map(strval(...), array_keys($responses)), static fn (string $code): bool => str_starts_with($code, '2')));
        sort($codes);

        $hasEmpty = false;
        $jsonFound = false;
        $jsonSchema = null;
        $otherContentType = null;
        foreach ($codes as $code) {
            $content = is_array($responses[$code]['content'] ?? null) ? $responses[$code]['content'] : [];
            if ($content === []) {
                $hasEmpty = true;
                continue;
            }
            if (isset($content['application/json'])) {
                if (!$jsonFound) {
                    $jsonFound = true;
                    $jsonSchema = is_array($content['application/json']['schema'] ?? null) ? $content['application/json']['schema'] : null;
                }
                continue;
            }
            $otherContentType ??= (string) array_key_first($content);
        }

        if (!$jsonFound && $otherContentType !== null) {
            return new ReturnSpec(
                self::STREAM_INTERFACE,
                null,
                static fn (string $call): string => "\$response = {$call};\n\nreturn \$response->getBody();",
                $otherContentType,
            );
        }
        if (!$jsonFound) {
            return new ReturnSpec('void', null, static fn (string $call): string => "{$call};");
        }
        if ($jsonSchema === null || $jsonSchema === []) {
            return new ReturnSpec('mixed', null, static fn (string $call): string => "\$response = {$call};\n\nreturn \$this->client->decode(\$response);");
        }

        $type = $this->types->resolve($jsonSchema);
        $nullable = $hasEmpty || $type->nullable;
        $type = $type->withNullable($nullable);
        $doc = in_array($type->kind, [PhpType::KIND_LIST, PhpType::KIND_MAP, PhpType::KIND_UNION], true) ? '@return ' . $type->docDeclaration() : null;

        if ($type->isMixed()) {
            return new ReturnSpec('mixed', null, static fn (string $call): string => "\$response = {$call};\n\nreturn \$this->client->decode(\$response);");
        }

        if ($type->needsHydration()) {
            $inner = $type->withNullable(false);
            $returnType = (string) preg_replace('/(?:\\\\?[A-Za-z0-9_]+\\\\)+/', '', $inner->declaration());
            $closure = 'static fn (' . ($type->raw === 'array' ? 'array' : 'mixed') . " \$data): {$returnType} => " . $inner->hydrateExpr('$data');
            $optional = $nullable ? ', optional: true' : '';
            $body = static fn (string $call): string => "\$response = {$call};\n\nreturn \$this->client->hydrate(\$response, {$closure}{$optional});";
        } else {
            $decoder = match ($type->kind) {
                PhpType::KIND_LIST => 'decodeList',
                PhpType::KIND_MAP, PhpType::KIND_UNION => 'decodeArray',
                default => 'decode',
            };
            $body = $nullable
                ? static fn (string $call): string => "\$response = {$call};\n\nreturn \$this->client->decodeOptional(\$response);"
                : static fn (string $call): string => "\$response = {$call};\n\nreturn \$this->client->{$decoder}(\$response);";
        }

        return new ReturnSpec($type->declaration(), $doc, $body, null, $type->uses);
    }

    /** @param \Closure(string, ?bool=): list<MethodParam> $byIn */
    private function requestCall(Operation $operation, \Closure $byIn, ?MethodParam $body, ?string $accept): string
    {
        $pairs = static fn (array $params): string => '[' . implode(', ', array_map(
            static fn (MethodParam $param): string => var_export($param->key, true) . ' => ' . $param->type->dehydrateExpr("\${$param->name}"),
            $params,
        )) . ']';

        $args = [var_export($operation->httpMethod(), true), 'Endpoint::' . $this->endpointConstants[$operation->path]];
        if ($byIn(MethodParam::IN_PATH) !== []) {
            $args[] = 'path: ' . $pairs($byIn(MethodParam::IN_PATH));
        }
        if ($byIn(MethodParam::IN_QUERY) !== []) {
            $args[] = 'query: ' . $pairs($byIn(MethodParam::IN_QUERY));
        }
        if ($body !== null) {
            $args[] = 'body: $body';
        }
        $headers = array_map(
            static fn (MethodParam $param): string => var_export($param->key, true) . " => \${$param->name}",
            $byIn(MethodParam::IN_HEADER),
        );
        if ($accept !== null) {
            $headers[] = "'Accept' => " . var_export($accept, true);
        }
        if ($headers !== []) {
            $args[] = 'headers: [' . implode(', ', $headers) . ']';
        }
        if ($body !== null && $body->contentType !== 'application/json') {
            $args[] = 'contentType: ' . var_export($body->contentType, true);
        }

        if (count($args) === 2) {
            return '$this->client->request(' . implode(', ', $args) . ')';
        }

        return "\$this->client->request(\n\t" . implode(",\n\t", $args) . ",\n)";
    }

    /** @param list<MethodParam> $params */
    private function docblock(Operation $operation, array $params, ReturnSpec $return): string
    {
        $lines = [];
        $summary = Naming::oneLine($operation->raw['summary'] ?? null);
        $lines[] = $summary !== '' ? $summary : $operation->httpMethod() . ' ' . $operation->path;

        $description = Doc::wrap($operation->raw['description'] ?? null);
        if ($description !== '' && $description !== $summary) {
            $lines[] = '';
            $lines[] = $description;
        }
        $permissions = $operation->permissions();
        if ($permissions !== []) {
            $lines[] = '';
            $lines[] = 'Required permissions: ' . implode(', ', $permissions);
        }

        $tags = [];
        foreach ($params as $param) {
            $needsDoc = $param->description !== '' || $param->docAddsInfo();
            if ($needsDoc) {
                $tags[] = Doc::tag("@param {$param->doc()} \${$param->name}", $param->description);
            }
        }
        if ($return->doc !== null) {
            $tags[] = $return->doc;
        }
        if (!empty($operation->raw['deprecated'])) {
            $tags[] = '@deprecated';
        }
        if ($tags !== []) {
            $lines[] = '';
            array_push($lines, ...$tags);
        }

        return implode("\n", $lines);
    }

    /** @param array<string, string> $accessors accessor name => class name */
    private function emitAccessors(array $accessors): PhpFile
    {
        [$file, $namespace] = FileFactory::create(self::NAMESPACE);
        $trait = $namespace->addTrait('ServiceAccessors');
        $trait->addComment('Gives Client one accessor per API tag. Used by OpusDNS\\Client\\Client.');
        foreach ($accessors as $name => $class) {
            $trait->addMethod($name)
                ->setReturnType(self::NAMESPACE . '\\' . $class)
                ->setBody("return new {$class}(\$this);");
        }

        return $file;
    }
}
