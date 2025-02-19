<?php

namespace dddCommonLib\infrastructure\messaging\rabbitmq;

use PhpAmqpLib\Message\AMQPMessage;

class DlxConsumer extends ACousumer
{
    protected const WAIT_SECONDS = 5;
    protected const SLEEP_SECONDS = 5;

    public function __construct(
        RabbitMqQueue $queue,
        string $exchangeName,
        array $messageTypeList,
        callable $filteredDispatch
    )
    {
        parent::__construct(
            $queue, 
            $exchangeName, 
            $messageTypeList, 
            $filteredDispatch
        );
    }

    protected function handle(
        callable $filteredDispatch
    ): callable
    {
        return function (AMQPMessage $message) use ($filteredDispatch) {
            $rabbitMqMessage = RabbitMqMessage::reconstruct($message);
            $filteredDispatch($rabbitMqMessage->messageBody());
            $this->queue->ack($rabbitMqMessage->deliveryTag()); // 処理完了後にACK
        };
    }

    protected function waitSeconds(): int
    {
        return self::WAIT_SECONDS;
    }

    protected function sleepSeconds(): int
    {
        return self::SLEEP_SECONDS;
    }
}