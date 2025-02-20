<?php

require_once 'vendor/autoload.php';

use dddCommonLib\infrastructure\messaging\kafka\KafkaConsumer;

$consumer = new KafkaConsumer(
    'testGroupId',
    'kafka:9092',
    'testTopic'
);

$filteredDispatch = function (string $message) {
    throw new Exception('メッセージが受信されました');
};

print("コンシューマーを起動します");

$consumer->handle($filteredDispatch);