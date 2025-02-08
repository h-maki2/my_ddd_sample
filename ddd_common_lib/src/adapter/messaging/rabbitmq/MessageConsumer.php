<?php

namespace dddCommonLib\adapter\messaging\rabbitmq;

use dddCommonLib\domain\model\common\JsonSerializer;
use dddCommonLib\domain\model\notification\Notification;
use Exception;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Message\AMQPMessage;

class MessageConsumer
{
    private RabbitMqQueue $queue;
    private string $exchangeName;
    private array $messageTypeList;

    private const WAIT_SECONDS = 5;
    private const SLEEP_SECONDS = 5;

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
                $this->channel()->wait(null, false, self::WAIT_SECONDS); // 5秒間メッセージを待つ
            } catch (AMQPTimeoutException $e) {
                sleep(self::SLEEP_SECONDS);
            }
        }
    }

    private function handle(
        callable $filteredDispatch
    ): callable
    {
        $channel = $this->channel();
        return function (AMQPMessage $message) use ($filteredDispatch, $channel) {
           $reconstructedMessage = RabbitMqMessage::reconstruct($message);
           $notification = $reconstructedMessage->toNotification();
           if ($this->filteredMessageType($notification)) {
               return;
           }

           try {
                $filteredDispatch($notification);
                $channel->basic_ack($reconstructedMessage->deliveryTag());
           } catch (Exception $e) {
                $retrievedMessage = $reconstructedMessage->retrieve();
                if ($retrievedMessage->hasReachedMaxRetryCount()) {
                    $channel->basic_nack($reconstructedMessage->deliveryTag(), false, false);
                } else {
                    $channel->basic_publish($retrievedMessage, '', $this->queueName());
                    $channel->basic_ack($reconstructedMessage->deliveryTag());
                }
           }
        };
    }

    private function filteredMessageType(Notification $notification): bool
    {
        if ($this->messageTypeList === []) {
            return false;
        }

        return !in_array($notification->notificationType, $this->messageTypeList);
    }
}