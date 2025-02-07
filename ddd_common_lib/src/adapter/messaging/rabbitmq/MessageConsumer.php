<?php

namespace dddCommonLib\adapter\messaging\rabbitmq;

use dddCommonLib\domain\model\common\JsonSerializer;
use dddCommonLib\domain\model\notification\Notification;
use Exception;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

class MessageConsumer
{
    private RabbitMqQueue $queue;
    private string $exchangeName;
    private array $messageTypeList;

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

    public function close(): void
    {
        $this->queue->close();
    }

    private function handle(
        callable $filteredDispatch
    ): callable
    {
        $channel = $this->queue->channel;
        return function (AMQPMessage $message) use ($filteredDispatch, $channel) {
           $reconstructedMessage = RabbitMqMessage::reconstruct($message);
           $notification = $this->notificationFrom($reconstructedMessage);
           if (!$this->filteredMessageType($notification)) {
               return;
           }

           try {
                $filteredDispatch($notification);
                $channel->basic_ack($reconstructedMessage->deliveryTag());
           } catch (Exception $e) {
                if ($reconstructedMessage->hasReachedMaxRetryCount()) {
                    
                }
           }
        };
    }

    private function notificationFrom(RabbitMqMessage $message): Notification
    {
        return JsonSerializer::deserialize($message->messageBody(), Notification::class);
    }

    private function filteredMessageType(Notification $notification): bool
    {
        if ($this->messageTypeList === []) {
            return true;
        }

        return in_array($notification->notificationType, $this->messageTypeList);
    }
}