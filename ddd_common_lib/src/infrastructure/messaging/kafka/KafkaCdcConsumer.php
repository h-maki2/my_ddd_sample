<?php

namespace dddCommonLib\infrastructure\messaging\kafka;

use dddCommonLib\domain\model\common\IMessagingLogger;

class KafkaCdcConsumer extends KafkaConsumer
{
    private RdKafka\KafkaConsumer $consumer;

    private const WAIT_TIME_MS = 50000;

    private const RETRY_DELAY_S = 5;

    public function __construct(string $hostName, string $subscribedDbTable, IMessagingLogger $logger)
    {
        parent::__construct($logger);
        $conf = new RdKafka\Conf();
        $conf->set('metadata.broker.list', $hostName);

        $this->consumer = new RdKafka\KafkaConsumer($conf);
        $this->consumer->subscribe([$subscribedDbTable]);
    }

    public function handle(): void
    {
        while (true) {
            $message = $this->consumer->consume(120*1000);
            if ($message->err) {
                echo "Error: {$message->errstr()}\n";
            } else {
                echo "Received: " . $message->payload . "\n";
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