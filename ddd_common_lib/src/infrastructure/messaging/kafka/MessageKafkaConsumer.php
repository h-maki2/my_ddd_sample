<?php

namespace dddCommonLib\infrastructure\messaging\kafka;

use RdKafka;

class MessageKafkaConsumer extends AKafkaConsumer
{
    public function __construct(
        string $groupId,
        string $hostName,
        array $topicNameList,
        KafkaEnableAuthCommit $enableAuthCommit = KafkaEnableAuthCommit::Enable,
        KafkaAutoOffsetReset $autoOffsetReset = KafkaAutoOffsetReset::EARLIEST
    )
    {
        parent::__construct(
            new RdKafka\KafkaConsumer(
                $this->rdkafkaConf($groupId, $hostName, $enableAuthCommit, $autoOffsetReset)
            )
        );
        $this->consumer->subscribe($topicNameList);
    }

    public function consume(): RdKafka\Message
    {
        return $this->consumer->consume($this->waitTimeMs());
    }

    public function commit(RdKafka\Message $message): void
    {
        $this->consumer->commit($message);
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