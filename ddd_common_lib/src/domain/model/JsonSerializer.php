<?php

namespace dddCommonLib\domain\model;

use DateTime;
use DateTimeImmutable;
use ReflectionClass;
use ReflectionProperty;

abstract class JsonSerializer
{
    public function serialize(): string
    {
        $targetList = [];
        $reflection = new ReflectionClass(static::class);
        $properties = $reflection->getProperties();

        $targetList = [];
        foreach ($properties as $property) {
            $property->setAccessible(true);
            $value = static::getValueFromProperty($property);

            if ($value instanceof DateTimeImmutable) {
                $targetList[$property->getName()] = $value->format(DateTime::ATOM);
            } else {
                $targetList[$property->getName()] = $value;
            }
        }

        return json_encode($targetList, JSON_THROW_ON_ERROR);
    }

    abstract protected function getValueFromProperty(ReflectionProperty $property): mixed;
}