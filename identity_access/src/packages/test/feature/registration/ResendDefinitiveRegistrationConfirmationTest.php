<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use packages\adapter\persistence\eloquent\EloquentDefinitiveRegistrationConfirmationRepository;
use packages\adapter\persistence\eloquent\EloquentAuthenticationAccountRepository;
use packages\domain\model\authenticationAccount\UserEmail;
use packages\domain\model\authenticationAccount\DefinitiveRegistrationCompletedStatus;
use packages\domain\model\authenticationAccount\password\Argon2HashPasswordManager;
use packages\test\helpers\definitiveRegistrationConfirmation\DefinitiveRegistrationConfirmationTestDataCreator;
use packages\test\helpers\authenticationAccount\AuthenticationAccountTestDataCreator;
use packages\test\helpers\authenticationAccount\TestAuthenticationAccountFactory;
use Tests\TestCase;

class ResendDefinitiveRegistrationConfirmationTest extends TestCase
{
    private EloquentAuthenticationAccountRepository $eloquentAuthenticationAccountRepository;
    private EloquentDefinitiveRegistrationConfirmationRepository $eloquentDefinitiveRegistrationConfirmationRepository;
    private AuthenticationAccountTestDataCreator $authenticationAccountTestDataCreator;
    private DefinitiveRegistrationConfirmationTestDataCreator $definitiveRegistrationConfirmationTestDataCreator;

    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
        $this->eloquentAuthenticationAccountRepository = new EloquentAuthenticationAccountRepository();
        $this->eloquentDefinitiveRegistrationConfirmationRepository = new EloquentDefinitiveRegistrationConfirmationRepository();
        $this->authenticationAccountTestDataCreator = new AuthenticationAccountTestDataCreator(
            $this->eloquentAuthenticationAccountRepository,
            new TestAuthenticationAccountFactory(new Argon2HashPasswordManager())
        );
        $this->definitiveRegistrationConfirmationTestDataCreator = new DefinitiveRegistrationConfirmationTestDataCreator($this->eloquentDefinitiveRegistrationConfirmationRepository, $this->eloquentAuthenticationAccountRepository);
    }

    public function test_登録済みのメールアドレスの場合に、本登録確認メールを再送信できる()
    {
        // given
        // 未認証の認証アカウントを作成して保存する
        $userEmail = new UserEmail('hello@example.com');
        $userId = $this->eloquentAuthenticationAccountRepository->nextUserId();
        $this->authenticationAccountTestDataCreator->create(
            email: $userEmail, 
            id: $userId,
            definitiveRegistrationCompletedStatus: DefinitiveRegistrationCompletedStatus::Incomplete
        );

        // 本登録確認を作成して保存する
        $this->definitiveRegistrationConfirmationTestDataCreator->create(userId: $userId);

        // when
        // 本登録確認メールを再送信する
        $response = $this->post('/resend', ['email' => $userEmail->value]);

        // then
        $response->assertStatus(200);
        $content = htmlspecialchars_decode($response->getContent());
        $this->assertStringContainsString('<title>本登録確認メール再送完了</title>', $content);
    }
}