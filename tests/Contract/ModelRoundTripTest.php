<?php

declare(strict_types=1);

namespace OpusDNS\Client\Tests\Contract;

use OpusDNS\Client\ApiModel;
use OpusDNS\Client\Tests\Support\SampleFactory;
use OpusDNS\Generator\Spec;
use OpusDNS\Generator\TypeResolver;
use PHPUnit\Framework\TestCase;

/**
 * Hydrates and serializes every generated model from a payload built out of the specification, so union,
 * enum, date and nested-model handling is exercised for all schemas, not only the hand-picked ones.
 */
final class ModelRoundTripTest extends TestCase
{
    public function testEveryModelSurvivesARoundTrip(): void
    {
        $spec = Spec::load(__DIR__ . '/../../spec/openapi.yaml');
        $types = new TypeResolver($spec);
        $factory = new SampleFactory($spec);

        $checked = 0;
        $failures = [];
        foreach ($spec->schemas() as $name => $schema) {
            if (!TypeResolver::isModelSchema($schema)) {
                continue;
            }
            /** @var class-string<ApiModel> $class */
            $class = TypeResolver::MODEL_NAMESPACE . '\\' . $types->className($name);
            [$payload, $expected] = $factory->model($name);

            try {
                $model = $class::fromArray($payload);
                $actual = $model->toArray();
                $this->assertSubset($expected, $actual, $name);
                self::assertEquals($actual, $class::fromArray($actual)->toArray(), "{$name}: serializing twice changes the output");
                $checked++;
            } catch (\Throwable $throwable) {
                $failures[] = sprintf('%s: %s (%s)', $name, $throwable->getMessage(), $throwable::class);
            }
        }

        self::assertSame([], $failures, implode("\n", $failures));
        self::assertGreaterThan(400, $checked);
    }

    /**
     * Every expected key must be present with an equal value; extra keys in the actual output are allowed because
     * properties with defaults appear even when the payload omitted them.
     *
     * @param array<mixed> $expected
     * @param array<mixed> $actual
     */
    private function assertSubset(array $expected, array $actual, string $path): void
    {
        foreach ($expected as $key => $value) {
            self::assertArrayHasKey($key, $actual, "{$path}.{$key} is missing from toArray()");
            if (is_array($value) && is_array($actual[$key])) {
                $this->assertSubset($value, $actual[$key], "{$path}.{$key}");
                continue;
            }
            self::assertEquals($value, $actual[$key], "{$path}.{$key} does not round-trip");
        }
    }
}
