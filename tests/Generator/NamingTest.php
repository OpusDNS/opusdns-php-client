<?php

declare(strict_types=1);

namespace OpusDNS\Client\Tests\Generator;

use OpusDNS\Generator\Naming;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class NamingTest extends TestCase
{
    /** @return iterable<string, array{string, string}> */
    public static function classNames(): iterable
    {
        yield 'plain' => ['DnsZone', 'DnsZone'];
        yield 'generic wrapper' => ['PageResponse_JobResponse_', 'PageResponseJobResponse'];
        yield 'fastapi body' => ['Body_upload_asset_v1_assets_post', 'BodyUploadAssetV1AssetsPost'];
        yield 'reserved word' => ['List', 'ListSchema'];
        yield 'leading digit' => ['3dModel', '_3dModel'];
    }

    #[DataProvider('classNames')]
    public function testClassName(string $schema, string $expected): void
    {
        self::assertSame($expected, Naming::className($schema));
    }

    /** @return iterable<string, array{string|int, string}> */
    public static function enumCases(): iterable
    {
        yield 'snake' => ['pending_delete', 'PENDING_DELETE'];
        yield 'kebab' => ['pending-adoption', 'PENDING_ADOPTION'];
        yield 'upper kebab' => ['HARD-BOUNCE', 'HARD_BOUNCE'];
        yield 'colon' => ['at-ext-contact:type', 'AT_EXT_CONTACT_TYPE'];
        yield 'digit first' => ['1d', '_1D'];
        yield 'int' => [301, '_301'];
        yield 'float-like' => ['1.0', '_1_0'];
        yield 'camel' => ['matchAny', 'MATCH_ANY'];
        yield 'class keyword' => ['class', 'CLASS_'];
        yield 'empty' => ['', 'EMPTY'];
    }

    #[DataProvider('enumCases')]
    public function testEnumCase(string|int $value, string $expected): void
    {
        self::assertSame($expected, Naming::enumCase($value));
    }

    public function testPropertyName(): void
    {
        self::assertSame('dnssecStatus', Naming::propertyName('dnssec_status'));
        self::assertSame('pageSize', Naming::propertyName('page_size'));
        self::assertSame('_2fa', Naming::propertyName('2fa'));
    }

    public function testMethodNameStripsTheFastApiSuffix(): void
    {
        self::assertSame('listZones', Naming::methodName('list_zones_v1_dns_get', '/v1/dns', 'get'));
        self::assertSame('requestAuthCode', Naming::methodName('request_auth_code_v1_domains_tld_specific_be__domain_reference__auth_code_request_post', '/x', 'post'));
        self::assertSame('dnsByZoneNameGet', Naming::methodName('', '/v1/dns/{zone_name}', 'get'));
    }

    public function testPathNamesAndEndpointConstants(): void
    {
        self::assertSame('DnsByZoneNameRrsets', Naming::pathName('/v1/dns/{zone_name}/rrsets'));
        self::assertSame('DomainsTldSpecificBeByDomainReferenceAuthCodeRequest', Naming::pathName('/v1/domains/tld-specific/be/{domain_reference}/auth_code/request'));
        self::assertSame('DNS_BY_ZONE_NAME_RRSETS', Naming::endpointConstant('/v1/dns/{zone_name}/rrsets'));
        self::assertSame('AI_CONCIERGE_CONTEXTS', Naming::endpointConstant('/v1/ai-concierge/contexts'));
        self::assertSame('TLDS', Naming::endpointConstant('/v1/tlds/'));
    }

    public function testServiceNames(): void
    {
        self::assertSame('DomainTldSpecificService', Naming::serviceClassName('domain_tld_specific'));
        self::assertSame('DnsService', Naming::serviceClassName('dns'));
        self::assertSame('VanityNameserversService', Naming::serviceClassName('nameserver'));
        self::assertSame('aiConcierge', Naming::accessorName('ai_concierge'));
        self::assertSame('vanityNameservers', Naming::accessorName('nameserver'));
    }

    public function testUniqueAppendsCountersCaseInsensitively(): void
    {
        $used = [];
        self::assertSame('Foo', Naming::unique('Foo', $used));
        self::assertSame('foo2', Naming::unique('foo', $used));
        self::assertSame('Foo_2', Naming::unique('Foo', $used, '_'));
    }
}
