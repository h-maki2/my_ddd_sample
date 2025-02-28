<?php

use dddCommonLib\infrastructure\messaging\kafka\CdcMessageListener;
use dddCommonLib\infrastructure\messaging\kafka\KafkaProducer;
use packages\messaging\kafka\LaravelMessagingLogger;

$producer = new KafkaProducer(
    config('app.kafkaHostName'),
    config('app.topickName')
);

$cdcConsumer = new CdcMessageListener(
    config('app.kafkaHostName'),
    config('app.cdcSubscribedDbTable'),
    new LaravelMessagingLogger(),
    $producer
);

$cdcConsumer->handle();