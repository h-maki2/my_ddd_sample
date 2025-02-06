<?php

use dddCommonLib\adapter\messaging\rabbitmq\RabbitMqDeliveryMode;
use dddCommonLib\adapter\messaging\rabbitmq\RabbitMqMessage;
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
        // 送信したメッセージのデリバリーモードが取得できる
        $this->assertEquals($deliveryMode->value, $rabbitMqMessage->value->get('delivery_mode'));
    }
}