<?php

declare(strict_types=1);

namespace OpusDNS\Generator\Emitter;

use Nette\PhpGenerator\PhpFile;
use OpusDNS\Generator\FileFactory;
use OpusDNS\Generator\Spec;
use OpusDNS\Generator\TypeResolver;

/**
 * Emits the Permission class from the x-required-permissions extension.
 */
final class PermissionEmitter
{
    public function __construct(private readonly Spec $spec)
    {
    }

    public function emit(): PhpFile
    {
        $required = [];
        foreach ($this->spec->operations() as $operation) {
            $permissions = $operation->permissions();
            if ($permissions !== []) {
                $required[$operation->path][$operation->httpMethod()] = $permissions;
            }
        }
        ksort($required);

        [$file, $namespace] = FileFactory::create(TypeResolver::NAMESPACE);
        $class = $namespace->addClass('Permission')->setFinal();
        $class->addComment('Permissions the API requires per endpoint and HTTP method, from the x-required-permissions extension.');
        $class->addConstant('REQUIRED', $required)
            ->setPublic()
            ->addComment('@var array<string, array<string, list<string>>>');

        $method = $class->addMethod('required')->setStatic()->setReturnType('array');
        $method->addParameter('method')->setType('string');
        $method->addParameter('path')->setType('string');
        $method->addComment("Permissions needed for an HTTP method on a path template, or an empty list when none are declared.\n\n@return list<string>");
        $method->setBody('return self::REQUIRED[$path][strtoupper($method)] ?? [];');

        return $file;
    }
}
