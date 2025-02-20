<?php

namespace dddCommonLib\infrastructure\messaging\kafka;

use RdKafka;

class KafkaConsumer
{
    private RdKafka\KafkaConsumer $consumer;

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

    public function handle(callable $filteredDispatch): void
    {
        while (true) {
            $message = $this->consumer->consume(10000);
            $filteredDispatch($message->payload);
        }
    }

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
}