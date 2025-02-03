<?php

namespace dddCommonLib\domain\model\domainEvent;

class DomainEventPublisher
{
    private static ?self $instance = null;

    private array $subscriberList = []; // DomainEventSubscriber[]

    private function __construct(){}

    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function subscribe(DomainEventSubscriber $subscriber): void
    {
        $this->addSubscriber($subscriber);
    }

    public function publish(DomainEvent $domainEvent): void
    {
        foreach ($this->subscriberList as $subscriber) {
            if ($subscriber->isSubscribedTo($domainEvent)) {
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