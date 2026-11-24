<?php

/**
 * This file is generated from the OpenAPI specification by bin/generate.
 * Do not edit it by hand; regenerate it instead.
 */

declare(strict_types=1);

namespace OpusDNS\Client\Enum;

enum DomainPreRegistrationNeedsAction: string
{
    case SIGNED_MARK_REQUIRED = 'signed_mark_required';
    case CONFIRMATION_REQUIRED = 'confirmation_required';
}
