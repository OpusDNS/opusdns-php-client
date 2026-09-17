<?php

declare(strict_types=1);

namespace OpusDNS\Generator\Emitter;

use Nette\PhpGenerator\PhpFile;
use OpusDNS\Generator\FileFactory;
use OpusDNS\Generator\Naming;
use OpusDNS\Generator\Spec;
use OpusDNS\Generator\TypeResolver;

/**
 * Emits the Endpoint class: one constant per path template, named like the endpoint constants of OpusDNS/api-spec.
 */
final class EndpointEmitter
{
    /** @param array<string, string> $constants */
    public function __construct(private readonly Spec $spec, private readonly array $constants)
    {
    }

    /** @return array<string, string> Path template to constant name */
    public static function constants(Spec $spec): array
    {
        $paths = array_keys($spec->paths());
        sort($paths);
        $used = [];
        $constants = [];
        foreach ($paths as $path) {
            $constants[$path] = Naming::unique(Naming::endpointConstant($path), $used, '_');
        }

        return $constants;
    }

    public function emit(): PhpFile
    {
        [$file, $namespace] = FileFactory::create(TypeResolver::NAMESPACE);
        $class = $namespace->addClass('Endpoint')->setFinal();
        $class->addComment('Path templates of every API endpoint. Placeholders are filled by Client::request().');

        $methods = [];
        foreach ($this->spec->operations() as $operation) {
            $methods[$operation->path][] = $operation->httpMethod();
        }
        foreach ($this->constants as $path => $name) {
            $class->addConstant($name, $path)
                ->setPublic()
                ->addComment(implode(', ', $methods[$path] ?? []));
        }

        return $file;
    }
}
