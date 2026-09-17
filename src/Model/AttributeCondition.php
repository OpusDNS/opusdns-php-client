<?php

/**
 * This file is generated from the OpenAPI specification by bin/generate.
 * Do not edit it by hand; regenerate it instead.
 */

declare(strict_types=1);

namespace OpusDNS\Client\Model;

use OpusDNS\Client\ApiModel;
use OpusDNS\Client\Enum\ConditionOperator;
use OpusDNS\Client\Enum\RegistryHandleAttributeType;
use OpusDNS\Client\Serializer;

final readonly class AttributeCondition implements ApiModel
{
    /**
     * @param RegistryHandleAttributeType $field The attribute key to evaluate
     * @param ConditionOperator $operator The comparison operator
     * @param string|list<string> $value The value(s) to compare against
     */
    public function __construct(
        public RegistryHandleAttributeType $field,
        public ConditionOperator $operator,
        public string|array $value,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): static
    {
        return new self(
            field: RegistryHandleAttributeType::from($data['field']),
            operator: ConditionOperator::from($data['operator']),
            value: $data['value'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return Serializer::normalize([
            'field' => $this->field,
            'operator' => $this->operator,
            'value' => $this->value,
        ]);
    }
}
