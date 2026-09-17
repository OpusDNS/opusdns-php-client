<?php

declare(strict_types=1);

namespace OpusDNS\Generator;

use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PhpNamespace;

final class FileFactory
{
    public const MARKER = 'This file is generated from the OpenAPI specification by bin/generate.';

    /** @return array{PhpFile, PhpNamespace} */
    public static function create(string $namespace): array
    {
        $file = new PhpFile();
        $file->setStrictTypes();
        $file->addComment(self::MARKER . "\nDo not edit it by hand; regenerate it instead.");

        return [$file, $file->addNamespace($namespace)];
    }

    /** Adds an import unless the class lives in the namespace itself. */
    public static function addUse(PhpNamespace $namespace, string $fqcn): void
    {
        $position = strrpos($fqcn, '\\');
        $owner = $position === false ? '' : substr($fqcn, 0, $position);
        if ($owner === $namespace->getName()) {
            return;
        }
        $namespace->addUse($fqcn);
    }

    /** @param list<string> $fqcns */
    public static function addUses(PhpNamespace $namespace, array $fqcns): void
    {
        foreach ($fqcns as $fqcn) {
            self::addUse($namespace, $fqcn);
        }
    }
}
