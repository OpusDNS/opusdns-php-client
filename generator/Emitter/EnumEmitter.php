<?php

declare(strict_types=1);

namespace OpusDNS\Generator\Emitter;

use Nette\PhpGenerator\PhpFile;
use OpusDNS\Generator\Doc;
use OpusDNS\Generator\FileFactory;
use OpusDNS\Generator\Spec;
use OpusDNS\Generator\TypeResolver;

final class EnumEmitter
{
    public function __construct(private readonly Spec $spec, private readonly TypeResolver $types)
    {
    }

    /** @return array<string, PhpFile> */
    public function emit(): array
    {
        $files = [];
        foreach ($this->spec->schemas() as $name => $schema) {
            if (!TypeResolver::isEnumSchema($schema)) {
                continue;
            }
            $class = $this->types->className($name);
            $isInt = ($schema['type'] ?? 'string') === 'integer';

            [$file, $namespace] = FileFactory::create(TypeResolver::ENUM_NAMESPACE);
            $enum = $namespace->addEnum($class);
            $enum->setType($isInt ? 'int' : 'string');
            $comment = Doc::forSchema($name, $class, $schema);
            if ($comment !== '') {
                $enum->addComment($comment);
            }
            foreach ($this->types->enumCases($name) as $value => $case) {
                $enum->addCase($case, $isInt ? (int) $value : (string) $value);
            }

            $files["Enum/{$class}.php"] = $file;
        }

        return $files;
    }
}
