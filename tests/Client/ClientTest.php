<?php

declare(strict_types=1);

namespace OpusDNS\Client\Tests\Client;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Utils;
use OpusDNS\Client\Client;
use OpusDNS\Client\Config;
use OpusDNS\Client\Endpoint;
use OpusDNS\Client\Enum\SortOrder;
use OpusDNS\Client\Exception\BadRequestException;
use OpusDNS\Client\Exception\ConflictException;
use OpusDNS\Client\Exception\DecodingException;
use OpusDNS\Client\Exception\ForbiddenException;
use OpusDNS\Client\Exception\HttpException;
use OpusDNS\Client\Exception\NetworkException;
use OpusDNS\Client\Exception\NotFoundException;
use OpusDNS\Client\Exception\RateLimitException;
use OpusDNS\Client\Exception\ServerException;
use OpusDNS\Client\Exception\UnauthorizedException;
use OpusDNS\Client\Exception\ValidationException;
use OpusDNS\Client\Model\DnsZoneCreate;
use OpusDNS\Client\Model\DomainUpdate;
use OpusDNS\Client\Tests\Support\ClientFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;

final class ClientTest extends TestCase
{
    public function testSendsAuthenticationAndDefaultHeaders(): void
    {
        [$client, $http] = ClientFactory::create(new Config('secret', Config::SANDBOX_URL, headers: ['X-Trace' => 'abc']));
        $http->queueJson(200, ['ok' => true]);

        $client->request('GET', Endpoint::DNS, headers: ['X-Trace' => 'override', 'X-Skip' => null]);

        $request = $http->lastRequest();
        self::assertSame('GET', $request->getMethod());
        self::assertSame('https://sandbox.opusdns.com/v1/dns', (string) $request->getUri());
        self::assertSame('secret', $request->getHeaderLine('X-Api-Key'));
        self::assertSame('application/json', $request->getHeaderLine('Accept'));
        self::assertMatchesRegularExpression('#^opusdns-php-client/(\d+\.\d+\.\d+\S*|dev)$#', $request->getHeaderLine('X-OpusDNS-Client'));
        self::assertSame(Config::defaultClientToken(), $request->getHeaderLine('X-OpusDNS-Client'));
        self::assertSame(Config::defaultClientToken(), $request->getHeaderLine('User-Agent'));
        self::assertSame('override', $request->getHeaderLine('X-Trace'));
        self::assertFalse($request->hasHeader('X-Skip'));
        self::assertFalse($request->hasHeader('Content-Type'));
    }

    public function testClientTokenAndUserAgentCanBeOverriddenOrOmitted(): void
    {
        [$client, $http] = ClientFactory::create(new Config('k', Config::SANDBOX_URL, userAgent: 'billing/2.0', clientToken: 'my-integration/3.1'));
        $client->request('GET', '/v1/dns');
        self::assertSame('billing/2.0', $http->lastRequest()->getHeaderLine('User-Agent'));
        self::assertSame('my-integration/3.1', $http->lastRequest()->getHeaderLine('X-OpusDNS-Client'));

        [$client, $http] = ClientFactory::create(new Config('k', Config::SANDBOX_URL, clientToken: ''));
        $client->request('GET', '/v1/dns');
        self::assertFalse($http->lastRequest()->hasHeader('X-OpusDNS-Client'));
        self::assertSame(Config::defaultClientToken(), $http->lastRequest()->getHeaderLine('User-Agent'));
    }

    public function testBaseUrls(): void
    {
        self::assertSame(Config::SANDBOX_URL, Config::sandbox('k')->baseUrl);
        self::assertSame(Config::PRODUCTION_URL, Config::production('k')->baseUrl);
        self::assertSame(Config::PRODUCTION_URL, (new Config('k'))->baseUrl);
        self::assertSame('https://example.test', (new Config('k', 'https://example.test/'))->baseUrl);
    }

