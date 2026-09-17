<?php

declare(strict_types=1);

namespace OpusDNS\Client\Tests\Support;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * PSR-18 client that records requests and answers from a queue.
 */
final class FakeHttpClient implements ClientInterface
{
    /** @var list<RequestInterface> */
    public array $requests = [];

    /** @var list<ResponseInterface|\Throwable> */
    private array $queue = [];

    public function queue(ResponseInterface|\Throwable $next): self
    {
        $this->queue[] = $next;

        return $this;
    }

    /** @param array<string, string> $headers */
    public function queueJson(int $status, mixed $body, array $headers = []): self
    {
        $encoded = $body === null ? '' : json_encode($body, JSON_THROW_ON_ERROR);

        return $this->queue(new Response($status, $headers + ['Content-Type' => 'application/json'], $encoded));
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->requests[] = $request;
        $next = array_shift($this->queue) ?? new Response(200, ['Content-Type' => 'application/json'], '{}');
        if ($next instanceof \Throwable) {
            throw $next;
        }

        return $next;
    }

    public function lastRequest(): RequestInterface
    {
        return $this->requests[array_key_last($this->requests) ?? throw new \LogicException('No request was sent.')];
    }
}
