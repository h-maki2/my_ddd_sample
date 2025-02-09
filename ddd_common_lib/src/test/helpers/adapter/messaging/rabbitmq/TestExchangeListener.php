<?php

namespace dddCommonLib\test\helpers\adapter\messaging\rabbitmq;

use dddCommonLib\adapter\messaging\rabbitmq\ConnectionSettings;
use dddCommonLib\adapter\messaging\rabbitmq\ExchangeListener;
use dddCommonLib\domain\model\common\JsonSerializer;
use dddCommonLib\domain\model\notification\Notification;
use dddCommonLib\test\helpers\domain\model\event\TestEvent;

class TestExchangeListener extends ExchangeListener
{
    private array $handledEventList = [];

    public function handledEventList(): array
    {
        return $this->handledEventList;
    }

    protected function exchangeName(): string
    {
        return TestExchangeName::TEST_EXCHANGE_NAME->value;
    }

    protected function queueName(): string
    {
        return self::class;
    }

    protected function connectionSettings(): ConnectionSettings
    {
        return new ConnectionSettings('rabbitmq', 'user', 'password', 5672);
    }

    protected function listensTo(): array
    {
        return [
            TestEvent::class
        ];
    }

    protected function filteredDispatch(): callable
    {
        return function (string $messageBody) {
            $notification = JsonSerializer::deserialize($messageBody, Notification::class);
            $this->addHandledEventList($notification);
        };
    }

    private function addHandledEventList(Notification $notification): void
    {
        $this->handledEventList[] = $notification->notificationType;
    }
}