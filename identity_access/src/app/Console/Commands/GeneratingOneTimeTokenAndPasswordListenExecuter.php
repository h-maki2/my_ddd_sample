<?php

namespace App\Console\Commands;

use dddCommonLib\infrastructure\messaging\kafka\MessageKafkaConsumer;
use Illuminate\Console\Command;
use packages\adapter\messaging\kafka\listener\GeneratingOneTimeTokenAndPasswordListener;
use packages\application\registration\provisionalRegistration\GeneratingOneTimeTokenAndPasswordApplicationService;
use packages\domain\model\common\transactionManage\TransactionManage;
use packages\domain\model\definitiveRegistrationConfirmation\IDefinitiveRegistrationConfirmationRepository;
use packages\domain\model\email\IEmailSender;
use packages\messaging\kafka\consumer\generatingOneTimeTokenAndPassword\GeneratingOneTimeTokenAndPasswordConsumer;
use packages\messaging\kafka\LaravelMessagingLogger;

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

    public function __construct(
        TransactionManage $transactionManage,
        IDefinitiveRegistrationConfirmationRepository $definitiveRegistrationConfirmationRepository,
        IEmailSender $emailSender
    )
    {
        $this->transactionManage = $transactionManage;
        $this->definitiveRegistrationConfirmationRepository = $definitiveRegistrationConfirmationRepository;
        $this->emailSender = $emailSender;
    }


    /**
     * Execute the console command.
     */
    public function handle()
    {
        $appService = new GeneratingOneTimeTokenAndPasswordApplicationService(
            $this->emailSender,
            $this->definitiveRegistrationConfirmationRepository,
            $this->transactionManage
        );

        $consumer = new MessageKafkaConsumer(
            config('app.consumerGroupId'),
            config('app.kafkaHostName'),
            config('app.topickName'),
        );

        $listener = new GeneratingOneTimeTokenAndPasswordListener(
            $consumer,
            new LaravelMessagingLogger(),
            $appService
        );
        $listener->handle();
    }
}
