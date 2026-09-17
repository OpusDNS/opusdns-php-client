<?php

declare(strict_types=1);

namespace OpusDNS\Client\Tests\Service;

use GuzzleHttp\Psr7\Response;
use OpusDNS\Client\Enum\DomainContactType;
use OpusDNS\Client\Enum\PeriodUnit;
use OpusDNS\Client\Enum\RenewalMode;
use OpusDNS\Client\Exception\ConflictException;
use OpusDNS\Client\Exception\ValidationException;
use OpusDNS\Client\Model\ContactHandle;
use OpusDNS\Client\Model\DomainCreate;
use OpusDNS\Client\Model\DomainPeriod;
use OpusDNS\Client\Model\DomainRenewRequest;
use OpusDNS\Client\Model\DomainResponse;
use OpusDNS\Client\Model\DomainTransferIn;
use OpusDNS\Client\Model\DomainUpdate;
use OpusDNS\Client\Model\Nameserver;
use OpusDNS\Client\Tests\Support\ClientFactory;
use PHPUnit\Framework\TestCase;

/**
 * Registrar flows through the generated DomainService: availability, registration, lookup, renewal, transfer,
 * update and deletion, all against a fake transport.
 */
final class DomainServiceTest extends TestCase
{
    /** @return array<string, mixed> */
    private static function domainJson(): array
    {
        return [
            'name' => 'example.com',
            'roid' => 'D123-OPUS',
            'sld' => 'example',
            'tld' => 'com',
            'domain_id' => 'domain_01h45ytscbebyvny4gc8cr8ma2',
            'registered_on' => '2025-01-31T10:00:00Z',
            'expires_on' => '2027-01-31T10:00:00Z',
            'renewal_mode' => 'expire',
            'transfer_lock' => true,
            'contacts' => [['contact_id' => 'contact_01h45ytscbebyvny4gc8cr8ma2', 'contact_type' => 'registrant']],
            'nameservers' => [['hostname' => 'ns1.example.net'], ['hostname' => 'ns2.example.net']],
            'registry_statuses' => ['ok'],
        ];
    }

    public function testCheckAvailability(): void
    {
        [$client, $http] = ClientFactory::create();
        $http->queueJson(200, ['results' => [
            ['domain' => 'example.com', 'available' => false, 'reason' => 'registered'],
            ['domain' => 'example.net', 'available' => true, 'is_premium' => true],
        ]]);

        $check = $client->domain()->eppCheckDomain(['example.com', 'example.net']);

        self::assertSame('GET', $http->lastRequest()->getMethod());
        self::assertSame('https://sandbox.opusdns.com/v1/domains/check?domains=example.com&domains=example.net', (string) $http->lastRequest()->getUri());
        self::assertCount(2, $check->results);
        self::assertFalse($check->results[0]->available);
        self::assertSame('registered', $check->results[0]->reason);
        self::assertFalse($check->results[0]->isPremium);
        self::assertTrue($check->results[1]->available);
        self::assertTrue($check->results[1]->isPremium);
    }

    public function testRegisterDomain(): void
    {
        [$client, $http] = ClientFactory::create();
        $http->queueJson(201, self::domainJson());

        $domain = $client->domain()->createDomain(new DomainCreate(
            contacts: [DomainContactType::REGISTRANT->value => [new ContactHandle('contact_01h45ytscbebyvny4gc8cr8ma2')]],
            name: 'example.com',
            period: new DomainPeriod(PeriodUnit::Y, 1),
            renewalMode: RenewalMode::EXPIRE,
            nameservers: [new Nameserver('ns1.example.net'), new Nameserver('ns2.example.net')],
            expectedPrice: '9.99',
        ));

        $request = $http->lastRequest();
        self::assertSame('POST', $request->getMethod());
        self::assertSame('https://sandbox.opusdns.com/v1/domains', (string) $request->getUri());
        self::assertEquals([
            'contacts' => ['registrant' => [['contact_id' => 'contact_01h45ytscbebyvny4gc8cr8ma2']]],
            'name' => 'example.com',
            'period' => ['unit' => 'y', 'value' => 1],
            'renewal_mode' => 'expire',
            'create_zone' => false,
            'expected_price' => '9.99',
            'nameservers' => [['hostname' => 'ns1.example.net'], ['hostname' => 'ns2.example.net']],
        ], json_decode((string) $request->getBody(), true));

        self::assertInstanceOf(DomainResponse::class, $domain);
        self::assertSame('example.com', $domain->name);
        self::assertSame('D123-OPUS', $domain->roid);
        self::assertSame('2027-01-31T10:00:00+00:00', $domain->expiresOn?->format(DATE_ATOM));
        self::assertSame(RenewalMode::EXPIRE, $domain->renewalMode);
        self::assertTrue($domain->transferLock);
        self::assertNotNull($domain->contacts);
        self::assertSame(DomainContactType::REGISTRANT, $domain->contacts[0]->contactType);
        self::assertNotNull($domain->nameservers);
        self::assertSame('ns2.example.net', $domain->nameservers[1]->hostname);
        self::assertSame(['ok'], $domain->registryStatuses);
    }

    public function testGetDomain(): void
    {
        [$client, $http] = ClientFactory::create();
        $http->queueJson(200, self::domainJson());

        $domain = $client->domain()->getDomain('example.com');

        self::assertSame('GET', $http->lastRequest()->getMethod());
        self::assertSame('https://sandbox.opusdns.com/v1/domains/example.com', (string) $http->lastRequest()->getUri());
        self::assertSame('example', $domain->sld);
        self::assertSame('com', $domain->tld);
        self::assertSame('2025-01-31T10:00:00+00:00', $domain->registeredOn?->format(DATE_ATOM));
    }

