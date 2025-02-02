<?php

namespace dddCommonLib\domain\model;

use DateTime;
use DateTimeImmutable;
use ReflectionClass;
use ReflectionProperty;

class StoredEvent extends JsonSerializer
{
    readonly string $eventBody;
    readonly DateTimeImmutable $occurredOn;
    readonly string $eventType;

    public function __construct(
        string $anEventType, 
        DateTimeImmutable $anOccurredOn, 
        string $anEventBody
    )
    {
        $this->eventType = $anEventType;
        $this->occurredOn = $anOccurredOn;
        $this->eventBody = $anEventBody;
    }

    public static function fromJsonArray(array $jsonArray): StoredEvent
    {
        return new StoredEvent(
            $jsonArray['eventType'],
            new DateTimeImmutable($jsonArray['occurredOn']),
            $jsonArray['eventBody']
        );
    }

    public function toDomainEvent(): DomainEvent
    {
        $eventDataArray = json_decode($this->eventBody, true, 512, JSON_THROW_ON_ERROR);
        $reflection = new ReflectionClass($this->eventType);
        $instance = $reflection->newInstanceWithoutConstructor();

        foreach ($eventDataArray as $property => $value) {
            $prop = $reflection->getProperty($property);
            $prop->setAccessible(true);

            if ($prop->getName() === 'occurredOn') {
                $value = new DateTimeImmutable($value);
            }

            $prop->setValue($instance, $value);
        }

        return $instance;
    }

    protected function getValueFromProperty(ReflectionProperty $property): mixed
    {
        return $property->getValue($this);
    }
}