    public function testFillsPathTemplatesAndSerializesQueries(): void
    {
        [$client, $http] = ClientFactory::create();

        $client->request('GET', Endpoint::DNS_BY_ZONE_NAME, path: ['zone_name' => 'ex ample.com'], query: [
            'page' => 2,
            'flag' => true,
            'skip' => null,
            'sort' => SortOrder::DESC,
            'ids' => ['a', 'b'],
            'since' => new \DateTimeImmutable('2026-01-02T03:04:05+02:00'),
        ]);

        self::assertSame(
            'https://sandbox.opusdns.com/v1/dns/ex%20ample.com?page=2&flag=true&sort=desc&ids=a&ids=b&since=2026-01-02T01%3A04%3A05Z',
            (string) $http->lastRequest()->getUri(),
        );
    }

    public function testMissingPathParametersAreRejected(): void
    {
        [$client] = ClientFactory::create();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Missing path parameter 'zone_name'");
        $client->request('GET', Endpoint::DNS_BY_ZONE_NAME);
    }

    public function testEncodesJsonBodies(): void
    {
        [$client, $http] = ClientFactory::create();

        $client->request('POST', Endpoint::DNS, body: new DnsZoneCreate('example.com'));
        self::assertSame('application/json', $http->lastRequest()->getHeaderLine('Content-Type'));
        self::assertSame('{"name":"example.com","dnssec_status":"disabled"}', (string) $http->lastRequest()->getBody());

        $client->request('POST', Endpoint::DNS, body: ['name' => 'x', 'nothing' => null, 'nested' => ['a' => null, 'b' => 1]]);
        self::assertSame('{"name":"x","nested":{"b":1}}', (string) $http->lastRequest()->getBody());

        $client->request('POST', Endpoint::DNS, body: []);
        self::assertSame('[]', (string) $http->lastRequest()->getBody());

        $client->request('PATCH', Endpoint::DOMAINS_BY_DOMAIN_REFERENCE, path: ['domain_reference' => 'example.com'], body: new DomainUpdate(attributes: []));
        self::assertSame('{"attributes":{}}', (string) $http->lastRequest()->getBody());
    }

    public function testEncodesFormAndMultipartBodies(): void
    {
        [$client, $http] = ClientFactory::create();

        $client->request('POST', '/v1/auth/token', body: ['grant_type' => 'x', 'a b' => 'c&d'], contentType: Client::CONTENT_FORM);
        self::assertSame('application/x-www-form-urlencoded', $http->lastRequest()->getHeaderLine('Content-Type'));
        self::assertSame('grant_type=x&a%20b=c%26d', (string) $http->lastRequest()->getBody());

        $client->request('POST', '/v1/upload', body: ['name' => 'logo', 'file' => Utils::streamFor('PNGDATA')], contentType: Client::CONTENT_MULTIPART);
        $request = $http->lastRequest();
        self::assertMatchesRegularExpression('#^multipart/form-data; boundary=[0-9a-f]{32}$#', $request->getHeaderLine('Content-Type'));
        $body = (string) $request->getBody();
        self::assertStringContainsString("Content-Disposition: form-data; name=\"name\"\r\n\r\nlogo\r\n", $body);
        self::assertStringContainsString('name="file"; filename="', $body);
        self::assertStringContainsString("PNGDATA\r\n", $body);
    }

    public function testMapsProblemResponsesToExceptions(): void
    {
        [$client, $http] = ClientFactory::create();
        $http->queueJson(404, ['type' => 'about:blank', 'title' => 'Not Found', 'status' => 404, 'detail' => 'Zone example.com does not exist']);

        try {
            $client->request('GET', Endpoint::DNS_BY_ZONE_NAME, path: ['zone_name' => 'example.com']);
            self::fail('Expected a NotFoundException');
        } catch (NotFoundException $exception) {
            self::assertSame(404, $exception->statusCode());
            self::assertSame(404, $exception->getCode());
            self::assertSame('about:blank', $exception->problemType);
            self::assertSame('Not Found', $exception->problemTitle);
            self::assertSame('Zone example.com does not exist', $exception->problemDetail);
            self::assertSame('HTTP 404 for GET https://sandbox.opusdns.com/v1/dns/example.com: Not Found. Zone example.com does not exist', $exception->getMessage());
            self::assertSame(404, $exception->body['status']);
            self::assertSame('GET', $exception->request->getMethod());
            self::assertFalse($exception->request->hasHeader('X-Api-Key'));
        }
    }

