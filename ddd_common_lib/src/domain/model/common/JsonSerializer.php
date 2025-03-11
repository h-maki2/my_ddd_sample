<?php

namespace dddCommonLib\domain\model\common;

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
            $targetList[$property->getName()] = $value;
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

            $prop->setValue($instance, $value);
        }

        return $instance;
    }

    public static function deserializeToArray(string $jsonString): array
    {
        return json_decode($jsonString, true, 512, JSON_THROW_ON_ERROR);
    }
}