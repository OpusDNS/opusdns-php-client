<?php

/**
 * This file is generated from the OpenAPI specification by bin/generate.
 * Do not edit it by hand; regenerate it instead.
 */

declare(strict_types=1);

namespace OpusDNS\Client\Enum;

/**
 * What a breakdown groups the redirects by.
 */
enum DomainForwardStatisticsDimension: string
{
    case BROWSER = 'browser';
    case PLATFORM = 'platform';
    case USER_AGENT = 'user_agent';
    case REFERRER = 'referrer';
    case COUNTRY = 'country';
    case STATUS_CODE = 'status_code';
    case URL = 'url';
    case FQDN = 'fqdn';
    case DOMAIN = 'domain';
    case FORWARD = 'forward';
    case RULE = 'rule';
}
