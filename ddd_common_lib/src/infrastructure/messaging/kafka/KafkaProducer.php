<?php

namespace dddCommonLib\infrastructure\messaging\kafka;

class KafkaProducer
{
    private const QUEUE_BUFFERING_MAX_MS = 10;
    private const MESSAGE_TIMEOUT_MS = 5000;

    private RdKafka\ProducerTopic $topic;
    private RdKafka\Producer $producer;

    public function __construct(string $hostName, string $topicName, Acks $acks = Acks::ALL)
    {
        $this->producer = new RdKafka\Producer(
            $this->rdKafkaConf($hostName, $acks)
        );
        $this->topic = $this->producer->newTopic($topicName);
    }

    public function send(string $message): void
    {
        $this->topic->produce(RD_KAFKA_PARTITION_UA, 0, $message);
        $this->producer->flush(10000);
    }

    private function rdKafkaConf(string $hostName, Acks $acks): RdKafka\Conf
    {
        $conf = new RdKafka\Conf();
        $conf->set('metadata.broker.list', $hostName);
        $conf->set('acks', $acks->value);
        $conf->set('queue.buffering.max.ms', self::QUEUE_BUFFERING_MAX_MS);
        $conf->set('message.timeout.ms', self::MESSAGE_TIMEOUT_MS);
        return $conf;
    }
}