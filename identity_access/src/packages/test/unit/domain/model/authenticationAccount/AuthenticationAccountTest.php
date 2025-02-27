<?php

use Lcobucci\JWT\Signer\Key\InMemory;
use packages\adapter\persistence\inMemory\InMemoryAuthenticationAccountRepository;
use packages\domain\model\authenticationAccount\LoginRestriction;
use packages\domain\model\authenticationAccount\FailedLoginCount;
use packages\domain\model\authenticationAccount\IAuthenticationAccountRepository;
use packages\domain\model\authenticationAccount\NextLoginAllowedAt;
use packages\domain\model\authenticationAccount\UserEmail;
use packages\domain\model\authenticationAccount\UserName;
use packages\domain\model\authenticationAccount\password\UserPassword;
use packages\domain\model\authenticationAccount\AuthenticationAccount;
use packages\domain\model\authenticationAccount\LoginRestrictionStatus;
use packages\domain\model\authenticationAccount\UnsubscribeStatus;
use packages\domain\model\authenticationAccount\DefinitiveRegistrationCompletedStatus;
use packages\domain\model\definitiveRegistrationConfirmation\OneTimePassword;
use packages\domain\model\definitiveRegistrationConfirmation\OneTimeToken;
use packages\domain\service\authenticationAccount\AuthenticationAccountService;
use packages\test\helpers\domains\authenticationAccount\TestAuthenticationAccountFactory;
use packages\test\helpers\domains\authenticationAccount\AuthenticationAccountTestDataCreator;
use packages\test\helpers\domains\authenticationAccount\password\Md5PasswordManager;
use packages\test\helpers\domains\definitiveRegistrationConfirmation\TestDefinitiveRegistrationConfirmationFactory;
use PHPUnit\Framework\TestCase;

class AuthenticationAccountTest extends TestCase
{
    private InMemoryAuthenticationAccountRepository $authenticationAccountRepository;
    private TestAuthenticationAccountFactory $testAuthenticationAccountFactory;
    private AuthenticationAccountTestDataCreator $authenticationAccountTestDataCreator;

    public function setUp(): void
    {
        $this->authenticationAccountRepository = new InMemoryAuthenticationAccountRepository();
        $this->testAuthenticationAccountFactory = new TestAuthenticationAccountFactory(new Md5PasswordManager());
        $this->authenticationAccountTestDataCreator = new AuthenticationAccountTestDataCreator(
            $this->authenticationAccountRepository,
            $this->testAuthenticationAccountFactory
        );

    }

    public function test_重複したメールアドレスを持つユーザーが存在しない場合、認証アカウントを初期化できる()
    {
        // given
        // user@example.comのアドレスを持つユーザーをあらかじめ作成しておく
        $alreadyExistsUserEmail = new UserEmail('user@example.com');
        $this->authenticationAccountTestDataCreator->create($alreadyExistsUserEmail);

        $email = new UserEmail('otheruser@example.com');
        $userId = $this->authenticationAccountRepository->nextUserId();
        $password = UserPassword::create('abcABC123!',  new Md5PasswordManager());
        $authenticationAccountService = new AuthenticationAccountService($this->authenticationAccountRepository);

        // when
        $authenticationAccount = AuthenticationAccount::create(
            $userId,
            $email,
            $password,
            $authenticationAccountService
        );

        // then
        $this->assertEquals(DefinitiveRegistrationCompletedStatus::Incomplete, $authenticationAccount->definitiveRegistrationCompletedStatus());
        $this->assertEquals(LoginRestrictionStatus::Unrestricted->value, $authenticationAccount->LoginRestriction()->loginRestrictionStatus());
        $this->assertEquals(0, $authenticationAccount->LoginRestriction()->failedLoginCount());
        $this->assertEquals(null, $authenticationAccount->LoginRestriction()->nextLoginAllowedAt());

        // 以下の属性はそのまま設定される
        $this->assertEquals($email, $authenticationAccount->email());
        $this->assertEquals($userId, $authenticationAccount->id());
        $this->assertEquals($password, $authenticationAccount->password());
    }

