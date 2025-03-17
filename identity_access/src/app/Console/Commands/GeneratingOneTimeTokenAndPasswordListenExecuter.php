<?php

namespace App\Console\Commands;

use dddCommonLib\domain\model\common\IMessagingLogger;
use dddCommonLib\domain\model\eventStore\IEventStore;
use dddCommonLib\infrastructure\messaging\kafka\MessageKafkaConsumer;
use Illuminate\Console\Command;
use packages\adapter\messaging\kafka\LaravelMessagingLogger;
use packages\adapter\messaging\kafka\listener\GeneratingOneTimeTokenAndPasswordListener;
use packages\application\registration\provisionalRegistration\GeneratingOneTimeTokenAndPasswordApplicationService;
use packages\domain\model\authenticationAccount\IAuthenticationAccountRepository;
use packages\domain\model\common\transactionManage\TransactionManage;
use packages\domain\model\definitiveRegistrationConfirmation\IDefinitiveRegistrationConfirmationRepository;
use packages\domain\model\email\IEmailSender;
use packages\messaging\kafka\consumer\generatingOneTimeTokenAndPassword\GeneratingOneTimeTokenAndPasswordConsumer;


class GeneratingOneTimeTokenAndPasswordListenExecuter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generating-oneTimeToken-and-password-consumer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '本登録用のワンタイムトークンとパスワードを作成するコンシューマを実行する';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $transactionManage = app(TransactionManage::class);
        $definitiveRegistrationConfirmationRepository = app(IDefinitiveRegistrationConfirmationRepository::class);;
        $messagingLogger = app(IMessagingLogger::class);
        $authenticationAccountRepository = app(IAuthenticationAccountRepository::class);
        $eventStore = app(IEventStore::class);

        $appService = new GeneratingOneTimeTokenAndPasswordApplicationService(
            $definitiveRegistrationConfirmationRepository,
            $transactionManage,
            $eventStore,
            $authenticationAccountRepository
        );

        $consumer = new MessageKafkaConsumer(
            'generate_one_time_consumer_group',
            config('app.kafkaHostName'),
            [config('app.identity_access_topic_name')],
        );

        $listener = new GeneratingOneTimeTokenAndPasswordListener(
            $consumer,
            $messagingLogger,
            $appService
        );
        $listener->handle();
    }
}
