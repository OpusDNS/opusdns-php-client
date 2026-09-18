<?php

declare(strict_types=1);

namespace OpusDNS\Client\Tests;

use OpusDNS\Client\Model\DnsZoneResponse;
use OpusDNS\Client\Paginator;
use OpusDNS\Client\Tests\Support\PageFetcher;
use PHPUnit\Framework\TestCase;

final class PaginatorTest extends TestCase
{
    public function testItemsWalkEveryPage(): void
    {
        $fetch = new PageFetcher([
            1 => PageFetcher::page(1, 2, true, ['a.example', 'b.example']),
            2 => PageFetcher::page(2, 2, false, ['c.example']),
        ]);

        $names = [];
        foreach (Paginator::items($fetch) as $zone) {
            $names[] = $zone->name;
        }

        self::assertSame(['a.example', 'b.example', 'c.example'], $names);
        self::assertSame([1, 2], $fetch->requested);
    }

    public function testPagesAreKeyedByPageNumberAndExposeMetadata(): void
    {
        $fetch = new PageFetcher([
            3 => PageFetcher::page(3, 4, true, ['a.example']),
            4 => PageFetcher::page(4, 4, false, ['b.example']),
        ]);

        $pages = iterator_to_array(Paginator::pages($fetch, firstPage: 3));

        self::assertSame([3, 4], array_keys($pages));
        self::assertSame(3, $pages[3]->pagination()->totalItems);
        self::assertSame('b.example', $pages[4]->results()[0]->name);
    }

    public function testStopsOnAnEmptyPageEvenWhenTheApiClaimsMore(): void
    {
        $fetch = new PageFetcher([
            1 => PageFetcher::page(1, 0, true, ['a.example']),
            2 => PageFetcher::page(2, 0, true, []),
            3 => PageFetcher::page(3, 0, true, ['never']),
        ]);

        $names = array_map(static fn (DnsZoneResponse $zone): string => $zone->name, iterator_to_array(Paginator::items($fetch), false));

        self::assertSame(['a.example'], $names);
        self::assertSame([1, 2], $fetch->requested);
    }

    public function testStopsAtTheLastPageNumberEvenWhenTheApiClaimsMore(): void
    {
        $fetch = new PageFetcher([
            1 => PageFetcher::page(1, 2, true, ['a.example']),
            2 => PageFetcher::page(2, 2, true, ['b.example']),
            3 => PageFetcher::page(3, 2, true, ['never']),
        ]);

        iterator_to_array(Paginator::items($fetch), false);

        self::assertSame([1, 2], $fetch->requested);
    }

    public function testMaxPagesCapsTheRequests(): void
    {
        $fetch = new PageFetcher([
            1 => PageFetcher::page(1, 5, true, ['a.example']),
            2 => PageFetcher::page(2, 5, true, ['b.example']),
            3 => PageFetcher::page(3, 5, true, ['c.example']),
        ]);

        $items = iterator_to_array(Paginator::items($fetch, maxPages: 2), false);

        self::assertCount(2, $items);
        self::assertSame([1, 2], $fetch->requested);
    }

    public function testRejectsInvalidPageNumbers(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        iterator_to_array(Paginator::pages(new PageFetcher([]), firstPage: 0));
    }

    public function testRejectsInvalidMaxPages(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        iterator_to_array(Paginator::pages(new PageFetcher([]), maxPages: 0));
    }
}
