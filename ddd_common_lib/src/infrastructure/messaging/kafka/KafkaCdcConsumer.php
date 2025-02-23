<?php

namespace dddCommonLib\infrastructure\messaging\kafka;

use dddCommonLib\domain\model\common\IMessagingLogger;
use dddCommonLib\domain\model\notification\Notification;
use Exception;

class KafkaCdcConsumer extends KafkaConsumer
{
    private RdKafka\KafkaConsumer $consumer;

    private KafkaProducer $producer;

    private const WAIT_TIME_MS = 50000;

    private const RETRY_DELAY_S = 5;

    public function __construct(
        string $hostName, 
        string $subscribedDbTable, 
        IMessagingLogger $logger,
        KafkaProducer $producer
    )
    {
        parent::__construct($logger);

        $conf = new RdKafka\Conf();
        $conf->set('metadata.broker.list', $hostName);

        $this->consumer = new RdKafka\KafkaConsumer($conf);
        $this->consumer->subscribe([$subscribedDbTable]);

        $this->producer = $producer;
    }

    public function handle(): void
    {
        while (true) {
            $message = $this->consumer->consume(self::WAIT_TIME_MS);
            if ($message->err) {
                $this->errorHandling($message->err);
                continue;
            }

            $cdcData = json_decode($message->payload, true);
            $notification = $this->toNotification($cdcData);

            try {
                $this->producer->send($notification);   
            } catch (Exception $ex) {
                $this->logger->error($ex->getMessage());
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

    private function toNotification(array $cdcData): Notification
    {
        return Notification::reconstruct(
            $cdcData['after']['event_type'],
            $cdcData['after']['occurred_on'],
            $cdcData['after']['event_body'],
            $cdcData['after']['id'],
        );
    }
}