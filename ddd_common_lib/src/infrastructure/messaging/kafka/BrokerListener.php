<?php

namespace dddCommonLib\infrastructure\messaging\kafka;

use dddCommonLib\domain\model\common\IMessagingLogger;

abstract class BrokerListener
{
    protected IMessagingLogger $logger;

    protected const WAIT_TIME_S = 5;

    public function __construct(IMessagingLogger $logger)
    {
        $this->logger = $logger;
    }

    abstract public function handle(): void;

    protected function errorHandling(int $messageError): void
    {
        switch ($messageError) {
            case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                // メッセージがまだ来ていない（正常）
                break;
            case RD_KAFKA_RESP_ERR__TIMED_OUT:
                // タイムアウト（正常）
                break;
            default:
                $this->logger->error("Kafka Consumer Error: " . rd_kafka_err2str($messageError));
                sleep(self::WAIT_TIME_S);
                break;
        }
    }
}