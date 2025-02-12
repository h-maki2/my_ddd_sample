<?php

namespace dddCommonLib\adapter\messaging\rabbitmq;

use Exception;
use PhpAmqpLib\Message\AMQPMessage;

class MessageConsumer extends ACousumer
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
        parent::__construct($queue, $exchangeName, $messageTypeList, $filteredDispatch);
    }

    protected function handle(
        callable $filteredDispatch
    ): callable
    {
        return function (AMQPMessage $message) use ($filteredDispatch) {
           $reconstructedMessage = RabbitMqMessage::reconstruct($message);
           $notification = $reconstructedMessage->toNotification();
           if ($this->filteredMessageType($notification)) {
               return;
           }

           try {
                $filteredDispatch($reconstructedMessage->messageBody());
                $this->queue->ack($reconstructedMessage->deliveryTag());
           } catch (Exception $e) {
                $retrievedMessage = $reconstructedMessage->retrieve();
                if ($retrievedMessage->hasReachedMaxRetryCount()) {
                    $this->queue->nack($reconstructedMessage->deliveryTag());
                } else {
                    $this->queue->requeueMessage($retrievedMessage, '', $this->queueName());
                    $this->queue->ack($reconstructedMessage->deliveryTag());
                }
           }
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