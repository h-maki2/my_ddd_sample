<?php

namespace dddCommonLib\infrastructure\messaging\kafka;

use dddCommonLib\domain\model\common\JsonSerializer;
use dddCommonLib\domain\model\notification\Notification;
use Exception;
use RdKafka;

abstract class KafkaConsumer
{
    private RdKafka\KafkaConsumer $consumer;

    private const WAIT_TIME_MS = 3000;

    public function __construct(
        string $groupId,
        string $hostName,
        string $topicName,
        KafkaEnableAuthCommit $enableAuthCommit = KafkaEnableAuthCommit::Enable,
        KafkaAutoOffsetReset $autoOffsetReset = KafkaAutoOffsetReset::EARLIEST
    )
    {
        $this->consumer = new RdKafka\KafkaConsumer(
            $this->rdkafkaConf($groupId, $hostName, $enableAuthCommit, $autoOffsetReset)
        );
        $this->consumer->subscribe([$topicName]);
    }

    public function handle(): void
    {
        while (true) {
            $message = $this->consumer->consume(self::WAIT_TIME_MS);
            if ($message->err) {
                continue;
            }

            $notification = JsonSerializer::deserialize($message->payload, Notification::class);
            if ($this->filteredMessageType($notification)) {
                continue;
            }
            $this->filteredDispatch($notification);
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
}