<?php

namespace dddCommonLib\domain\eventStore;

use dddCommonLib\domain\model\DomainEvent;

class EventSerializer
{
    public function serialize(DomainEvent $aDomainEvent): string
    {
        return json_encode($aDomainEvent, JSON_THROW_ON_ERROR);
    }

    public function deserialize(string $serializedDomainEvent): DomainEvent
    {
        $decoded = json_decode($serializedDomainEvent, true, 512, JSON_THROW_ON_ERROR);
        return $decoded;
    }
}