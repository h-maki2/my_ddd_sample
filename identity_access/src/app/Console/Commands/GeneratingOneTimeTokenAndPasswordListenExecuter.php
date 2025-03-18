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

    private IDefinitiveRegistrationConfirmationRepository $definitiveRegistrationConfirmationRepository;
    private IMessagingLogger $messagingLogger;
    private IAuthenticationAccountRepository $authenticationAccountRepository;
    private IEventStore $eventStore;
    private TransactionManage $transactionManage;

    public function __construct(
        IDefinitiveRegistrationConfirmationRepository $definitiveRegistrationConfirmationRepository,
        IMessagingLogger $messagingLogger,
        IAuthenticationAccountRepository $authenticationAccountRepository,
        IEventStore $eventStore,
        TransactionManage $transactionManage
    ) {
        parent::__construct();
        $this->definitiveRegistrationConfirmationRepository = $definitiveRegistrationConfirmationRepository;
        $this->messagingLogger = $messagingLogger;
        $this->authenticationAccountRepository = $authenticationAccountRepository;
        $this->eventStore = $eventStore;
        $this->transactionManage = $transactionManage;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $appService = new GeneratingOneTimeTokenAndPasswordApplicationService(
            $this->definitiveRegistrationConfirmationRepository,
            $this->transactionManage,
            $this->eventStore,
            $this->authenticationAccountRepository
        );

        $consumer = new MessageKafkaConsumer(
            config('app.generate_one_time_consumer_group_id'),
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
