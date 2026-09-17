<?php

declare(strict_types=1);

namespace OpusDNS\Client\Exception;

/**
 * 429 Too Many Requests.
 */
final class RateLimitException extends HttpException
{
}
