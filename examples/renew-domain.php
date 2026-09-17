<?php

declare(strict_types=1);

use OpusDNS\Client\Enum\PeriodUnit;
use OpusDNS\Client\Model\DomainPeriod;
use OpusDNS\Client\Model\DomainRenewRequest;

require __DIR__ . '/bootstrap.php';

// Usage: OPUSDNS_API_KEY=... php examples/renew-domain.php <domain>
// Renews the domain for one year, using its current expiry date as the API requires.

run(static function (): void {
    $client = sandboxClient();
    $domainName = argument(1, 'domain');

    $domain = $client->domain()->getDomain($domainName);
    if ($domain->expiresOn === null) {
        echo "{$domain->name} has no expiry date and cannot be renewed.\n";
        exit(1);
    }
    echo "{$domain->name} currently expires on ", $domain->expiresOn->format('Y-m-d'), "\n";

    $renewal = $client->domain()->renewDomain($domain->name, new DomainRenewRequest(
        currentExpiryDate: $domain->expiresOn,
        period: new DomainPeriod(PeriodUnit::Y, 1),
    ));

    echo "Renewed {$renewal->name} by {$renewal->periodExtended->value}{$renewal->periodExtended->unit->value}\n";
    echo "  new expiry:  ", $renewal->newExpiryDate->format('Y-m-d'), "\n";
});
