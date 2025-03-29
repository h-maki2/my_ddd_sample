<?php

use Lcobucci\JWT\Signer\Key\InMemory;
use packages\adapter\persistence\inMemory\InMemoryAuthenticationAccountRepository;
use packages\adapter\persistence\inMemory\InMemoryUserProfileRepository;
use packages\domain\model\userProfile\SelfIntroductionText;
use packages\domain\model\userProfile\UserName;
use packages\domain\model\userProfile\UserProfile;
use packages\domain\service\userProfile\UserProfileService;
use packages\test\helpers\domain\authenticationAccount\AuthenticationAccountTestDataCreator;
use packages\test\helpers\domain\authenticationAccount\password\Md5PasswordManager;
use packages\test\helpers\domain\authenticationAccount\TestAuthenticationAccountFactory;
use packages\test\helpers\domain\userProfile\UserProfileTestDataCreator;
use PHPUnit\Framework\TestCase;

class UserProfileTest extends TestCase
{
    private AuthenticationAccountTestDataCreator $authAccountTestDataCreator;
    private UserProfileTestDataCreator $userProfileTestDataCreator;
    private InMemoryAuthenticationAccountRepository $authenticationAccountRepository;
    private InMemoryUserProfileRepository $userProfileRepository;

    protected function setUp(): void
    {
        $this->authenticationAccountRepository = new InMemoryAuthenticationAccountRepository();
        $this->authAccountTestDataCreator = new AuthenticationAccountTestDataCreator(
            $this->authenticationAccountRepository,
            new TestAuthenticationAccountFactory(new Md5PasswordManager())
        );

        $this->userProfileRepository = new InMemoryUserProfileRepository();
        $this->userProfileTestDataCreator = new UserProfileTestDataCreator(
            $this->userProfileRepository,
            $this->authenticationAccountRepository
        );
    }

    public function test_ユーザープロフィールが既に登録済みの場合に、createメソッドでインスタンスを生成すると例外が発生する()
    {
        // given
        // アカウントを登録しておく
        $userId = $this->authenticationAccountRepository->nextUserId();
        $this->authAccountTestDataCreator->create(
            id: $userId
        );

        // ユーザープロフィールを登録しておく
        $this->userProfileTestDataCreator->create(
            userId: $userId
        );

        // when・then
        // ユーザープロフィールを作成する
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('ユーザープロフィールが既に存在します。userId: ' . $userId->value);
        UserProfile::create(
            $userId,
            new UserName('test-user-name'),
            new SelfIntroductionText('test-self-introduction-text'),
            new UserProfileService(
                $this->userProfileRepository
            )
        );
    }
}