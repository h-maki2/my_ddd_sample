<?php

namespace dddCommonLib\infrastructure\messaging\kafka;

use dddCommonLib\domain\model\common\JsonSerializer;
use dddCommonLib\domain\model\notification\Notification;
use RdKafka;

class KafkaProducer
{
    private const QUEUE_BUFFERING_MAX_MS = 10;
    private const MESSAGE_TIMEOUT_MS = 5000;

    private RdKafka\ProducerTopic $topic;
    private RdKafka\Producer $producer;

    public function __construct(string $hostName, string $topicName, KafkaAcks $acks = KafkaAcks::ALL)
    {
        $this->producer = new RdKafka\Producer(
            $this->rdKafkaConf($hostName, $acks)
        );
        $this->topic = $this->producer->newTopic($topicName);
    }

    public function send(Notification $notification): void
    {
        $serializedNotification = JsonSerializer::serialize($notification);
        $this->topic->produce(RD_KAFKA_PARTITION_UA, 0, $serializedNotification);
        $this->producer->flush(10000);
    }

    private function rdKafkaConf(string $hostName, KafkaAcks $acks): RdKafka\Conf
    {
        $conf = new RdKafka\Conf();
        $conf->set('metadata.broker.list', $hostName);
        $conf->set('acks', $acks->value);
        $conf->set('queue.buffering.max.ms', self::QUEUE_BUFFERING_MAX_MS);
        $conf->set('message.timeout.ms', self::MESSAGE_TIMEOUT_MS);
        return $conf;
    }
}