    public function test_重複したメールアドレスを持つユーザーが既に存在する場合、認証アカウントを初期化できない()
    {
        // given
        // user@example.comのアドレスを持つユーザーをあらかじめ作成しておく
        $alreadyExistsUserEmail = new UserEmail('user@example.com');
        $this->authenticationAccountTestDataCreator->create($alreadyExistsUserEmail);

        // メールアドレスが重複している
        $email = new UserEmail('user@example.com');
        $userId = $this->authenticationAccountRepository->nextUserId();
        $password = UserPassword::create('abcABC123!',  new Md5PasswordManager());
        $authenticationAccountService = new AuthenticationAccountService($this->authenticationAccountRepository);

        // when・then
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('すでに存在するメールアドレスです。');
        AuthenticationAccount::create(
            $userId,
            $email,
            $password,
            $authenticationAccountService
        );
    }

    public function test_認証アカウントを再構築できる()
    {
        // given
        $email = new UserEmail('otheruser@example.com');
        $userId = $this->authenticationAccountRepository->nextUserId();
        $password = UserPassword::create('abcABC123!',  new Md5PasswordManager());
        $definitiveRegistrationCompletedStatus = DefinitiveRegistrationCompletedStatus::Completed;
        $LoginRestriction = LoginRestriction::initialization();

        // when
        $authenticationAccount = AuthenticationAccount::reconstruct(
            $userId,
            $email,
            $password,
            $definitiveRegistrationCompletedStatus,
            $LoginRestriction,
            UnsubscribeStatus::Subscribed
        );

        // then
        $this->assertEquals($email, $authenticationAccount->email());
        $this->assertEquals($userId, $authenticationAccount->id());
        $this->assertEquals($password, $authenticationAccount->password());
        $this->assertEquals($definitiveRegistrationCompletedStatus, $authenticationAccount->definitiveRegistrationCompletedStatus());
        $this->assertEquals($LoginRestriction, $authenticationAccount->LoginRestriction());
    }

    public function 認証ステータスを本登録済みに更新できる()
    {
        // given
        // 本登録済みステータスが未認証の認証アカウントを作成
        $definitiveRegistrationCompletedStatus = DefinitiveRegistrationCompletedStatus::Incomplete;
        $authenticationAccount = $this->testAuthenticationAccountFactory->create(
            definitiveRegistrationCompletedStatus: $definitiveRegistrationCompletedStatus
        );

        // 認証アカウントに紐づく本登録確認情報を作成
        $oneTimePassword = OneTimePassword::create();
        $definitiveRegistrationConfirmation = TestDefinitiveRegistrationConfirmationFactory::create(
            userId: $authenticationAccount->id(),
            oneTimePassword: $oneTimePassword
        );

        // when
        $authenticationAccount->updateDefinitiveRegistrationCompleted($definitiveRegistrationConfirmation, $oneTimePassword, new DateTimeImmutable());

        // then
        $this->assertEquals(DefinitiveRegistrationCompletedStatus::Completed, $authenticationAccount->definitiveRegistrationCompletedStatus());
    }

    public function test_認証アカウントに紐づいていない本登録確認情報が入力された場合に、認証ステータスを本登録済みに更新できない()
    {
        // given
        // 本登録済みステータスが未認証の認証アカウントを作成
        $definitiveRegistrationCompletedStatus = DefinitiveRegistrationCompletedStatus::Incomplete;
        $authenticationAccount = $this->testAuthenticationAccountFactory->create(
            definitiveRegistrationCompletedStatus: $definitiveRegistrationCompletedStatus
        );

        // 認証アカウントに紐づいていない本登録確認情報を作成する
        $oneTimePassword = OneTimePassword::create();
        $definitiveRegistrationConfirmation = TestDefinitiveRegistrationConfirmationFactory::create(
            oneTimePassword: $oneTimePassword
        );

        // when・then
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('認証アカウントに紐づいていない本登録確認情報です。');
        $authenticationAccount->updateDefinitiveRegistrationCompleted($definitiveRegistrationConfirmation, $oneTimePassword, new DateTimeImmutable());
    }

