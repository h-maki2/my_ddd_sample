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

    private TransactionManage $transactionManage;
    private IDefinitiveRegistrationConfirmationRepository $definitiveRegistrationConfirmationRepository;
    private IEmailSender $emailSender;
    private IMessagingLogger $messagingLogger;
    private IAuthenticationAccountRepository $authenticationAccountRepository;
    private IEventStore $eventStore;

    public function __construct(
        TransactionManage $transactionManage,
        IDefinitiveRegistrationConfirmationRepository $definitiveRegistrationConfirmationRepository,
        IEmailSender $emailSender,
        IMessagingLogger $messagingLogger,
        IAuthenticationAccountRepository $authenticationAccountRepository,
        IEventStore $eventStore
    )
    {
        parent::__construct();
        $this->transactionManage = $transactionManage;
        $this->definitiveRegistrationConfirmationRepository = $definitiveRegistrationConfirmationRepository;
        $this->emailSender = $emailSender;
        $this->messagingLogger = $messagingLogger;
        $this->authenticationAccountRepository = $authenticationAccountRepository;
        $this->eventStore = $eventStore;
    }


    /**
     * Execute the console command.
     */
    public function handle()
    {
        $appService = new GeneratingOneTimeTokenAndPasswordApplicationService(
            $this->emailSender,
            $this->definitiveRegistrationConfirmationRepository,
            $this->transactionManage,
            $this->eventStore,
            $this->authenticationAccountRepository
        );

        $consumer = new MessageKafkaConsumer(
            config('app.consumerGroupId'),
            config('app.kafkaHostName'),
            [config('app.identity_access_topic_name')],
        );

        $listener = new GeneratingOneTimeTokenAndPasswordListener(
            $consumer,
            $this->messagingLogger,
            $appService
        );
        $listener->handle();
    }
}
