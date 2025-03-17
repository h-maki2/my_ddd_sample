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

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $emailSender = app(IEmailSender::class);
        $logger = app(IMessagingLogger::class);

        $consumer = new MessageKafkaConsumer(
            'aaaaaaaaaaaaaaaa',
            config('app.kafkaHostName'),
            [config('app.notification_topic_name')]
        );

        $appService = new SendDefinitiveRegistrationCompleteEmailApplicationService(
            $emailSender
        );

        $listener = new SendDefinitiveRegistrationCompleteEmailListener(
            $consumer,
            $logger,
            $appService
        );
        $listener->handle();
    }
}