    public function test_正しくないワンタイムパスワードが入力された場合に、認証ステータスを本登録確認済みに更新できない()
    {
        // given
        // 本登録済みステータスが未認証の認証アカウントを作成
        $definitiveRegistrationCompletedStatus = DefinitiveRegistrationCompletedStatus::Incomplete; 
        $authenticationAccount = $this->testAuthenticationAccountFactory->create(
            definitiveRegistrationCompletedStatus: $definitiveRegistrationCompletedStatus
        );

        // 認証アカウントに紐づく本登録確認情報を作成
        $oneTimePassword = OneTimePassword::reconstruct('111111');
        $definitiveRegistrationConfirmation = TestDefinitiveRegistrationConfirmationFactory::create(
            userId: $authenticationAccount->id(),
            oneTimePassword: $oneTimePassword
        );

        // when・then
        // 不正なパスワードを入力して、認証ステータスを本登録済みに更新する
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('ワンタイムパスワードが一致しません。');
        $invalidOneTimePassword = OneTimePassword::create('222222');
        $authenticationAccount->updateDefinitiveRegistrationCompleted($definitiveRegistrationConfirmation, $invalidOneTimePassword, new DateTimeImmutable());
    }

    public function test_認証ステータスが本登録済みの場合、パスワードの変更が行える()
    {
        // given
        // 本登録済みステータスが本登録済みの認証アカウントを作成
        $definitiveRegistrationCompletedStatus = DefinitiveRegistrationCompletedStatus::Completed;
        $password = UserPassword::create('abcABC123!',  new Md5PasswordManager());
        $authenticationAccount = $this->testAuthenticationAccountFactory->create(
            password: $password,
            definitiveRegistrationCompletedStatus: $definitiveRegistrationCompletedStatus
        );

        // when
        $passwordAfterChange = UserPassword::create('abcABC123_',  new Md5PasswordManager());
        $authenticationAccount->changePassword($passwordAfterChange, new DateTimeImmutable());

        // then
        $this->assertEquals($passwordAfterChange, $authenticationAccount->password());
    }

    public function test_認証ステータスが未確認の場合、パスワードの変更が行えない()
    {
        // given
        $definitiveRegistrationCompletedStatus = DefinitiveRegistrationCompletedStatus::Incomplete;
        $password = UserPassword::create('abcABC123!',  new Md5PasswordManager());
        $authenticationAccount = $this->testAuthenticationAccountFactory->create(
            password: $password,
            definitiveRegistrationCompletedStatus: $definitiveRegistrationCompletedStatus
        );

        // when・then
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('本登録済みのユーザーではありません。');
        $passwordAfterChange = UserPassword::create('abcABC123_',  new Md5PasswordManager());
        $authenticationAccount->changePassword($passwordAfterChange, new DateTimeImmutable());
    }

    public function test_アカウントがロックされている場合、パスワードの変更が行えない()
    {
        // given
        // アカウントがロックされている認証アカウントを作成
        $definitiveRegistrationCompletedStatus = DefinitiveRegistrationCompletedStatus::Completed;
        $password = UserPassword::create('abcABC123!',  new Md5PasswordManager());
        $loginRestriction = LoginRestriction::reconstruct(
            FailedLoginCount::reconstruct(10),
            LoginRestrictionStatus::Restricted,
            NextLoginAllowedAt::reconstruct(new DateTimeImmutable('+10 minutes'))
        );
        $authenticationAccount = $this->testAuthenticationAccountFactory->create(
            password: $password,
            definitiveRegistrationCompletedStatus: $definitiveRegistrationCompletedStatus,
            loginRestriction: $loginRestriction
        );

        // when・then
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('アカウントがロックされています。');
        $passwordAfterChange = UserPassword::create('abcABC123!_',  new Md5PasswordManager());
        $authenticationAccount->changePassword($passwordAfterChange, new DateTimeImmutable());
    }

    public function test_ログイン失敗回数を更新する()
    {
        // given
        $definitiveRegistrationCompletedStatus = DefinitiveRegistrationCompletedStatus::Completed;
        // ログイン失敗回数は0回
        $LoginRestriction = LoginRestriction::reconstruct(
            FailedLoginCount::reconstruct(0),
            LoginRestrictionStatus::Unrestricted,
            null
        );
        $authenticationAccount = $this->testAuthenticationAccountFactory->create(
            definitiveRegistrationCompletedStatus: $definitiveRegistrationCompletedStatus,
            loginRestriction: $LoginRestriction
        );

        // when
        $authenticationAccount->addFailedLoginCount();

        // then
        $this->assertEquals(1, $authenticationAccount->LoginRestriction()->failedLoginCount());
    }

