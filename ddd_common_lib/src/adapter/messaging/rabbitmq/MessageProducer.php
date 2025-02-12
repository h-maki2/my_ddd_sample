<?php

namespace dddCommonLib\adapter\messaging\rabbitmq;

use dddCommonLib\adapter\messaging\rabbitmq\exceptions\NotExistsQueueException;
use dddCommonLib\adapter\messaging\rabbitmq\exceptions\NotSendMessageException;
use Exception;
use RuntimeException;

class MessageProducer
{
    readonly Exchange $exchange;

    private const MAX_RETRY_COUNT = 5;

    public function __construct(Exchange $exchange)
    {
        $this->exchange = $exchange;
    }

    public function send(
        RabbitMqMessage $message,
        string $routingKey = ''
    ): void
    {
        $currentRetryCount = 0;

        while ($currentRetryCount < self::MAX_RETRY_COUNT) {
            try {
                $this->exchange->publish(
                    $message,
                    $routingKey
                );
                break;
            } catch (NotExistsQueueException $e) {
                throw new $e;
            } catch (Exception $e) {
                $currentRetryCount++;

                $waitTime = pow(2, $currentRetryCount);

                if ($currentRetryCount < self::MAX_RETRY_COUNT) {
                    sleep($waitTime);
                } else {
                    throw new RuntimeException($e->getMessage());
                }
            }
        }
    }

    public function close(): void
    {
        $this->exchange->close();
    }
}