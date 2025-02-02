<?php

namespace dddCommonLib\domain\model;

class DomainEventPublisher
{
    private static ?self $instance = null;

    private array $subscriberList = []; // DomainEventSubscriber[]

    private function __construct()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
    }

    public static function instance(): self
    {
        return new self();
    }

    public function subscribe(DomainEventSubscriber $subscriber): void
    {
        $this->addSubscriber($subscriber);
    }

    public function publish(DomainEvent $domainEvent): void
    {
        foreach ($this->subscriberList as $subscriber) {
            if ($subscriber->subscribedToEventType() === $domainEvent->eventType()) {
                $subscriber->handleEvent($domainEvent);
            }
        }
    }

    public function reset(): void
    {
        $this->subscriberList = [];
    }

    private function addSubscriber(DomainEventSubscriber $subscriber): void
    {
        $this->subscriberList[] = $subscriber;
    }
}