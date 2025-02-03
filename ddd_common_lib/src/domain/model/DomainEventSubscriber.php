<?php

namespace dddCommonLib\domain\model;

interface DomainEventSubscriber
{
    public function handleEvent(DomainEvent $aDomainEvent): void;

    public function isSubscribedTo(DomainEvent $aDomainEvent): bool;

    public function subscribedToEventType(): string;
}