    public function testRenewDomain(): void
    {
        [$client, $http] = ClientFactory::create();
        $http->queueJson(200, [
            'name' => 'example.com',
            'new_expiry_date' => '2028-01-31T10:00:00Z',
            'period_extended' => ['unit' => 'y', 'value' => 1],
        ]);

        $renewal = $client->domain()->renewDomain('example.com', new DomainRenewRequest(
            currentExpiryDate: new \DateTimeImmutable('2027-01-31T10:00:00Z'),
            period: new DomainPeriod(PeriodUnit::Y, 1),
            expectedPrice: '12.50',
        ));

        $request = $http->lastRequest();
        self::assertSame('POST', $request->getMethod());
        self::assertSame('https://sandbox.opusdns.com/v1/domains/example.com/renew', (string) $request->getUri());
        self::assertEquals([
            'current_expiry_date' => '2027-01-31T10:00:00Z',
            'period' => ['unit' => 'y', 'value' => 1],
            'expected_price' => '12.50',
        ], json_decode((string) $request->getBody(), true));
        self::assertSame('2028-01-31T10:00:00+00:00', $renewal->newExpiryDate->format(DATE_ATOM));
        self::assertSame(PeriodUnit::Y, $renewal->periodExtended->unit);
        self::assertSame(1, $renewal->periodExtended->value);
    }

    public function testTransferDomain(): void
    {
        [$client, $http] = ClientFactory::create();
        $http->queueJson(201, ['name' => 'example.org', 'roid' => 'D456-OPUS', 'sld' => 'example', 'tld' => 'org']);

        $domain = $client->domain()->transferDomain(new DomainTransferIn('example.org', RenewalMode::RENEW, authCode: 'epp-secret'));

        $request = $http->lastRequest();
        self::assertSame('POST', $request->getMethod());
        self::assertSame('https://sandbox.opusdns.com/v1/domains/transfer', (string) $request->getUri());
        self::assertEquals([
            'name' => 'example.org',
            'renewal_mode' => 'renew',
            'auth_code' => 'epp-secret',
            'create_zone' => false,
        ], json_decode((string) $request->getBody(), true));
        self::assertSame('example.org', $domain->name);
        self::assertNull($domain->expiresOn);
    }

    public function testUpdateDomainSendsOnlyTheGivenFields(): void
    {
        [$client, $http] = ClientFactory::create();
        $http->queueJson(200, self::domainJson());

        $client->domain()->updateDomain('example.com', new DomainUpdate(renewalMode: RenewalMode::RENEW));

        $request = $http->lastRequest();
        self::assertSame('PATCH', $request->getMethod());
        self::assertSame('https://sandbox.opusdns.com/v1/domains/example.com', (string) $request->getUri());
        self::assertSame('{"renewal_mode":"renew"}', (string) $request->getBody());

        $http->queueJson(200, self::domainJson());
        $client->domain()->updateDomain('example.com', ['nameservers' => [['hostname' => 'ns1.example.net']], 'auth_code' => null]);
        self::assertSame('{"nameservers":[{"hostname":"ns1.example.net"}]}', (string) $http->lastRequest()->getBody());
    }

    public function testDeleteDomain(): void
    {
        [$client, $http] = ClientFactory::create();
        $http->queue(new Response(204));

        $client->domain()->deleteDomain('example.com');

        self::assertSame('DELETE', $http->lastRequest()->getMethod());
        self::assertSame('https://sandbox.opusdns.com/v1/domains/example.com', (string) $http->lastRequest()->getUri());
    }

    public function testListDomainsWithFilters(): void
    {
        [$client, $http] = ClientFactory::create();
        $http->queueJson(200, [
            'pagination' => ['current_page' => 1, 'has_next_page' => false, 'has_previous_page' => false, 'page_size' => 10, 'total_items' => 1, 'total_pages' => 1],
            'results' => [self::domainJson()],
        ]);

        $page = $client->domain()->getDomains(search: 'exam', expiresIn30Days: true);

        self::assertSame(
            'https://sandbox.opusdns.com/v1/domains?page=1&page_size=10&sort_by=created_on&sort_order=desc&status_tag_mode=match_any&tag_mode=match_any&search=exam&expires_in_30_days=true',
            (string) $http->lastRequest()->getUri(),
        );
        self::assertSame(1, $page->pagination->totalItems);
        self::assertSame('example.com', $page->results[0]->name);
        self::assertSame(RenewalMode::EXPIRE, $page->results[0]->renewalMode);
    }

    public function testRegistrationConflictsAndValidationErrorsAreTyped(): void
    {
        [$client, $http] = ClientFactory::create();
        $http->queueJson(409, ['type' => 'about:blank', 'title' => 'Conflict', 'status' => 409, 'detail' => 'Domain already registered']);

        try {
            $client->domain()->createDomain(['name' => 'example.com']);
            self::fail('Expected a ConflictException');
        } catch (ConflictException $exception) {
            self::assertSame('Domain already registered', $exception->problemDetail);
        }

        $http->queueJson(422, ['type' => 'about:blank', 'title' => 'Request validation failed', 'status' => 422, 'errors' => [
            ['loc' => ['body', 'period'], 'msg' => 'Field required', 'type' => 'missing'],
        ]]);

        try {
            $client->domain()->createDomain(['name' => 'example.com']);
            self::fail('Expected a ValidationException');
        } catch (ValidationException $exception) {
            self::assertSame(['body.period: Field required'], $exception->messages());
        }
    }
}
