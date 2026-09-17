<?php

declare(strict_types=1);

namespace OpusDNS\Client\Tests;

use OpusDNS\Client\Union;
use OpusDNS\Client\Tests\Support\UnionModelA;
use OpusDNS\Client\Tests\Support\UnionModelB;
use PHPUnit\Framework\TestCase;

final class UnionTest extends TestCase
{
    public function testDiscriminateUsesTheMapping(): void
    {
        $mapping = ['a' => UnionModelA::class, 'b' => UnionModelB::class];

        self::assertInstanceOf(UnionModelA::class, Union::discriminate(['kind' => 'a', 'left' => 'l'], 'kind', $mapping));
        self::assertInstanceOf(UnionModelB::class, Union::discriminate(['kind' => 'b', 'left' => 'l', 'right' => 'r'], 'kind', $mapping));
    }

    public function testDiscriminateRejectsUnknownValues(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage("discriminator 'kind' is \"c\", expected one of a, b");
        Union::discriminate(['kind' => 'c'], 'kind', ['a' => UnionModelA::class, 'b' => UnionModelB::class]);
    }

    public function testHydratePicksTheFirstCandidateWhoseRequiredKeysExist(): void
    {
        $candidates = [UnionModelB::class => ['kind', 'left', 'right'], UnionModelA::class => ['kind', 'left']];

        self::assertInstanceOf(UnionModelB::class, Union::hydrate(['kind' => 'x', 'left' => 'l', 'right' => 'r'], $candidates));
        self::assertInstanceOf(UnionModelA::class, Union::hydrate(['kind' => 'x', 'left' => 'l'], $candidates));
    }

    public function testHydrateFallsBackToTheFirstCandidate(): void
    {
        $model = Union::hydrate(['kind' => 'x', 'left' => 'l'], [UnionModelA::class => ['kind', 'left', 'missing']]);

        self::assertInstanceOf(UnionModelA::class, $model);
    }
}
