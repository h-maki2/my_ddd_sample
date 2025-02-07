<?php

namespace dddCommonLib\adapter\messaging\rabbitmq;

use Exception;
use PhpAmqpLib\Message\AMQPMessage;

class MessageConsumer
{
    private Exchange $exchange;

    public function __construct(
        Exchange $exchange,
    )
    {
        $this->exchange = $exchange;
    }

    private function callBack(
        $dispachProcessing
    ): callable
    {
        $channel = $this->exchange->channel;
        return function (AMQPMessage $message) use ($dispachProcessing, $channel) {
           $reconstructedMessage = RabbitMqMessage::reconstruct($message);

           try {
                $dispachProcessing($reconstructedMessage);
           } catch (Exception $e) {
               
           }

           if ($reconstructedMessage->hasReachedMaxRetryCount()) {
              
           }
        };
    }
}