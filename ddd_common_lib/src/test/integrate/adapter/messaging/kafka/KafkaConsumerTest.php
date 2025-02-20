<?php

use dddCommonLib\infrastructure\messaging\kafka\KafkaConsumer;
use dddCommonLib\infrastructure\messaging\kafka\KafkaProducer;
use dddCommonLib\test\helpers\adapter\messaging\kafka\KafkaCatchedTestMessageList;
use PHPUnit\Framework\TestCase;

class KafkaConsumerTest extends TestCase
{
    private KafkaProducer $producer;
    private KafkaConsumer $consumer;
    private string $catchedMessage;

    public function setUp(): void
    {
        $this->producer = new KafkaProducer('kafka:9092', 'testTopic');
        $this->consumer = new KafkaConsumer(
            'testGroupId',
            'kafka:9092',
            'testTopic'
        );
    }

    public function tearDown(): void
    {
        
    }

    public function test_プロデューサから送信したメッセージを受信できることを確認する()
    {
        // given
        $sendMessage = 'test message';

        $this->producer->send($sendMessage);

        $filteredDispath = function (string $message) {
            $this->catchedMessage = $message;
            throw new Exception('メッセージが受信されました');
        };

        // when
        try {
            $this->consumer->handle($filteredDispath);
        } catch (Exception $ex) {
            // 何もしない
        }

        // then
        $this->assertEquals($sendMessage, $this->catchedMessage);
    }
}