    public function test_認証ステータスが未確認の場合、ログイン失敗回数を更新しない()
    {
        // given
        $definitiveRegistrationCompletedStatus = DefinitiveRegistrationCompletedStatus::Incomplete;
        // ログイン失敗回数は0回
        $LoginRestriction = LoginRestriction::reconstruct(
            FailedLoginCount::reconstruct(0),
            LoginRestrictionStatus::Unrestricted,
            null
        );
        $authenticationAccount = $this->testAuthenticationAccountFactory->create(
            definitiveRegistrationCompletedStatus: $definitiveRegistrationCompletedStatus,
            loginRestriction: $LoginRestriction
        );

        // when・then
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('本登録済みのユーザーではありません。');
        $authenticationAccount->addFailedLoginCount();
    }

    public function test_ログイン制限が有効可能の場合、ログイン制限を有効にする()
    {
        // given
        // ログイン失敗回数が10回に達している認証アカウントを生成する
        $definitiveRegistrationCompletedStatus = DefinitiveRegistrationCompletedStatus::Completed;
        $LoginRestriction = LoginRestriction::reconstruct(
            FailedLoginCount::reconstruct(10),
            LoginRestrictionStatus::Unrestricted,
            null
        );
        $authenticationAccount = $this->testAuthenticationAccountFactory->create(
            definitiveRegistrationCompletedStatus: $definitiveRegistrationCompletedStatus,
            loginRestriction: $LoginRestriction
        );

        // when
        $authenticationAccount->locking(new DateTimeImmutable());

        // then
        $this->assertEquals(LoginRestrictionStatus::Restricted->value, $authenticationAccount->LoginRestriction()->loginRestrictionStatus());
        $this->assertNotNull($authenticationAccount->LoginRestriction()->nextLoginAllowedAt());
    }

    public function test_認証ステータスが未認証の場合、ログイン制限を有効にできない()
    {
        // given
        $definitiveRegistrationCompletedStatus = DefinitiveRegistrationCompletedStatus::Incomplete;
        $LoginRestriction = LoginRestriction::reconstruct(
            FailedLoginCount::reconstruct(10),
            LoginRestrictionStatus::Unrestricted,
            null
        );
        $authenticationAccount = $this->testAuthenticationAccountFactory->create(
            definitiveRegistrationCompletedStatus: $definitiveRegistrationCompletedStatus,
            loginRestriction: $LoginRestriction
        );

        // when・then
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('本登録済みのユーザーではありません。');
        $authenticationAccount->locking(new DateTimeImmutable());
    }

    public function test_ログイン制限が有効で再ログイン可能である場合はログイン制限を解除できる()
    {
        // given
        // ログイン制限は有効だが再ログインは可能である認証アカウントを生成する
        $definitiveRegistrationCompletedStatus = DefinitiveRegistrationCompletedStatus::Completed;
        $LoginRestriction = LoginRestriction::reconstruct(
            FailedLoginCount::reconstruct(10),
            LoginRestrictionStatus::Restricted,
            NextLoginAllowedAt::reconstruct(new DateTimeImmutable('-1 minutes'))
        );
        $authenticationAccount = $this->testAuthenticationAccountFactory->create(
            definitiveRegistrationCompletedStatus: $definitiveRegistrationCompletedStatus,
            loginRestriction: $LoginRestriction
        );

        // when
        $authenticationAccount->unlocking(new DateTimeImmutable());

        // then
        $this->assertEquals(LoginRestrictionStatus::Unrestricted->value, $authenticationAccount->LoginRestriction()->loginRestrictionStatus());
        $this->assertNull($authenticationAccount->LoginRestriction()->nextLoginAllowedAt());
    }

    public function test_ログイン制限が有効状態で再ログインが不可である場合、ログインができないことを判定できる()
    {
        // given
        // ログイン制限が有効状態で再ログインが不可である認証アカウントを生成する
        $definitiveRegistrationCompletedStatus = DefinitiveRegistrationCompletedStatus::Completed;
        $loginRestriction = LoginRestriction::reconstruct(
            FailedLoginCount::reconstruct(10),
            LoginRestrictionStatus::Restricted,
            NextLoginAllowedAt::reconstruct(new DateTimeImmutable('+10 minutes'))
        );
        $authenticationAccount = $this->testAuthenticationAccountFactory->create(
            definitiveRegistrationCompletedStatus: $definitiveRegistrationCompletedStatus,
            loginRestriction: $loginRestriction
        );

        // when
        $result = $authenticationAccount->canLoggedIn(new DateTimeImmutable());

        // then
        $this->assertFalse($result);
    }