    /** @return iterable<string, array{int, class-string<HttpException>}> */
    public static function statusClasses(): iterable
    {
        yield '400' => [400, BadRequestException::class];
        yield '401' => [401, UnauthorizedException::class];
        yield '403' => [403, ForbiddenException::class];
        yield '404' => [404, NotFoundException::class];
        yield '409' => [409, ConflictException::class];
        yield '422' => [422, ValidationException::class];
        yield '429' => [429, RateLimitException::class];
        yield '500' => [500, ServerException::class];
        yield '503' => [503, ServerException::class];
        yield '418' => [418, HttpException::class];
    }

    #[DataProvider('statusClasses')]
    public function testExceptionClassPerStatus(int $status, string $class): void
    {
        [$client, $http] = ClientFactory::create();
        $http->queue(new Response($status, [], 'not json'));

        try {
            $client->request('GET', Endpoint::DNS);
            self::fail('Expected an exception');
        } catch (HttpException $exception) {
            self::assertSame($class, $exception::class);
            self::assertSame([], $exception->body);
            self::assertSame("HTTP {$status} for GET https://sandbox.opusdns.com/v1/dns", $exception->getMessage());
        }
    }

    public function testValidationErrorsExposeMessages(): void
    {
        [$client, $http] = ClientFactory::create();
        $http->queueJson(422, [
            'type' => 'about:blank',
            'title' => 'Request validation failed',
            'status' => 422,
            'errors' => [
                ['loc' => ['body', 'name'], 'msg' => 'Field required', 'type' => 'missing'],
                ['loc' => [], 'msg' => 'Something is off', 'type' => 'value_error'],
            ],
        ]);

        try {
            $client->request('POST', Endpoint::DNS, body: []);
            self::fail('Expected a ValidationException');
        } catch (ValidationException $exception) {
            self::assertCount(2, $exception->errors());
            self::assertSame(['body.name: Field required', 'Something is off'], $exception->messages());
        }
    }

    public function testTransportFailuresBecomeNetworkExceptions(): void
    {
        [$client, $http] = ClientFactory::create();
        $http->queue(new class ('connection refused') extends \RuntimeException implements ClientExceptionInterface {
        });

        $this->expectException(NetworkException::class);
        $this->expectExceptionMessage('GET https://sandbox.opusdns.com/v1/dns failed: connection refused');
        $client->request('GET', Endpoint::DNS);
    }

    public function testDecodeHelpers(): void
    {
        [$client, $http] = ClientFactory::create();

        $http->queue(new Response(204));
        $empty = $client->request('DELETE', '/x');
        self::assertNull($client->decode($empty));
        self::assertNull($client->decodeOptional($empty));

        $http->queueJson(200, [1, 2]);
        self::assertSame([1, 2], $client->decodeList($client->request('GET', '/x')));

        $http->queueJson(200, ['a' => 1]);
        self::assertSame(['a' => 1], $client->decodeArray($client->request('GET', '/x')));

        $http->queueJson(200, ['a' => 1]);
        try {
            $client->decodeList($client->request('GET', '/x'));
            self::fail('Expected a DecodingException');
        } catch (DecodingException $exception) {
            self::assertStringContainsString('Expected a JSON array', $exception->getMessage());
        }

        $http->queueJson(200, 'scalar');
        $this->expectException(DecodingException::class);
        $client->decodeArray($client->request('GET', '/x'));
    }

    public function testInvalidJsonIsReported(): void
    {
        [$client, $http] = ClientFactory::create();
        $http->queue(new Response(200, [], '{oops'));

        $this->expectException(DecodingException::class);
        $client->decode($client->request('GET', '/x'));
    }

    public function testCreateFallsBackToGuzzle(): void
    {
        $client = Client::create(Config::sandbox('k'));

        self::assertSame('k', $client->config()->apiKey);
    }

    public function testConfigRejectsBadInput(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Config(' ');
    }

    public function testConfigRejectsInvalidUrls(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Config('k', 'not a url');
    }
}
