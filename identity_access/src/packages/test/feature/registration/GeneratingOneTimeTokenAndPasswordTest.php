<?php

use dddCommonLib\infrastructure\messaging\kafka\KafkaProducer;
use dddCommonLib\test\helpers\domain\model\notification\TestNotificationFactory;
use Illuminate\Support\Facades\DB;
use packages\adapter\persistence\eloquent\EloquentAuthenticationAccountRepository;
use packages\adapter\persistence\eloquent\EloquentDefinitiveRegistrationConfirmationRepository;
use packages\test\helpers\domain\authenticationAccount\password\Md5PasswordManager;
use packages\test\helpers\domain\authenticationAccount\TestAuthenticationAccountCreatedFactory;
use packages\test\helpers\domain\authenticationAccount\TestAuthenticationAccountFactory;
use Tests\TestCase;

class GeneratingOneTimeTokenAndPasswordTest extends TestCase
{
    private EloquentDefinitiveRegistrationConfirmationRepository $definitiveRegistrationConfirmationRepository;
    private EloquentAuthenticationAccountRepository $authenticationAccountRepository;
    private KafkaProducer $kafkaProducer;

    public function setUp(): void
    {
        parent::setUp();

        DB::table('definitive_registration_confirmations')->truncate();

        $this->definitiveRegistrationConfirmationRepository = new EloquentDefinitiveRegistrationConfirmationRepository();
        $this->authenticationAccountRepository = new EloquentAuthenticationAccountRepository();

        $this->kafkaProducer = new KafkaProducer(
            config('app.kafkaHostName'),
            config('app.topickName')
        );

        // イベントを受信するリスナを起動させておく
        exec('php artisan app:generating-oneTimeToken-and-password-consumer > /dev/null 2>&1 &');
    }

    public function test_authenticationAccountCreatedイベントがpublishされた場合、ワンタイムパスワードとワンタイムトークンが生成されることを確認()
    {
        // given
        // authenticationAccountCreatedイベントを作成する
        $userId = $this->authenticationAccountRepository->nextUserId();
        $authAccountCreatedFactory = new TestAuthenticationAccountCreatedFactory(
            $this->authenticationAccountRepository,
            new TestAuthenticationAccountFactory(new Md5PasswordManager())
        );
        $authAccountCreated = $authAccountCreatedFactory->create($userId);

        // notificationを作成する
        $notification = TestNotificationFactory::createFromDomainEvent($authAccountCreated);

        // when
        // イベントをpublishする
        $this->kafkaProducer->send($notification);
        sleep(5);

        // then
        // ワンタイムパスワードとワンタイムトークンが生成されていることを確認する
        $definitiveRegistrationConfirmation = $this->definitiveRegistrationConfirmationRepository->findById($userId);
        $this->assertNotNull($definitiveRegistrationConfirmation);
    }
}