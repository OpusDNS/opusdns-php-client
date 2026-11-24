<?php

/**
 * This file is generated from the OpenAPI specification by bin/generate.
 * Do not edit it by hand; regenerate it instead.
 */

declare(strict_types=1);

namespace OpusDNS\Client\Enum;

enum DomainPreRegistrationPublicStage: string
{
    case REQUESTED = 'requested';
    case NEEDS_ACTION = 'needs_action';
    case HELD = 'held';
    case SUBMITTING = 'submitting';
    case REGISTERED = 'registered';
    case FAILED = 'failed';
    case CANCELED = 'canceled';
}
