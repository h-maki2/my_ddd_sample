<?php

use dddCommonLib\adapter\messaging\rabbitmq\RabbitMqDeliveryMode;
use dddCommonLib\adapter\messaging\rabbitmq\RabbitMqMessage;
use dddCommonLib\adapter\messaging\rabbitmq\RabbitMqRetryCount;
use PHPUnit\Framework\TestCase;

class RabbitMqMessageTest extends TestCase
{
    public function test_エクスチェンジに送信するメッセージを取得できる()
    {
        // given
        // 送信するメッセージを取得する
        $sendingMessage = json_encode(['test' => 'test message']);
        $deliveryMode = RabbitMqDeliveryMode::PERSISTENT;

        // when
        $rabbitMqMessage = RabbitMqMessage::get($sendingMessage, $deliveryMode);

        // then
        // 送信したメッセージが取得できる
        $this->assertEquals($sendingMessage, $rabbitMqMessage->value->body);

        // 送信したメッセージのデリバリーモードを取得できる
        $this->assertEquals($deliveryMode, $rabbitMqMessage->deliveryMode());
        
        // 再送信の回数が0回であることを確認する
        $headers = $rabbitMqMessage->value->get('application_headers');
        $retryCount = $headers->getNativeData()[RabbitMqRetryCount::key()];
        $this->assertEquals(0, $retryCount);

        // 再送信の回数が最大回数に達していないことを確認する
        $this->assertFalse($rabbitMqMessage->hasReachedMaxRetryCount());
    }

    public function test_エクスチェンジに再送信するメッセージを取得できる()
    {
        // given
        // 送信するメッセージを取得する
        $sendingMessage = json_encode(['test' => 'test message']);
        $deliveryMode = RabbitMqDeliveryMode::PERSISTENT;
        $rabbitMqMessage = RabbitMqMessage::get($sendingMessage, $deliveryMode);

        // when
        // 再送信するメッセージを取得する
        $retrievedMessage = $rabbitMqMessage->retrieve();

        // then
        // 再送信するメッセージが取得できる
        $this->assertEquals($sendingMessage, $retrievedMessage->value->body);

        // 再送信するメッセージのデリバリーモードを取得できる
        $this->assertEquals($deliveryMode, $rabbitMqMessage->deliveryMode());

        // 再送信の回数が1回であることを確認する
        $headers = $retrievedMessage->value->get('application_headers');
        $retryCount = $headers->getNativeData()[RabbitMqRetryCount::key()];
        $this->assertEquals(1, $retryCount);

        // 再送信の回数が最大回数に達していないことを確認する
        $this->assertFalse($retrievedMessage->hasReachedMaxRetryCount());
    }

    public function test_エクスチェンジに再送信するメッセージが最大回数に達しているか確認できる()
    {
        // given
        // 送信するメッセージを取得する
        $sendingMessage = json_encode(['test' => 'test message']);
        $deliveryMode = RabbitMqDeliveryMode::PERSISTENT;
        $rabbitMqMessage = RabbitMqMessage::get($sendingMessage, $deliveryMode);

        // when
        // 3回再送信する
        $retrievedMessage = $rabbitMqMessage->retrieve();
        $retrievedMessage = $retrievedMessage->retrieve();
        $retrievedMessage = $retrievedMessage->retrieve();

        // then
        // 再送信するメッセージが取得できる
        $this->assertEquals($sendingMessage, $retrievedMessage->value->body);

        // 再送信するメッセージのデリバリーモードを取得できる
        $this->assertEquals($deliveryMode, $retrievedMessage->deliveryMode());

        // 再送信の回数が最大回数に達していることを確認する
        $headers = $retrievedMessage->value->get('application_headers');
        $retryCount = $headers->getNativeData()[RabbitMqRetryCount::key()];
        $this->assertEquals(3, $retryCount);

        // 再送信の回数が最大回数に達していることを確認する
        $this->assertTrue($retrievedMessage->hasReachedMaxRetryCount());
    }
}