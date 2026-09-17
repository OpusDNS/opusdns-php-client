<?php

declare(strict_types=1);

use OpusDNS\Client\Client;
use OpusDNS\Client\Config;
use OpusDNS\Client\Exception\HttpException;
use OpusDNS\Client\Exception\OpusDnsException;
use OpusDNS\Client\Exception\ValidationException;

require __DIR__ . '/../vendor/autoload.php';

function sandboxClient(): Client
{
    $apiKey = getenv('OPUSDNS_API_KEY') ?: envFile(dirname(__DIR__) . '/.env')['OPUSDNS_API_KEY'] ?? '';
    if ($apiKey === '') {
        fwrite(STDERR, "Set OPUSDNS_API_KEY in the environment or in .env (see .env.example).\n");
        exit(1);
    }

    return Client::create(Config::sandbox($apiKey));
}

/** @return array<string, string> */
function envFile(string $path): array
{
    if (!is_file($path)) {
        return [];
    }
    $values = [];
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$name, $value] = explode('=', $line, 2);
        $values[trim($name)] = trim(trim($value), '"\'');
    }

    return $values;
}

function argument(int $position, string $name): string
{
    global $argv;
    $value = $argv[$position] ?? '';
    if ($value === '') {
        fwrite(STDERR, "Usage: php {$argv[0]} <{$name}>\n");
        exit(1);
    }

    return $value;
}

function run(callable $script): void
{
    try {
        $script();
    } catch (ValidationException $exception) {
        fwrite(STDERR, "Validation failed:\n");
        foreach ($exception->messages() as $message) {
            fwrite(STDERR, "  {$message}\n");
        }
        exit(1);
    } catch (HttpException $exception) {
        fwrite(STDERR, "API error {$exception->statusCode()}: {$exception->problemTitle}. {$exception->problemDetail}\n");
        exit(1);
    } catch (OpusDnsException $exception) {
        fwrite(STDERR, "Client error: {$exception->getMessage()}\n");
        exit(1);
    }
}
