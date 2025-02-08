<?php

namespace dddCommonLib\domain\model\domainEvent;

interface DomainEventSubscriber
{
    public function handleEvent(DomainEvent $aDomainEvent): void;

    public function subscribedToEventType(): string;
}