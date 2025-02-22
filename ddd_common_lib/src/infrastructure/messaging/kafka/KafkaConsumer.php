<?php

namespace dddCommonLib\infrastructure\messaging\kafka;

use dddCommonLib\domain\model\common\IMessagingLogger;
use dddCommonLib\domain\model\common\JsonSerializer;
use dddCommonLib\domain\model\notification\Notification;
use Exception;
use RdKafka;

abstract class KafkaConsumer
{
    private RdKafka\KafkaConsumer $consumer;

    private IMessagingLogger $logger;

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
        KafkaAutoOffsetReset $autoOffsetReset = KafkaAutoOffsetReset::EARLIEST,
        bool $testable = false
    )
    {
        $this->logger = $logger;

        $this->consumer = new RdKafka\KafkaConsumer(
            $this->rdkafkaConf($groupId, $hostName, $enableAuthCommit, $autoOffsetReset)
        );
        $this->consumer->subscribe([$topicName]);

        $this->testable = $testable;
    }

    public function handle(): void
    {
        while (true) {
            $message = $this->consumer->consume(self::WAIT_TIME_MS);
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
                if ($this->testable) {
                    throw new $ex;
                }

                $currentReturyCount++;

                if ($currentReturyCount >= self::MAX_RETYR_COUNT) {
                    $this->logger->error($ex->getMessage() . ' notificationId: ' . $notification->notificationId);
                    break;
                }

                sleep(self::RETRY_DELAY_S);
            }
        }
    }

    private function errorHandling(int $messageError): void
    {
        switch ($messageError) {
            case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                // メッセージがまだ来ていない（正常）
                break;
            case RD_KAFKA_RESP_ERR__TIMED_OUT:
                // タイムアウト（正常）
                break;
            default:
                $this->logger->error("Kafka Consumer Error: " . rd_kafka_err2str($messageError));
                sleep(self::RETRY_DELAY_S);
                break;
        }
    }
}