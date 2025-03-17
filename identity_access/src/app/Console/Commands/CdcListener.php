<?php

namespace App\Console\Commands;

use dddCommonLib\domain\model\common\IMessagingLogger;
use dddCommonLib\infrastructure\messaging\kafka\CdcBrokerListener;
use dddCommonLib\infrastructure\messaging\kafka\CdcKafkaConsumer;
use dddCommonLib\infrastructure\messaging\kafka\KafkaProducer;
use Illuminate\Console\Command;

class CdcListener extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cdc-listener';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'CDCを実行する';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $messagingLogger = app(IMessagingLogger::class);

        $consumer = new CdcKafkaConsumer(
            config('app.kafkaHostName'),
            [config('app.cdcTopicName')],
            config('app.cdcConsumerGroupId')
        );

        $cdcBrokerListener = new CdcBrokerListener(
            $consumer,
            $this->targetProducerList(),
            $messagingLogger
        );
        $cdcBrokerListener->handle();
    }

    /**
     * @return KafkaProducer[]
     */
    private function targetProducerList(): array
    {
        return [
            new KafkaProducer(
                config('app.kafkaHostName'),
                config('app.identity_access_topic_name')
            ),
            new KafkaProducer(
                config('app.kafkaHostName'),
                config('app.notification_topic_name')
            ),
        ];
    }
}
