<?php

namespace dddCommonLib\infrastructure\messaging\kafka;

class KafkaConsumer
{
    private RdKafka\KafkaConsumer $consumer;

    public function __construct(
        string $groupId,
        string $hostName,
        string $topicName,
        KafkaEnableAuthCommit $enableAuthCommit = KafkaEnableAuthCommit::Disable,
        KafkaAutoOffsetReset $autoOffsetReset = KafkaAutoOffsetReset::LATEST
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
            $this->consumer->commitAsync($message);
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