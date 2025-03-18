<?php

namespace App\Console\Commands;

use dddCommonLib\domain\model\common\IMessagingLogger;
use dddCommonLib\infrastructure\messaging\kafka\MessageKafkaConsumer;
use Illuminate\Console\Command;
use packages\adapter\messaging\kafka\listener\DeleteOnetTimeTokenAndPasswordListener;
use packages\application\registration\definitiveRegistration\DeleteOnetTimeTokenAndPasswordApplicationService;
use packages\domain\model\definitiveRegistrationConfirmation\IDefinitiveRegistrationConfirmationRepository;

class DeleteOnetTimeTokenAndPasswordListenerExecuter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-onet-time-token-and-password-listener-executer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'ワンタイムトークンとワンタイムパスワードを削除するリスナーを実行する';

    private IDefinitiveRegistrationConfirmationRepository $definitiveRegistrationConfirmationRepository;
    private IMessagingLogger $messagingLogger;

    public function __construct(
        IDefinitiveRegistrationConfirmationRepository $definitiveRegistrationConfirmationRepository,
        IMessagingLogger $messagingLogger
    ) {
        parent::__construct();
        $this->definitiveRegistrationConfirmationRepository = $definitiveRegistrationConfirmationRepository;
        $this->messagingLogger = $messagingLogger;
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $appService = new DeleteOnetTimeTokenAndPasswordApplicationService(
            $this->definitiveRegistrationConfirmationRepository
        );

        $consumer = new MessageKafkaConsumer(
            config('app.delete_one_time_cousumer_group_id'),
            config('app.kafkaHostName'),
            [config('app.identity_access_topic_name')],
        );

        $listener = new DeleteOnetTimeTokenAndPasswordListener(
            $consumer,
            $this->messagingLogger,
            $appService
        );
        $listener->handle();
    }
}
