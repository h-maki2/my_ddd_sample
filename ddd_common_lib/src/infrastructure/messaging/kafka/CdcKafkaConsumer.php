<?php

namespace dddCommonLib\infrastructure\messaging\kafka;

use RdKafka;

class CdcKafkaConsumer extends AKafkaConsumer
{
    public function __construct(
        string $hostName, 
        array $topicNameList,
        string $groupId,
        KafkaEnableAuthCommit $enableAuthCommit = KafkaEnableAuthCommit::Disable,
    )
    {
        $conf = new RdKafka\Conf();
        $conf->set('metadata.broker.list', $hostName);
        $conf->set('group.id', $groupId);
        $conf->set('enable.auto.commit', $enableAuthCommit->value);

        parent::__construct(
            new RdKafka\KafkaConsumer($conf)
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
}