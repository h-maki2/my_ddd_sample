<?php

namespace App\Console\Commands;

use dddCommonLib\domain\model\common\IMessagingLogger;
use dddCommonLib\infrastructure\messaging\kafka\MessageKafkaConsumer;
use Illuminate\Console\Command;
use packages\adapter\messaging\kafka\listener\GeneratingOneTimeTokenAndPasswordListener;
use packages\adapter\messaging\kafka\listener\SendDefinitiveRegistrationConfirmationEmailListener;
use packages\application\definitiveRegistrationConfirmation\SendDefinitiveRegistrationConfirmationEmailApplicationService;
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
    public function handle()
    {
        $consumer = new MessageKafkaConsumer(
            config('app.consumerGroupId'),
            config('app.kafkaHostName'),
            [config('app.notification_topic_name')]
        );

        $appService = new SendDefinitiveRegistrationConfirmationEmailApplicationService(
            $this->emailSender
        );

        $listener = new SendDefinitiveRegistrationConfirmationEmailListener(
            $consumer,
            $this->logger,
            $appService
        );
        $listener->handle();
    }
}