    public function test_ログイン制限が有効状態で再ログインが可能である場合、ログインが可能であることを判定できる()
    {
        // given
        // ログイン制限は有効だが再ログイン可能な認証アカウントを生成する
        $definitiveRegistrationCompletedStatus = DefinitiveRegistrationCompletedStatus::Completed;
        $loginRestriction = LoginRestriction::reconstruct(
            FailedLoginCount::reconstruct(10),
            LoginRestrictionStatus::Restricted,
            NextLoginAllowedAt::reconstruct(new DateTimeImmutable('-1 minutes'))
        );
        $authenticationAccount = $this->testAuthenticationAccountFactory->create(
            definitiveRegistrationCompletedStatus: $definitiveRegistrationCompletedStatus,
            loginRestriction: $loginRestriction
        );

        // when
        $result = $authenticationAccount->canLoggedIn(new DateTimeImmutable());

        // then
        $this->assertTrue($result);
    }

    public function test_ログイン制限が有効状態ではない場合、ログインが可能であることを判定できる()
    {
        // given
        // ログイン制限が有効状態ではない認証アカウントを生成する
        $definitiveRegistrationCompletedStatus = DefinitiveRegistrationCompletedStatus::Completed;
        $loginRestriction = LoginRestriction::reconstruct(
            FailedLoginCount::reconstruct(9),
            LoginRestrictionStatus::Unrestricted,
            null
        );
        $authenticationAccount = $this->testAuthenticationAccountFactory->create(
            definitiveRegistrationCompletedStatus: $definitiveRegistrationCompletedStatus,
            loginRestriction: $loginRestriction
        );

        // when
        $result = $authenticationAccount->canLoggedIn(new DateTimeImmutable());

        // then
        $this->assertTrue($result);
    }

    public function test_認証ステータスが未認証の場合、ログイン不可であることを判定できる()
    {
        // given
        // 認証ステータスが未認証の認証アカウントを生成する
        $definitiveRegistrationCompletedStatus = DefinitiveRegistrationCompletedStatus::Incomplete;
        $authenticationAccount = $this->testAuthenticationAccountFactory->create(
            definitiveRegistrationCompletedStatus: $definitiveRegistrationCompletedStatus
        );

        // when
        $result = $authenticationAccount->canLoggedIn(new DateTimeImmutable());

        // then
        $this->assertFalse($result);
    }

    public function test_ログイン制限を有効にできるかどうかを判定できる()
    {
        // given
        // ログイン失敗回数が10回に達していている認証アカウントを生成する
        $definitiveRegistrationCompletedStatus = DefinitiveRegistrationCompletedStatus::Completed;
        $loginRestriction = LoginRestriction::reconstruct(
            FailedLoginCount::reconstruct(10),
            LoginRestrictionStatus::Unrestricted,
            null
        );
        $authenticationAccount = $this->testAuthenticationAccountFactory->create(
            definitiveRegistrationCompletedStatus: $definitiveRegistrationCompletedStatus,
            loginRestriction: $loginRestriction
        );

        // when
        $result = $authenticationAccount->canLocking(new DateTimeImmutable());

        // then
        $this->assertTrue($result);
    }

    public function test_ログイン制限を有効にできないことを判定できる()
    {
        // given
        // ログイン失敗回数が10回に達していない認証アカウントを生成する
        $definitiveRegistrationCompletedStatus = DefinitiveRegistrationCompletedStatus::Completed;
        $loginRestriction = LoginRestriction::reconstruct(
            FailedLoginCount::reconstruct(9),
            LoginRestrictionStatus::Unrestricted,
            null
        );
        $authenticationAccount = $this->testAuthenticationAccountFactory->create(
            definitiveRegistrationCompletedStatus: $definitiveRegistrationCompletedStatus,
            loginRestriction: $loginRestriction
        );

        // when
        $result = $authenticationAccount->canLocking(new DateTimeImmutable());

        // then
        $this->assertFalse($result);
    }
}