<?php

use Lcobucci\JWT\Signer\Key\InMemory;
use packages\adapter\persistence\inMemory\InMemoryAuthenticationAccountRepository;
use packages\adapter\persistence\inMemory\InMemoryUserProfileRepository;
use packages\application\userProfile\fetch\FetchUserProfileApplicationService;
use packages\application\userProfile\fetch\fetchLoggedInUserProfile\FetchLoggedInUserProfileResult;
use packages\domain\model\authenticationAccount\IAuthenticationAccountRepository;
use packages\domain\model\authenticationAccount\UserEmail;
use packages\domain\model\oauth\scope\IScopeAuthorizationChecker;
use packages\domain\model\oauth\scope\Scope;
use packages\domain\model\userProfile\IUserProfileRepository;
use packages\domain\model\userProfile\SelfIntroductionText;
use packages\domain\model\userProfile\UserName;
use packages\domain\service\authenticationAccount\AuthenticationService;
use packages\test\helpers\domain\authenticationAccount\AuthenticationAccountTestDataCreator;
use packages\test\helpers\domain\authenticationAccount\password\Md5PasswordManager;
use packages\test\helpers\domain\authenticationAccount\TestAuthenticationAccountFactory;
use packages\test\helpers\domain\authenticationAccount\TestUserIdFactory;
use packages\test\helpers\domain\userProfile\UserProfileTestDataCreator;
use PHPUnit\Framework\TestCase;

class FetchUserProfileApplicationServiceTest extends TestCase
{
    private InMemoryAuthenticationAccountRepository $authenticationAccountRepository;
    private InMemoryUserProfileRepository $userProfileRepository;
    private AuthenticationAccountTestDataCreator $authenticationAccountTestDataCreator;
    private UserProfileTestDataCreator $userProfileTestDataCreator;
    private FetchUserProfileApplicationService $fetchUserProfileApplicationService;

    public function setUp(): void
    {
        $this->authenticationAccountRepository = new InMemoryAuthenticationAccountRepository();
        $this->userProfileRepository = new InMemoryUserProfileRepository();
        $this->authenticationAccountTestDataCreator = new AuthenticationAccountTestDataCreator(
            $this->authenticationAccountRepository,
            new TestAuthenticationAccountFactory(new Md5PasswordManager())
        );
        $this->userProfileTestDataCreator = new UserProfileTestDataCreator(
            $this->userProfileRepository,
            $this->authenticationAccountRepository
        );

        $scopeAuthorizationChecker = $this->createMock(IScopeAuthorizationChecker::class);
        $scopeAuthorizationChecker
            ->method('isAuthorized')
            ->willReturn(true);
        
        $authenticationService = $this->createMock(AuthenticationService::class);
        $authenticationService
            ->method('loggedInUserId')
            ->willReturn(TestUserIdFactory::createUserId());

        $this->fetchUserProfileApplicationService = new FetchUserProfileApplicationService(
            $this->userProfileRepository,
            $authenticationService,
            $scopeAuthorizationChecker,
            $this->authenticationAccountRepository
        );
    }

    public function test_ユーザープロフィール情報を取得する()
    {
        // given
        // アカウントを作成する
        $userId = TestUserIdFactory::createUserId();
        $userEmail = new UserEmail('test@test.com');
        $this->authenticationAccountTestDataCreator->create(
            id: $userId,
            email: $userEmail
        );

        // ユーザープロフィールを作成する
        $name = new UserName('テスト太郎');
        $selfIntroductionText = new SelfIntroductionText('よろしくお願いします。');
        $this->userProfileTestDataCreator->create(
            $userId,
            $name,
            $selfIntroductionText
        );

        $scopeString = Scope::ReadAccount->value;

        // when
        $result = $this->fetchUserProfileApplicationService->fetchLoggedInUserProfile($scopeString);

        // then
        $this->assertEquals($name->value, $result->userName);
        $this->assertEquals($selfIntroductionText->value, $result->selfIntroductionText);
        $this->assertEquals($userEmail->value, $result->email);
        $this->assertEquals($userId->value, $result->userId);
        $this->assertTrue($result->isExistsUserProfile);
    }

    public function test_ユーザープロフィールが存在しない場合は空の情報を返す()
    {
        // アカウントを作成する
        $userId = TestUserIdFactory::createUserId();
        $userEmail = new UserEmail('test@test.com');
        $this->authenticationAccountTestDataCreator->create(
            id: $userId,
            email: $userEmail
        );

        $scopeString = Scope::ReadAccount->value;

        // when
        $result = $this->fetchUserProfileApplicationService->fetchLoggedInUserProfile($scopeString);

        // then
        $this->assertEquals('', $result->userName);
        $this->assertEquals('', $result->selfIntroductionText);
        $this->assertEquals('', $result->email);
        $this->assertEquals('', $result->userId);
        $this->assertFalse($result->isExistsUserProfile);
    }
}