<?php

namespace dddCommonLib\test\helpers\adapter\messaging\rabbitmq;

use dddCommonLib\adapter\messaging\rabbitmq\ConnectionSettings;
use dddCommonLib\adapter\messaging\rabbitmq\ExchangeListener;
use dddCommonLib\adapter\messaging\rabbitmq\RabbitMqQueue;
use dddCommonLib\domain\model\common\JsonSerializer;
use dddCommonLib\domain\model\notification\Notification;
use dddCommonLib\test\helpers\domain\model\event\OtherTestEvent;
use dddCommonLib\test\helpers\domain\model\event\TestEvent;

class TestExchangeListener extends ExchangeListener
{
    private array $handledEventList = [];

    public function testHandle(int $handledEventCount): void
    {
        while ($this->queue->isSendingMessageToConsumer() && count($this->handledEventList) < $handledEventCount) {
            $this->queue->wait(30);
        }
    }

    public function queue(): RabbitMqQueue
    {
        return $this->queue;
    }

    public function handledEventList(): array
    {
        return $this->handledEventList;
    }

    public function exchangeName(): string
    {
        return TestExchangeName::TEST_EXCHANGE_NAME->value;
    }

    public function queueName(): string
    {
        return self::class;
    }

    public function connectionSettings(): ConnectionSettings
    {
        return new ConnectionSettings('rabbitmq', 'user', 'password', 5672);
    }

    protected function listensTo(): array
    {
        return [
            TestEvent::class,
            OtherTestEvent::class
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