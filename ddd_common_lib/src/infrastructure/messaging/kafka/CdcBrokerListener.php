<?php

namespace dddCommonLib\infrastructure\messaging\kafka;

use dddCommonLib\domain\model\common\IMessagingLogger;
use dddCommonLib\domain\model\notification\Notification;
use Exception;

class CdcBrokerListener extends BrokerListener
{
    private AKafkaConsumer $consumer;
    private KafkaProducer $producer;

    public function __construct(
        AKafkaConsumer $consumer,
        KafkaProducer $producer,
        IMessagingLogger $logger
    )
    {
        parent::__construct($logger);
        $this->consumer = $consumer;
        $this->producer = $producer;
    }

    public function handle(): void
    {
        while (true) {
            $message = $this->consumer->consume();
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