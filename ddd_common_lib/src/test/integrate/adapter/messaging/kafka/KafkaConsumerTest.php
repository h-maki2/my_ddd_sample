<?php

use dddCommonLib\infrastructure\messaging\kafka\KafkaConsumer;
use dddCommonLib\infrastructure\messaging\kafka\KafkaProducer;
use dddCommonLib\test\helpers\adapter\messaging\kafka\KafkaCatchedTestMessageList;
use dddCommonLib\test\helpers\adapter\messaging\kafka\TestConsumer;
use PHPUnit\Framework\TestCase;

class KafkaConsumerTest extends TestCase
{
    private KafkaProducer $producer;
    private TestConsumer $consumer;
    private string $catchedMessage;
    private $filteredDispatch;

    public function setUp(): void
    {
        $this->producer = new KafkaProducer('kafka:9092', 'testTopic');
        $this->consumer = new TestConsumer(
            'testGroupId',
            'kafka:9092',
            'testTopic'
        );
    }

    public function test_プロデューサから送信したメッセージを受信できることを確認する()
    {
        // given
        $sendMessage = 'test message';

        $this->producer->send($sendMessage);

        // when
        try {
            $this->consumer->handle($this->filteredDispatch);
        } catch (Exception $ex) {
            // 何もしない
        }

        // then
        $this->assertEquals($sendMessage, $this->catchedMessage);
    }
}