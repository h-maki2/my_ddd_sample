<?php

require_once 'vendor/autoload.php';

use dddCommonLib\adapter\messaging\rabbitmq\ConnectionSettings;
use dddCommonLib\adapter\messaging\rabbitmq\Exchange;
use dddCommonLib\adapter\messaging\rabbitmq\MessageConsumer;
use dddCommonLib\adapter\messaging\rabbitmq\RabbitMqMessage;
use dddCommonLib\adapter\messaging\rabbitmq\RabbitMqQueue;
use dddCommonLib\domain\model\notification\Notification;
use dddCommonLib\test\helpers\adapter\messaging\rabbitmq\TestExchangeName;
use dddCommonLib\test\helpers\adapter\messaging\rabbitmq\TestQueueName;

$testConnection = new ConnectionSettings('rabbitmq', 'user', 'password', 5672);

$testExchangeName = TestExchangeName::TEST_EXCHANGE_NAME->value;
$testQueueName = TestQueueName::TEST_QUEUE_NAME->value;

// エクスチェンジを作成する
$exchange = Exchange::fanOutInstance(
    $testConnection,
    $testExchangeName,
    true
);

// キューを作成する
$queue = RabbitMqQueue::fromInstanceWithBindExchange(
    $exchange,
    $testQueueName
);

$consumer = new MessageConsumer(
    $queue,
    $testExchangeName,
    [],
    function (Notification $notification) {
       throw new Exception('DLXにメッセージを転送します');
    }
);

print("コンシューマーを起動します");

$consumer->listen();