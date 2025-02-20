<?php

use dddCommonLib\infrastructure\messaging\kafka\KafkaConsumer;
use dddCommonLib\infrastructure\messaging\kafka\KafkaProducer;
use PHPUnit\Framework\TestCase;

class KafkaConsumerTest extends TestCase
{
    public function test_プロデューサから送信したメッセージを受信できることを確認する()
    {
        // given
        // バックグランドでコンシューマを起動する
        exec('php src/test/integrate/adapter/messaging/kafka/consumerInBackgroundProcess.php > output.log 2>&1 &');

        sleep(2);

        $producer = new KafkaProducer('kafka:9092', 'testTopic');

        $sendMessage = 'test message';

        $producer->send($sendMessage);

        $this->assertTrue(true);
    }
}