<?php

namespace dddCommonLib\infrastructure\messaging\kafka;

class CdcKafkaConsumer extends AKafkaConsumer
{
    private const WAIT_TIME_MS = 10000;

    public function __construct(
        string $hostName, 
        array $subscribedDbTable,
        KafkaEnableAuthCommit $enableAuthCommit = KafkaEnableAuthCommit::Enable,
    )
    {
        $conf = new RdKafka\Conf();
        $conf->set('metadata.broker.list', $hostName);
        $conf->set('enable.auto.commit', $enableAuthCommit->value);

        parent::__construct(
            new RdKafka\BrokerListener($conf)
        );
        $this->consumer->subscribe($subscribedDbTable);
    }

    public function consume(): RdKafka\Message
    {
        return $this->consumer->consume($this->waitTimeMs());
    }

    public function commit(RdKafka\Message $message): void
    {
        $this->consumer->commit($message);
    }

    protected function waitTimeMs(): int
    {
        return self::WAIT_TIME_MS;
    }
}