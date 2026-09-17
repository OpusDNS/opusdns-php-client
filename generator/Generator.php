<?php

declare(strict_types=1);

namespace OpusDNS\Generator;

use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PsrPrinter;
use OpusDNS\Generator\Emitter\EndpointEmitter;
use OpusDNS\Generator\Emitter\EnumEmitter;
use OpusDNS\Generator\Emitter\ModelEmitter;
use OpusDNS\Generator\Emitter\PermissionEmitter;
use OpusDNS\Generator\Emitter\ServiceEmitter;

final class Generator
{
    /** Short names of hand-written classes that generated files import; a schema with one of these names would clash. */
    private const RUNTIME_SHORT_NAMES = ['ApiModel', 'Client', 'Config', 'Endpoint', 'Permission', 'Serializer', 'Union', 'Paginator'];

    /**
     * Generates the client into $outDir from the specification at $specPath.
     *
     * @return array<string, int|string> Counts of what was written
     */
    public function generate(string $specPath, string $outDir): array
    {
        $spec = Spec::load($specPath);
        $types = new TypeResolver($spec);
        $this->assertNoClashes($spec, $types);

        $endpoints = EndpointEmitter::constants($spec);
        $enums = (new EnumEmitter($spec, $types))->emit();
        $models = (new ModelEmitter($spec, $types))->emit();
        $services = (new ServiceEmitter($spec, $types, $endpoints))->emit();

        $files = $enums + $models + $services;
        $files['Endpoint.php'] = (new EndpointEmitter($spec, $endpoints))->emit();
        $files['Permission.php'] = (new PermissionEmitter($spec))->emit();

        $this->clean($outDir);
        $printer = new PsrPrinter();
        foreach ($files as $relative => $file) {
            $this->write("{$outDir}/{$relative}", $printer->printFile($file));
        }

        return [
            'spec_version' => $spec->version(),
            'files' => count($files),
            'enums' => count($enums),
            'models' => count($models),
            'services' => count($services) - 1,
            'operations' => count($spec->operations()),
        ];
    }

    private function assertNoClashes(Spec $spec, TypeResolver $types): void
    {
        $clashes = [];
        foreach (array_keys($spec->schemas()) as $name) {
            if (in_array($types->className($name), self::RUNTIME_SHORT_NAMES, true)) {
                $clashes[] = $name;
            }
        }
        if ($clashes !== []) {
            throw new \RuntimeException('Schema names clash with runtime classes: ' . implode(', ', $clashes));
        }
    }

    /** Deletes previously generated files (identified by their header) so removed schemas do not linger. */
    private function clean(string $outDir): void
    {
        if (!is_dir($outDir)) {
            return;
        }
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($outDir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );
        foreach ($iterator as $entry) {
            /** @var \SplFileInfo $entry */
            if ($entry->isFile() && $entry->getExtension() === 'php') {
                $head = (string) file_get_contents($entry->getPathname(), false, null, 0, 400);
                if (str_contains($head, FileFactory::MARKER)) {
                    unlink($entry->getPathname());
                }
            } elseif ($entry->isDir() && count(scandir($entry->getPathname()) ?: []) === 2) {
                rmdir($entry->getPathname());
            }
        }
    }

    private function write(string $path, string $contents): void
    {
        $dir = dirname($path);
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new \RuntimeException("Cannot create directory {$dir}");
        }
        if (file_put_contents($path, $contents) === false) {
            throw new \RuntimeException("Cannot write {$path}");
        }
    }
}
