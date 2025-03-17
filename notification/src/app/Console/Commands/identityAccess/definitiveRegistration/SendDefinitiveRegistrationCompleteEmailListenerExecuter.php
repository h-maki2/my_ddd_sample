<?php

namespace App\Console\Commands\identityAccess\definitiveRegistration;

use dddCommonLib\domain\model\common\IMessagingLogger;
use dddCommonLib\infrastructure\messaging\kafka\MessageKafkaConsumer;
use Illuminate\Console\Command;
use packages\adapter\messaging\kafka\listener\identityAccess\definitiveRegistration\SendDefinitiveRegistrationCompleteEmailListener;
use packages\application\identityAccess\definitiveRegistration\SendDefinitiveRegistrationCompleteEmailApplicationService;
use packages\domain\model\email\IEmailSender;

class SendDefinitiveRegistrationCompleteEmailListenerExecuter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-definitive-registration-complete-email-listener-executer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '本登録完了メールを送信するリスナーを実行する';

    private IEmailSender $emailSender;
    private IMessagingLogger $logger;

    public function __construct(
        IEmailSender $emailSender,
        IMessagingLogger $logger
    )
    {
        parent::__construct();
        $this->emailSender = $emailSender;
        $this->logger = $logger;
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $consumer = new MessageKafkaConsumer(
            config('app.consumerGroupId'),
            config('app.kafkaHostName'),
            [config('app.notification_topic_name')]
        );

        $appService = new SendDefinitiveRegistrationCompleteEmailApplicationService(
            $this->emailSender
        );

        $listener = new SendDefinitiveRegistrationCompleteEmailListener(
            $consumer,
            $this->logger,
            $appService
        );
        $listener->handle();
    }
}
