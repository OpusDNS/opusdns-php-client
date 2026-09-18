<?php

declare(strict_types=1);

namespace OpusDNS\Generator\Emitter;

use Nette\PhpGenerator\PhpFile;
use OpusDNS\Generator\Defaults;
use OpusDNS\Generator\Doc;
use OpusDNS\Generator\FileFactory;
use OpusDNS\Generator\Naming;
use OpusDNS\Generator\PhpType;
use OpusDNS\Generator\Spec;
use OpusDNS\Generator\TypeResolver;

/**
 * Emits one final readonly class per object schema, with fromArray() and toArray().
 */
final class ModelEmitter
{
    /** Schema of the pagination metadata; models pairing it with a results list implement Page. */
    public const PAGE_METADATA_SCHEMA = 'PaginationMetadata';

    public function __construct(private readonly Spec $spec, private readonly TypeResolver $types)
    {
    }

    /** @return array<string, PhpFile> */
    public function emit(): array
    {
        $files = [];
        foreach ($this->spec->schemas() as $name => $schema) {
            if (!TypeResolver::isModelSchema($schema)) {
                continue;
            }
            $class = $this->types->className($name);
            $files["Model/{$class}.php"] = $this->emitModel($name, $class, $schema);
        }

        return $files;
    }

    /** @param array<string, mixed> $schema */
    private function emitModel(string $schemaName, string $className, array $schema): PhpFile
    {
        [$file, $namespace] = FileFactory::create(TypeResolver::MODEL_NAMESPACE);
        $namespace->addUse(TypeResolver::NAMESPACE . '\\ApiModel');
        $namespace->addUse(TypeResolver::NAMESPACE . '\\Serializer');

        $class = $namespace->addClass($className)->setFinal()->setReadOnly();
        $class->addImplement(TypeResolver::NAMESPACE . '\\ApiModel');
        $comment = Doc::forSchema($schemaName, $className, $schema);
        if ($comment !== '') {
            $class->addComment($comment);
        }

        $pageItem = $this->pageItem($schema);
        if ($pageItem !== null) {
            $namespace->addUse(TypeResolver::NAMESPACE . '\\Page');
            $class->addImplement(TypeResolver::NAMESPACE . '\\Page');
            $class->addComment(($comment !== '' ? "\n" : '') . "@implements Page<{$pageItem}>");
        }

        $properties = $this->properties($schema);
        foreach ($properties as $property) {
            FileFactory::addUses($namespace, $property->type->uses);
        }

        $constructor = $class->addMethod('__construct');
        foreach ($properties as $property) {
            $parameter = $constructor->addPromotedParameter($property->name)->setType($property->type->declaration());
            $parameter->setPublic();
            if ($property->hasDefault) {
                $parameter->setDefaultValue($property->default);
            }
            $docType = $property->type->docDeclaration();
            $needsDoc = in_array($property->type->kind, [PhpType::KIND_LIST, PhpType::KIND_MAP, PhpType::KIND_UNION], true);
            if ($needsDoc || $property->description !== '') {
                $constructor->addComment(Doc::tag("@param {$docType} \${$property->name}", $property->description));
            }
        }

        $fromArray = $class->addMethod('fromArray')->setStatic()->setReturnType('static');
        $fromArray->addParameter('data')->setType('array');
        $fromArray->addComment('@param array<string, mixed> $data');
        $fromArray->setBody($this->fromArrayBody($properties));

        $toArray = $class->addMethod('toArray')->setReturnType('array');
        $toArray->addComment('@return array<string, mixed>');
        $toArray->setBody($this->toArrayBody($properties));

        $jsonSerialize = $class->addMethod('jsonSerialize')->setReturnType('array');
        $jsonSerialize->addComment('@return array<string, mixed>');
        $jsonSerialize->setBody('return $this->toArray();');

        if ($pageItem !== null) {
            $results = $class->addMethod('results')->setReturnType('array');
            $results->addComment("@return list<{$pageItem}>");
            $results->setBody('return $this->results;');
            $class->addMethod('pagination')
                ->setReturnType(TypeResolver::MODEL_NAMESPACE . '\\' . self::PAGE_METADATA_SCHEMA)
                ->setBody('return $this->pagination;');
        }

        return $file;
    }

