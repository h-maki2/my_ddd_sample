<?php

namespace dddCommonLib\infrastructure\messaging\kafka;

use dddCommonLib\domain\model\common\IMessagingLogger;
use dddCommonLib\domain\model\common\JsonSerializer;
use dddCommonLib\domain\model\notification\Notification;
use Exception;
use RdKafka;

abstract class NotificationBrokerListener extends BrokerListener
{
    private RdKafka\BrokerListener $consumer;

    private bool $testable;

    private const WAIT_TIME_MS = 10000;

    private const MAX_RETYR_COUNT = 3;
    private const RETRY_DELAY_S = 5;

    public function __construct(
        string $groupId,
        string $hostName,
        string $topicName,
        IMessagingLogger $logger,
        KafkaEnableAuthCommit $enableAuthCommit = KafkaEnableAuthCommit::Enable,
        KafkaAutoOffsetReset $autoOffsetReset = KafkaAutoOffsetReset::EARLIEST
    )
    {
        parent::__construct($logger);

        $this->consumer = new RdKafka\BrokerListener(
            $this->rdkafkaConf($groupId, $hostName, $enableAuthCommit, $autoOffsetReset)
        );
        $this->consumer->subscribe([$topicName]);
    }

    public function handle(): void
    {
        while (true) {
            $message = $this->consumer->consume($this->waitTimeMs());
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

    private function rdkafkaConf(
        string $groupId, 
        string $hostName,
        KafkaEnableAuthCommit $enableAuthCommit,
        KafkaAutoOffsetReset $autoOffsetReset
    ): RdKafka\Conf
    {
        $conf = new RdKafka\Conf();
        $conf->set('group.id', $groupId);
        $conf->set('metadata.broker.list', $hostName);
        $conf->set('enable.auto.commit', $enableAuthCommit->value);
        $conf->set('auto.offset.reset', $autoOffsetReset->value);
        return $conf;
    }

    private function filteredMessageType(Notification $notification): bool
    {
        if ($this->listensTo() === []) {
            return false;
        }

        return !in_array($notification->notificationType, $this->listensTo());
    }

    private function proccessNotification(Notification $notification): void
    {
        $currentReturyCount = 0;
        while ($currentReturyCount < self::MAX_RETYR_COUNT) {
            try {
                $this->filteredDispatch($notification);
                break;
            } catch (Exception $ex) {
                $currentReturyCount++;

                if ($currentReturyCount >= self::MAX_RETYR_COUNT) {
                    $this->logger->error($ex->getMessage() . ' notificationId: ' . $notification->notificationId);
                    break;
                }

                sleep($this->retryDelayS());
            }
        }
    }

    protected function waitTimeMs(): int
    {
        return self::WAIT_TIME_MS;
    }

    protected function retryDelayS(): int
    {
        return self::RETRY_DELAY_S;
    }
}