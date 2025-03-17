<?php

namespace App\Console\Commands\identityAccess\definitiveRegistration;

use dddCommonLib\domain\model\common\IMessagingLogger;
use dddCommonLib\infrastructure\messaging\kafka\MessageKafkaConsumer;
use Illuminate\Console\Command;
use packages\adapter\messaging\kafka\listener\identityAccess\definitiveRegistration\SendDefinitiveRegistrationConfirmationEmailListener;
use packages\application\identityAccess\definitiveRegistration\SendDefinitiveRegistrationConfirmationEmailApplicationService;
use packages\domain\model\email\IEmailSender;

class SendDefinitiveRegistrationConfirmationEmailListenerExecuter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-definitive-registration-confirmation-email-listener';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '仮登録完了メールを送信するリスナーを実行する';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $emailSender = app(IEmailSender::class);
        $logger = app(IMessagingLogger::class);

        $consumer = new MessageKafkaConsumer(
            'scscnsnnsnnenne',
            config('app.kafkaHostName'),
            [config('app.notification_topic_name')]
        );

        $appService = new SendDefinitiveRegistrationConfirmationEmailApplicationService(
            $emailSender
        );

        $listener = new SendDefinitiveRegistrationConfirmationEmailListener(
            $consumer,
            $logger,
            $appService
        );
        $listener->handle();
    }
}
