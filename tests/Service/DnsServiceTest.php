<?php

declare(strict_types=1);

namespace OpusDNS\Client\Tests\Service;

use GuzzleHttp\Psr7\Response;
use OpusDNS\Client\Enum\DnsRrsetType;
use OpusDNS\Client\Enum\DnssecStatus;
use OpusDNS\Client\Enum\PatchOp;
use OpusDNS\Client\Exception\NotFoundException;
use OpusDNS\Client\Model\DnsChangesResponse;
use OpusDNS\Client\Model\DnsRrsetPatch;
use OpusDNS\Client\Model\DnsRrsetPatchOp;
use OpusDNS\Client\Model\DnsZoneCreate;
use OpusDNS\Client\Model\DnsZoneResponse;
use OpusDNS\Client\Model\DnsZoneRrsetsPatchOps;
use OpusDNS\Client\Tests\Support\ClientFactory;
use PHPUnit\Framework\TestCase;

/**
 * Exercises the generated DnsService end to end through the runtime with a fake transport.
 */
final class DnsServiceTest extends TestCase
{
    public function testGetZoneHydratesTheResponse(): void
    {
        [$client, $http] = ClientFactory::create();
        $http->queueJson(200, [
            'dns_zone_id' => 'zone_01h45ytscbebyvny4gc8cr8ma2',
            'name' => 'example.com',
            'dnssec_status' => 'enabled',
            'created_on' => '2026-01-02T03:04:05Z',
            'rrsets' => [
                ['name' => 'www.example.com.', 'ttl' => 300, 'type' => 'A', 'records' => [['rdata' => '192.0.2.1']]],
            ],
        ]);

        $zone = $client->dns()->getZone('example.com');

        self::assertSame('GET', $http->lastRequest()->getMethod());
        self::assertSame('https://sandbox.opusdns.com/v1/dns/example.com', (string) $http->lastRequest()->getUri());
        self::assertInstanceOf(DnsZoneResponse::class, $zone);
        self::assertSame('example.com', $zone->name);
        self::assertSame(DnssecStatus::ENABLED, $zone->dnssecStatus);
        self::assertSame('2026-01-02T03:04:05+00:00', $zone->createdOn?->format(DATE_ATOM));
        self::assertNotNull($zone->rrsets);
        self::assertSame(DnsRrsetType::A, $zone->rrsets[0]->type);
        self::assertSame(300, $zone->rrsets[0]->ttl);
        self::assertFalse($zone->rrsets[0]->protected);
        self::assertSame('192.0.2.1', $zone->rrsets[0]->records[0]->rdata);
        self::assertSame('enabled', $zone->toArray()['dnssec_status']);
    }

    public function testCreateZoneReturnsNullWhenAccepted(): void
    {
        [$client, $http] = ClientFactory::create();
        $http->queue(new Response(202));

        $result = $client->dns()->createZone(new DnsZoneCreate('example.com', DnssecStatus::ENABLED));

        self::assertNull($result);
        self::assertSame('POST', $http->lastRequest()->getMethod());
        self::assertSame('https://sandbox.opusdns.com/v1/dns', (string) $http->lastRequest()->getUri());
        self::assertSame('{"name":"example.com","dnssec_status":"enabled"}', (string) $http->lastRequest()->getBody());
    }

    public function testCreateZoneHydratesChanges(): void
    {
        [$client, $http] = ClientFactory::create();
        $http->queueJson(200, ['changes' => [], 'num_changes' => 0, 'zone_name' => 'example.com']);

        $changes = $client->dns()->createZone(['name' => 'example.com']);

        self::assertInstanceOf(DnsChangesResponse::class, $changes);
        self::assertSame('example.com', $changes->zoneName);
        self::assertSame(0, $changes->numChanges);
    }

    public function testPatchZoneRrsetsSendsOperations(): void
    {
        [$client, $http] = ClientFactory::create();
        $http->queue(new Response(204));

        $client->dns()->patchZoneRrsets('example.com', new DnsZoneRrsetsPatchOps([
            new DnsRrsetPatchOp(PatchOp::UPSERT, new DnsRrsetPatch('www.example.com.', [['rdata' => '192.0.2.1']], 3600, DnsRrsetType::A)),
        ]));

        $request = $http->lastRequest();
        self::assertSame('PATCH', $request->getMethod());
        self::assertSame('https://sandbox.opusdns.com/v1/dns/example.com/rrsets', (string) $request->getUri());
        self::assertEquals([
            'ops' => [[
                'op' => 'upsert',
                'rrset' => ['name' => 'www.example.com.', 'records' => [['rdata' => '192.0.2.1']], 'ttl' => 3600, 'type' => 'A'],
            ]],
        ], json_decode((string) $request->getBody(), true));
    }

    public function testListZonesSendsDefaultsAndHydratesPages(): void
    {
        [$client, $http] = ClientFactory::create();
        $http->queueJson(200, [
            'pagination' => ['current_page' => 1, 'has_next_page' => false, 'has_previous_page' => false, 'page_size' => 10, 'total_items' => 1, 'total_pages' => 1],
            'results' => [['dns_zone_id' => 'zone_1', 'name' => 'example.com']],
        ]);

        $page = $client->dns()->listZones(search: 'exam');

        self::assertSame(
            'https://sandbox.opusdns.com/v1/dns?page=1&page_size=10&sort_by=created_on&sort_order=desc&tag_mode=match_any&search=exam',
            (string) $http->lastRequest()->getUri(),
        );
        self::assertSame(1, $page->pagination->totalItems);
        self::assertFalse($page->pagination->hasNextPage);
        self::assertSame('example.com', $page->results[0]->name);
        self::assertSame(DnssecStatus::DISABLED, $page->results[0]->dnssecStatus);
    }

    public function testDeleteZone(): void
    {
        [$client, $http] = ClientFactory::create();
        $http->queue(new Response(204));

        $client->dns()->deleteZone('example.com');

        self::assertSame('DELETE', $http->lastRequest()->getMethod());
        self::assertSame('https://sandbox.opusdns.com/v1/dns/example.com', (string) $http->lastRequest()->getUri());
    }

    public function testErrorsPropagate(): void
    {
        [$client, $http] = ClientFactory::create();
        $http->queueJson(404, ['type' => 'about:blank', 'title' => 'Not Found', 'status' => 404]);

        $this->expectException(NotFoundException::class);
        $client->dns()->getZone('missing.example');
    }

    public function testUnknownEnumValuesAreRejected(): void
    {
        [$client, $http] = ClientFactory::create();
        $http->queueJson(200, ['dns_zone_id' => 'z', 'name' => 'example.com', 'dnssec_status' => 'brand-new']);

        $this->expectException(\ValueError::class);
        $client->dns()->getZone('example.com');
    }
}
