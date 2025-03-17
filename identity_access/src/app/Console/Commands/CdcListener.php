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

    private IMessagingLogger $messagingLogger;

    public function __construct(
        IMessagingLogger $messagingLogger
    )
    {
        parent::__construct();
        $this->messagingLogger = $messagingLogger;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $producer = new KafkaProducer(
            config('app.kafkaHostName'),
            config('app.sourceTopicName')
        );

        $consumer = new CdcKafkaConsumer(
            config('app.kafkaHostName'),
            [config('app.cdcTopicName')],
            config('app.cdcConsumerGroupId')
        );

        $cdcBrokerListener = new CdcBrokerListener(
            $consumer,
            $producer,
            $this->messagingLogger
        );
        $cdcBrokerListener->handle();
    }
}
