<?php

use dddCommonLib\infrastructure\messaging\kafka\KafkaCdcConsumer;
use dddCommonLib\infrastructure\messaging\kafka\KafkaProducer;
use packages\messaging\kafka\LaravelMessagingLogger;

$producer = new KafkaProducer(
    config('app.kafkaHostName'),
    config('app.topickName')
);

$cdcConsumer = new KafkaCdcConsumer(
    config('app.kafkaHostName'),
    config('app.cdcSubscribedDbTable'),
    new LaravelMessagingLogger(),
    $producer
);

$cdcConsumer->handle();