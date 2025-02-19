<?php

namespace dddCommonLib\infrastructure\messaging\rabbitmq;

use dddCommonLib\domain\model\notification\Notification;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Exception\AMQPTimeoutException;

abstract class ACousumer
{
    protected RabbitMqQueue $queue;
    protected string $exchangeName;
    protected array $messageTypeList;

    public function __construct(
        RabbitMqQueue $queue,
        string $exchangeName,
        array $messageTypeList,
        callable $filteredDispatch
    )
    {
        $this->queue = $queue;
        $this->exchangeName = $exchangeName;
        $this->messageTypeList = $messageTypeList;

        $this->queue->consume(
            $this->handle($filteredDispatch)
        );
    }

    public function close(): void
    {
        $this->queue->close();
    }

    public function listen(): void
    {
        $this->queue->run($this->waitSeconds(), $this->sleepSeconds());
    }

    abstract protected function handle(callable $filteredDispatch): callable;

    abstract protected function sleepSeconds(): int;

    abstract protected function waitSeconds(): int;


    protected function filteredMessageType(Notification $notification): bool
    {
        if ($this->messageTypeList === []) {
            return false;
        }

        return !in_array($notification->notificationType, $this->messageTypeList);
    }
}