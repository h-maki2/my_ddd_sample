<?php

namespace dddCommonLib\infrastructure\messaging\kafka;

use dddCommonLib\domain\model\common\JsonSerializer;
use dddCommonLib\domain\model\notification\Notification;
use Exception;
use RdKafka;
use RuntimeException;

class KafkaProducer
{
    private const QUEUE_BUFFERING_MAX_MS = 10;
    private const MESSAGE_TIMEOUT_MS = 5000;

    private const WAIT_TIME_MS = 1000;

    private const MAX_RETRY_COUNT = 5;

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

        try {
            $this->topic->produce(RD_KAFKA_PARTITION_UA, 0, $serializedNotification);
            $this->producer->poll(0);
        } catch (Exception $e) {
            throw new RuntimeException("メッセージの送信中に例外発生: " . $e->getMessage());
        }

        $currentRetryCount = 0;
        while ($currentRetryCount < self::MAX_RETRY_COUNT) {
            $result = $this->producer->flush(self::WAIT_TIME_MS);
            if ($result === RD_KAFKA_RESP_ERR_NO_ERROR) {
                // 送信エラーがない場合
                break;
            }
    
            $currentRetryCount++;
        }
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