    /**
     * Short class name of the items when the schema is a page: exactly a results list of models plus pagination
     * metadata, the shape every paginated listing of the API shares.
     *
     * @param array<string, mixed> $schema
     */
    private function pageItem(array $schema): ?string
    {
        $properties = $schema['properties'];
        $keys = array_keys($properties);
        sort($keys);
        if ($keys !== ['pagination', 'results']) {
            return null;
        }
        $results = $properties['results'];
        $pagination = $properties['pagination'];
        if (!is_array($results) || ($results['type'] ?? null) !== 'array' || !isset($results['items']['$ref'])) {
            return null;
        }
        if (!is_array($pagination) || !isset($pagination['$ref']) || Spec::refName((string) $pagination['$ref']) !== self::PAGE_METADATA_SCHEMA) {
            return null;
        }
        $item = $this->types->resolve($results['items']);

        return $item->kind === PhpType::KIND_MODEL ? $item->shortName() : null;
    }

    /**
     * @param array<string, mixed> $schema
     * @return list<ModelProperty>
     */
    private function properties(array $schema): array
    {
        $required = array_flip(array_map(strval(...), $schema['required'] ?? []));
        $used = [];
        $properties = [];

        foreach ($schema['properties'] as $key => $propertySchema) {
            $key = (string) $key;
            $propertySchema = is_array($propertySchema) ? $propertySchema : [];
            $type = $this->types->resolve($propertySchema);
            $isRequired = isset($required[$key]);
            [$hasDefault, $default] = Defaults::for($propertySchema, $type, $this->types);
            $nullable = $type->nullable || (!$isRequired && !$hasDefault);
            if (!$hasDefault && $nullable) {
                $hasDefault = true;
                $default = null;
            }

            $description = Naming::oneLine($propertySchema['description'] ?? null);
            if (isset($propertySchema['x-typeid-prefix'])) {
                $description = trim($description . ' TypeID prefix: ' . $propertySchema['x-typeid-prefix'] . '.');
            }

            $properties[] = new ModelProperty(
                Naming::unique(Naming::propertyName($key), $used),
                $key,
                $type->withNullable($nullable),
                $isRequired,
                $hasDefault,
                $default,
                $description,
            );
        }

        usort($properties, static fn (ModelProperty $a, ModelProperty $b): int => (int) $a->hasDefault <=> (int) $b->hasDefault);

        return $properties;
    }

    /** @param list<ModelProperty> $properties */
    private function fromArrayBody(array $properties): string
    {
        $lines = [];
        foreach ($properties as $property) {
            $lines[] = "\t{$property->name}: " . str_replace("\n", "\n\t", $this->hydration($property)) . ',';
        }

        return "return new self(\n" . implode("\n", $lines) . "\n);";
    }

    private function hydration(ModelProperty $property): string
    {
        $access = "\$data['{$property->key}']";
        $type = $property->type;
        $fallback = $property->hasDefault && $property->default !== null ? Defaults::code($property->default) : null;

        if (!$type->needsHydration()) {
            if ($property->required && !$type->nullable && $fallback === null) {
                return $access;
            }

            return $access . ' ?? ' . ($fallback ?? 'null');
        }

        if ($property->required && !$type->nullable && $fallback === null) {
            return $type->hydrateExpr($access);
        }

        return "isset({$access}) ? " . $type->hydrateExpr($access) . ' : ' . ($fallback ?? 'null');
    }

    /** @param list<ModelProperty> $properties */
    private function toArrayBody(array $properties): string
    {
        $lines = [];
        foreach ($properties as $property) {
            $lines[] = "\t'{$property->key}' => " . $property->type->dehydrateExpr("\$this->{$property->name}") . ',';
        }

        return "return Serializer::normalize([\n" . implode("\n", $lines) . "\n]);";
    }
}
