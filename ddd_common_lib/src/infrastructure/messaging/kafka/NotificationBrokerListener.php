<?php

namespace dddCommonLib\infrastructure\messaging\kafka;

use dddCommonLib\domain\model\common\IMessagingLogger;
use dddCommonLib\domain\model\common\JsonSerializer;
use dddCommonLib\domain\model\notification\Notification;
use Exception;

abstract class NotificationBrokerListener extends BrokerListener
{
    private AKafkaConsumer $consumer;

    private const MAX_RETYR_COUNT = 3;
    private const RETRY_DELAY_S = 5;

    private bool $testable;

    public function __construct(
        AKafkaConsumer $consumer,
        IMessagingLogger $logger,
        bool $testable = false
    )
    {
        parent::__construct($logger);

        $this->consumer = $consumer;

        $this->testable = $testable;
    }

    public function handle(): void
    {
        while (true) {
            $message = $this->consumer->consume();
            if ($message->err) {
                $this->errorHandling($message->err);
                continue;
            }

            $notification = JsonSerializer::deserialize($message->payload, Notification::class);
            if ($this->filteredMessageType($notification)) {
                $this->consumer->commit($message);
                continue;
            }

            $this->proccessNotification($notification);
            $this->consumer->commit($message);
        }
    }

    abstract protected function filteredDispatch(Notification $notification): void;

    abstract protected function listensTo(): array;

    private function filteredMessageType(Notification $notification): bool
    {
        if ($this->listensTo() === []) {
            return false;
        }

        return !in_array($notification->notificationType, $this->listensTo());
    }

    private function proccessNotification(Notification $notification): void
    {
        $currentRetryCount = 0;
        while ($currentRetryCount < self::MAX_RETYR_COUNT) {
            try {
                $this->filteredDispatch($notification);
                break;
            } catch (Exception $ex) {
                if ($this->testable) {
                    // テスト時は例外をそのままスロー
                    throw $ex;
                }

                $currentRetryCount++;

                if ($currentRetryCount >= self::MAX_RETYR_COUNT) {
                    $this->logger->error($ex->getMessage() . ' notificationId: ' . $notification->notificationId);
                    break;
                }

                sleep(self::RETRY_DELAY_S);
            }
        }
    }
}