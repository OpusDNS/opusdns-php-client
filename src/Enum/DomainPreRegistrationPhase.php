<?php

/**
 * This file is generated from the OpenAPI specification by bin/generate.
 * Do not edit it by hand; regenerate it instead.
 */

declare(strict_types=1);

namespace OpusDNS\Client\Enum;

/**
 * A spec's launch phase types, plus general availability, which a spec keeps as its own block.
 */
enum DomainPreRegistrationPhase: string
{
    case SUNRISE = 'sunrise';
    case LANDRUSH = 'landrush';
    case EAP = 'eap';
    case LIMITED_REGISTRATION = 'limited_registration';
    case GENERAL_AVAILABILITY = 'general_availability';
}
