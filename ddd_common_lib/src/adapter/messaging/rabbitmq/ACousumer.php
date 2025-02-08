<?php

namespace dddCommonLib\adapter\messaging\rabbitmq;

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

        $this->queue->channel->basic_consume(
            $this->queue->queueName, 
            '', 
            false, 
            false, 
            false, 
            false, 
            $this->handle($filteredDispatch)
        );
    }

    public function channel(): AMQPChannel
    {
        return $this->queue->channel;
    }

    public function queueName(): string
    {
        return $this->queue->queueName;
    }

    public function close(): void
    {
        $this->queue->close();
    }

    public function listen(): void
    {
        while ($this->channel()->is_consuming()) {
            try {
                $this->channel()->wait(null, false, $this->waitSeconds()); // 5秒間メッセージを待つ
            } catch (AMQPTimeoutException $e) {
                sleep($this->sleepSeconds());
            }
        }
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