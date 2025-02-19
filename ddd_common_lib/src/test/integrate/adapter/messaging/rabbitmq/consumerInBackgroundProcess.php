<?php

require_once 'vendor/autoload.php';

use dddCommonLib\infrastructure\messaging\rabbitmq\ConnectionSettings;
use dddCommonLib\infrastructure\messaging\rabbitmq\Exchange;
use dddCommonLib\infrastructure\messaging\rabbitmq\MessageConsumer;
use dddCommonLib\infrastructure\messaging\rabbitmq\RabbitMqMessage;
use dddCommonLib\infrastructure\messaging\rabbitmq\RabbitMqQueue;
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
    function (string $messageBody) {
       throw new Exception('DLXにメッセージを転送します');
    }
);

print("コンシューマーを起動します");

$consumer->listen();