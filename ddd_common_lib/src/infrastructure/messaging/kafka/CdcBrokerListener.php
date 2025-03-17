<?php

namespace dddCommonLib\infrastructure\messaging\kafka;

use dddCommonLib\domain\model\common\IMessagingLogger;
use dddCommonLib\domain\model\notification\Notification;
use Exception;

class CdcBrokerListener extends BrokerListener
{
    private AKafkaConsumer $consumer;
    private array $targetProducerList;

    /**
     * @param KafkaProducer[] $targetProducerList
     */
    public function __construct(
        AKafkaConsumer $consumer,
        array $targetProducerList,
        IMessagingLogger $logger
    )
    {
        parent::__construct($logger);
        $this->consumer = $consumer;
        $this->targetProducerList = $targetProducerList;
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
            print_r($cdcData);

            if (!isset($cdcData['payload'])) {
                $this->consumer->commit($message);
                continue;
            }

            $notification = $this->toNotification($cdcData['payload']);
            if ($notification === null) {
                $this->consumer->commit($message);
                continue;
            }

            try {
                foreach ($this->targetProducerList as $producer) {
                    $producer->send($notification);
                }
                $this->consumer->commit($message);
            } catch (Exception $ex) {
                $this->logger->error($ex->getMessage());
            }
        }
    }

    private function toNotification(array $cdcData): ?Notification
    {
        if ($cdcData['after'] === null) {
            return null;
        }

        return Notification::reconstruct(
            $cdcData['after']['event_body'],
            $cdcData['after']['event_id'],
            $cdcData['after']['type_name'],
            $cdcData['after']['occurred_on']
        );
    }
}