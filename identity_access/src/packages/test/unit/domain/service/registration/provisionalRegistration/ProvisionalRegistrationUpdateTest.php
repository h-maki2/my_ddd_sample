<?php

use packages\adapter\persistence\inMemory\InMemoryDefinitiveRegistrationConfirmationRepository;
use packages\adapter\persistence\inMemory\InMemoryAuthenticationAccountRepository;
use packages\domain\model\definitiveRegistrationConfirmation\OneTimeToken;
use packages\domain\model\email\SendEmailDto;
use packages\domain\model\authenticationAccount\UserEmail;
use packages\domain\model\authenticationAccount\password\UserPassword;
use packages\domain\model\authenticationAccount\DefinitiveRegistrationCompletedStatus;
use packages\domain\model\email\IEmailSender;
use packages\domain\service\registration\provisionalRegistration\ProvisionalRegistrationUpdate;
use packages\test\helpers\domains\authenticationAccount\password\Md5PasswordManager;
use packages\test\helpers\adapter\transactionManage\TestTransactionManage;
use PHPUnit\Framework\TestCase;

class ProvisionalRegistrationUpdateTest extends TestCase
{
    private InMemoryDefinitiveRegistrationConfirmationRepository $definitiveRegistrationConfirmationRepository;
    private InMemoryAuthenticationAccountRepository $authenticationAccountRepository;
    private TestTransactionManage $transactionManage;
    private SendEmailDto $capturedSendEmailDto;
    private ProvisionalRegistrationUpdate $provisionalRegistrationUpdate;
    private IEmailSender $emailSender;

    public function setUp(): void
    {
        $this->definitiveRegistrationConfirmationRepository = new InMemoryDefinitiveRegistrationConfirmationRepository();
        $this->authenticationAccountRepository = new InMemoryAuthenticationAccountRepository();
        $this->transactionManage = new TestTransactionManage();
        
        $emailSender = $this->createMock(IEmailSender::class);
        $emailSender
            ->method('send')
            ->with($this->callback(function (SendEmailDto $sendEmailDto) {
                $this->capturedSendEmailDto = $sendEmailDto;
                return true;
            }));
        $this->emailSender = $emailSender;

        $this->provisionalRegistrationUpdate = new ProvisionalRegistrationUpdate(
            $this->authenticationAccountRepository,
            $this->definitiveRegistrationConfirmationRepository,
            $this->transactionManage,
            $this->emailSender
        );
    }

    public function test_ユーザー登録が成功する()
    {
        // given
        $userEmail = new UserEmail('test@example.com');
        $userPassword = UserPassword::create('acbABC123_',  new Md5PasswordManager());
        $oneTimeToken = OneTimeToken::create();

        // when
        $this->provisionalRegistrationUpdate->handle($userEmail, $userPassword, $oneTimeToken);

        // then
        // ユーザーが未認証状態で登録されていることを確認
        $actualAuthInfo = $this->authenticationAccountRepository->findByEmail($userEmail);
        $this->assertEquals(DefinitiveRegistrationCompletedStatus::Incomplete, $actualAuthInfo->definitiveRegistrationCompletedStatus());
        $this->assertEquals($userPassword, $actualAuthInfo->password());

        // 本登録確認情報が保存されていることを確認
        $actualDefinitiveRegistrationConfirmation = $this->definitiveRegistrationConfirmationRepository->findByTokenValue($oneTimeToken->tokenValue());
        $this->assertNotEmpty($actualDefinitiveRegistrationConfirmation);

        // メール送信する内容が正しいことを確認する
        $this->assertEquals($userEmail->value, $this->capturedSendEmailDto->toAddress);
        $this->assertEquals($actualDefinitiveRegistrationConfirmation->oneTimePassword()->value, $this->capturedSendEmailDto->templateVariables['oneTimePassword']);
        $this->assertStringContainsString($actualDefinitiveRegistrationConfirmation->oneTimeToken()->tokenValue()->value, $this->capturedSendEmailDto->templateVariables['definitiveRegisterUrl']);
    }
}