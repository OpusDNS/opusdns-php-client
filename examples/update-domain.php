<?php

declare(strict_types=1);

use OpusDNS\Client\Enum\RenewalMode;
use OpusDNS\Client\Model\DomainUpdate;
use OpusDNS\Client\Model\Nameserver;

require __DIR__ . '/bootstrap.php';

// Usage: OPUSDNS_API_KEY=... php examples/update-domain.php <domain>
// Switches the domain to automatic renewal and attaches the sandbox nameservers.

run(static function (): void {
    $client = sandboxClient();
    $domainName = argument(1, 'domain');

    $before = $client->domain()->getDomain($domainName);
    echo "{$before->name}\n";
    echo "  renewal:     ", $before->renewalMode?->value, "\n";
    echo "  nameservers: ", implode(', ', array_map(static fn ($ns) => $ns->hostname, $before->nameservers ?? [])), "\n";

    $after = $client->domain()->updateDomain($before->name, new DomainUpdate(
        renewalMode: RenewalMode::RENEW,
        nameservers: [new Nameserver('ns1.sandbox.opusdns.com'), new Nameserver('ns2.sandbox.opusdns.com')],
    ));

    echo "Updated {$after->name}\n";
    echo "  renewal:     ", $after->renewalMode?->value, "\n";
    echo "  nameservers: ", implode(', ', array_map(static fn ($ns) => $ns->hostname, $after->nameservers ?? [])), "\n";
});
