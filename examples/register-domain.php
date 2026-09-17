<?php

declare(strict_types=1);

use OpusDNS\Client\Enum\PeriodUnit;
use OpusDNS\Client\Enum\RenewalMode;
use OpusDNS\Client\Model\ContactCreate;
use OpusDNS\Client\Model\ContactHandle;
use OpusDNS\Client\Model\DomainCreate;
use OpusDNS\Client\Model\DomainPeriod;
use OpusDNS\Client\Model\Nameserver;

require __DIR__ . '/bootstrap.php';

// Usage: OPUSDNS_API_KEY=... php examples/register-domain.php
// Registers opusdns-php-client-<random>.com or .net for one year on the sandbox with a new contact,
// a DNS zone and the sandbox nameservers.

run(static function (): void {
    $client = sandboxClient();
    $tld = ['com', 'net'][random_int(0, 1)];
    $domainName = sprintf('opusdns-php-client-%s.%s', bin2hex(random_bytes(4)), $tld);

    $check = $client->domain()->eppCheckDomain([$domainName]);
    $result = $check->results[0];
    if (!$result->available) {
        echo "{$domainName} is not available: {$result->reason}\n";
        exit(1);
    }
    echo "{$domainName} is available", $result->isPremium ? ' (premium)' : '', "\n";

    $contact = $client->contact()->createContact(new ContactCreate(
        city: 'Berlin',
        country: 'DE',
        disclose: false,
        email: 'php-client@example.com',
        firstName: 'PHP',
        lastName: 'Client',
        phone: '+49.301234567',
        postalCode: '10115',
        street: 'Unter den Linden 1',
        org: 'OpusDNS PHP client example',
    ));
    echo "Created contact {$contact->contactId}\n";

    $handles = [new ContactHandle((string) $contact->contactId)];
    $domain = $client->domain()->createDomain(new DomainCreate(
        contacts: ['registrant' => $handles, 'admin' => $handles, 'tech' => $handles, 'billing' => $handles],
        name: $domainName,
        period: new DomainPeriod(PeriodUnit::Y, 1),
        renewalMode: RenewalMode::EXPIRE,
        createZone: true,
        nameservers: [new Nameserver('ns1.sandbox.opusdns.com'), new Nameserver('ns2.sandbox.opusdns.com')],
    ));

    echo "Registered {$domain->name}\n";
    echo "  id:          {$domain->domainId}\n";
    echo "  expires on:  ", $domain->expiresOn?->format('Y-m-d'), "\n";
    echo "  renewal:     ", $domain->renewalMode?->value, "\n";
    echo "  nameservers: ", implode(', ', array_map(static fn ($ns) => $ns->hostname, $domain->nameservers ?? [])), "\n";
    echo "  statuses:    ", implode(', ', $domain->registryStatuses ?? []), "\n";
});
