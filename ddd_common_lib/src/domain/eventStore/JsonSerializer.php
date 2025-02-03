<?php

namespace dddCommonLib\domain\eventStore;

use DateTime;
use DateTimeImmutable;
use ReflectionClass;

class JsonSerializer
{
    public static function serialize(object $serializeObject): string
    {
        $targetList = [];
        $reflection = new ReflectionClass($serializeObject);
        $properties = $reflection->getProperties();

        $targetList = [];
        foreach ($properties as $property) {
            $property->setAccessible(true);
            $value = $property->getValue($serializeObject);

            if ($value instanceof DateTimeImmutable) {
                $targetList[$property->getName()] = $value->format(DateTime::ATOM);
            } else {
                $targetList[$property->getName()] = $value;
            }
        }

        return json_encode($targetList, JSON_THROW_ON_ERROR);
    }

    public static function deserialize(string $eventBody, string $eventType): object
    {
        $eventDataArray = json_decode($eventBody, true, 512, JSON_THROW_ON_ERROR);
        $reflection = new ReflectionClass($eventType);
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
}