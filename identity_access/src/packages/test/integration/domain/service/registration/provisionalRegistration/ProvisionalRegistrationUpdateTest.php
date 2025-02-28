<?php

use App\Models\AuthenticationInformation as EloquentAuthenticationInformation;
use App\Models\User as EloquentUser;
use App\Models\DefinitiveRegistrationConfirmation as EloquentDefinitiveRegistrationConfirmation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use packages\adapter\email\LaravelEmailSender;
use packages\adapter\persistence\eloquent\EloquentDefinitiveRegistrationConfirmationRepository;
use packages\adapter\persistence\eloquent\EloquentAuthenticationAccountRepository;
use packages\domain\model\definitiveRegistrationConfirmation\OneTimeToken;
use packages\domain\model\definitiveRegistrationConfirmation\OneTimeTokenExpiration;
use packages\domain\model\definitiveRegistrationConfirmation\OneTimeTokenValue;
use packages\domain\model\email\SendEmailDto;
use packages\domain\model\authenticationAccount\UserEmail;
use packages\domain\model\authenticationAccount\password\UserPassword;
use packages\domain\model\authenticationAccount\DefinitiveRegistrationCompletedStatus;
use packages\domain\model\authenticationAccount\password\Argon2HashPasswordManager;
use packages\domain\model\email\IEmailSender;
use packages\domain\service\registration\provisionalRegistration\ProvisionalRegistration;
use packages\domain\service\registration\provisionalRegistration\ProvisionalRegistrationUpdate;
use packages\test\helpers\domain\definitiveRegistrationConfirmation\DefinitiveRegistrationConfirmationTestDataCreator;
use packages\test\helpers\domain\authenticationAccount\AuthenticationAccountTestDataCreator;
use packages\test\helpers\domain\authenticationAccount\password\Md5PasswordManager;
use packages\test\helpers\domain\authenticationAccount\TestAuthenticationAccountFactory;
use packages\test\helpers\adapter\transactionManage\TestTransactionManage;
use Tests\TestCase;

class ProvisionalRegistrationUpdateTest extends TestCase
{
    private EloquentDefinitiveRegistrationConfirmationRepository $definitiveRegistrationConfirmationRepository;
    private EloquentAuthenticationAccountRepository $authenticationAccountRepository;
    private AuthenticationAccountTestDataCreator $authenticationAccountTestDataCreator;
    private DefinitiveRegistrationConfirmationTestDataCreator $definitiveRegistrationConfirmationTestDataCreator;
    private IEmailSender $emailSender;
    private SendEmailDto $capturedSendEmailDto;
    private ProvisionalRegistrationUpdate $provisionalRegistrationUpdate;

    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
        $this->definitiveRegistrationConfirmationRepository = new EloquentDefinitiveRegistrationConfirmationRepository();
        $this->authenticationAccountRepository = new EloquentAuthenticationAccountRepository();
        $this->transactionManage = new TestTransactionManage();
        $this->authenticationAccountTestDataCreator = new AuthenticationAccountTestDataCreator(
            $this->authenticationAccountRepository,
            new TestAuthenticationAccountFactory(new Argon2HashPasswordManager())
        );
        $this->definitiveRegistrationConfirmationTestDataCreator = new DefinitiveRegistrationConfirmationTestDataCreator($this->definitiveRegistrationConfirmationRepository, $this->authenticationAccountRepository);

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

        // テスト前にデータを全削除する
        EloquentAuthenticationInformation::query()->delete();
        EloquentDefinitiveRegistrationConfirmation::query()->delete();
        EloquentUser::query()->delete();
    }

    public function test_ユーザー登録が成功する()
    {
        // given
        $userEmail = new UserEmail('test@example.com');
        $userPassword = UserPassword::create('acbABC123_',  new Md5PasswordManager());
        $oneTimeToken = OneTimeToken::create();

        // when
        // ユーザー登録を行う
        $this->provisionalRegistrationUpdate->handle($userEmail, $userPassword, $oneTimeToken);

        // then
        // 未認証状態の認証アカウントが登録されていることを確認する
        $actualAuthenticationAccount = $this->authenticationAccountRepository->findByEmail($userEmail);
        $this->assertEquals(DefinitiveRegistrationCompletedStatus::Incomplete, $actualAuthenticationAccount->definitiveRegistrationCompletedStatus());

        // 本登録確認情報が登録されていることを確認する
        $actualDefinitiveRegistrationConfirmation = $this->definitiveRegistrationConfirmationRepository->findByTokenValue($oneTimeToken->tokenValue());
        $this->assertNotEmpty($actualDefinitiveRegistrationConfirmation);

        // メール送信する内容が正しいことを確認する
        $this->assertEquals($userEmail->value, $this->capturedSendEmailDto->toAddress);
        $this->assertEquals($actualDefinitiveRegistrationConfirmation->oneTimePassword()->value, $this->capturedSendEmailDto->templateVariables['oneTimePassword']);
        $this->assertStringContainsString($actualDefinitiveRegistrationConfirmation->oneTimeToken()->tokenValue()->value, $this->capturedSendEmailDto->templateVariables['definitiveRegisterUrl']);
    }
}