<?php

/**
 * This file is generated from the OpenAPI specification by bin/generate.
 * Do not edit it by hand; regenerate it instead.
 */

declare(strict_types=1);

namespace OpusDNS\Client\Model;

use OpusDNS\Client\ApiModel;
use OpusDNS\Client\Enum\EventObjectType;
use OpusDNS\Client\Enum\EventSubtype;
use OpusDNS\Client\Enum\EventType;
use OpusDNS\Client\Serializer;

final readonly class EventResponse implements ApiModel
{
    /**
     * @param \DateTimeImmutable $createdOn When the event was created
     * @param \DateTimeImmutable|null $acknowledgedOn When the event was acknowledged
     * @param string|null $eventId TypeID prefix: event.
     * @param string|null $objectId The id of the object that the event is about
     * @param EventObjectType $objectType The type of object that the event is about
     * @param EventSubtype|null $subtype The specific type/result of operation (considering the type property), more
     *     detailed (e.g., 'NOTIFICATION' with the 'DOMAIN_MODIFICATION' class)
     * @param EventType|null $type The type of the event - indicates the kind of operation occurring (e.g.,
     *     'ACCOUNT_CREATE', 'DOMAIN_MODIFICATION')
     */
    public function __construct(
        public \DateTimeImmutable $createdOn,
        public EventData $eventData,
        public ?\DateTimeImmutable $acknowledgedOn = null,
        public ?string $eventId = null,
        public ?string $objectId = null,
        public EventObjectType $objectType = EventObjectType::RAW,
        public ?EventSubtype $subtype = null,
        public ?EventType $type = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): static
    {
        return new self(
            createdOn: new \DateTimeImmutable($data['created_on']),
            eventData: EventData::fromArray($data['event_data']),
            acknowledgedOn: isset($data['acknowledged_on']) ? new \DateTimeImmutable($data['acknowledged_on']) : null,
            eventId: $data['event_id'] ?? null,
            objectId: $data['object_id'] ?? null,
            objectType: isset($data['object_type']) ? EventObjectType::from($data['object_type']) : EventObjectType::RAW,
            subtype: isset($data['subtype']) ? EventSubtype::from($data['subtype']) : null,
            type: isset($data['type']) ? EventType::from($data['type']) : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return Serializer::normalize([
            'created_on' => $this->createdOn,
            'event_data' => $this->eventData,
            'acknowledged_on' => $this->acknowledgedOn,
            'event_id' => $this->eventId,
            'object_id' => $this->objectId,
            'object_type' => $this->objectType,
            'subtype' => $this->subtype,
            'type' => $this->type,
        ]);
    }
}
