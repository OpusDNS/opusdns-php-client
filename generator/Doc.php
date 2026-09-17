<?php

declare(strict_types=1);

namespace OpusDNS\Generator;

final class Doc
{
    /**
     * Class-level docblock for a schema: its title when it adds information, then its description.
     *
     * @param array<string, mixed> $schema
     */
    public static function forSchema(string $schemaName, string $className, array $schema): string
    {
        $lines = [];
        $title = Naming::oneLine($schema['title'] ?? null);
        if ($title !== '' && strcasecmp($title, $className) !== 0 && strcasecmp($title, $schemaName) !== 0) {
            $lines[] = $title;
        }
        $description = self::wrap($schema['description'] ?? null);
        if ($description !== '') {
            if ($lines !== []) {
                $lines[] = '';
            }
            $lines[] = $description;
        }

        return implode("\n", $lines);
    }

    /** A docblock tag line such as "@param list<string> $ids Description", wrapped with indented continuation lines. */
    public static function tag(string $tag, string $text = '', int $width = 110): string
    {
        $text = Naming::oneLine($text);
        if ($text === '') {
            return $tag;
        }
        if (strlen($tag) + 1 + strlen($text) <= $width) {
            return $tag . ' ' . $text;
        }

        $lines = explode("\n", wordwrap($text, max(40, $width - strlen($tag) - 1)));
        $first = $tag . ' ' . array_shift($lines);
        if ($lines === []) {
            return $first;
        }

        return $first . "\n    " . str_replace("\n", "\n    ", wordwrap(implode(' ', $lines), $width - 4));
    }

    /** Wraps free text for a docblock, keeping paragraph breaks. */
    public static function wrap(?string $text, int $width = 100): string
    {
        $text = trim((string) $text);
        if ($text === '') {
            return '';
        }
        $paragraphs = preg_split('/\n\s*\n/', $text) ?: [];

        return implode("\n\n", array_map(
            static fn (string $paragraph): string => wordwrap(Naming::oneLine($paragraph), $width),
            $paragraphs,
        ));
    }
}
