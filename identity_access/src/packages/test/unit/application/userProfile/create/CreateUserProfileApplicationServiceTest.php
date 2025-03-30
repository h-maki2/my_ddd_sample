<?php

use packages\adapter\persistence\inMemory\InMemoryAuthenticationAccountRepository;
use packages\adapter\persistence\inMemory\InMemoryUserProfileRepository;
use packages\application\common\validation\ValidationErrorMessageData;
use packages\application\userProfile\create\CreateUserProfileApplicationService;
use packages\application\userProfile\create\CreateUserProfileResult;
use packages\domain\model\oauth\scope\IScopeAuthorizationChecker;
use packages\domain\model\oauth\scope\Scope;
use packages\domain\service\authenticationAccount\AuthenticationService;
use packages\domain\service\oauth\LoggedInUserIdFetcher;
use packages\test\helpers\domain\authenticationAccount\AuthenticationAccountTestDataCreator;
use packages\test\helpers\domain\authenticationAccount\password\Md5PasswordManager;
use packages\test\helpers\domain\authenticationAccount\TestAuthenticationAccountFactory;
use packages\test\helpers\domain\authenticationAccount\TestUserIdFactory;
use PHPUnit\Framework\TestCase;

class CreateUserProfileApplicationServiceTest extends TestCase
{
    private InMemoryUserProfileRepository $userProfileRepository;
    private AuthenticationAccountTestDataCreator $authenticationAccountTestDataCreator;
    private CreateUserProfileApplicationService $createUserProfileApplicationService;

    public function setUp(): void
    {
        $authenticationService = $this->createMock(AuthenticationService::class);
        $expectedUserId = TestUserIdFactory::createUserId();
        $authenticationService->method('loggedInUserId')
            ->willReturn($expectedUserId);
        
        $scopeAuthorizationChecker = $this->createMock(IScopeAuthorizationChecker::class);
        $scopeAuthorizationChecker->method('isAuthorized')
            ->willReturn(true);
        
        $this->userProfileRepository = new InMemoryUserProfileRepository();

        $this->authenticationAccountTestDataCreator = new AuthenticationAccountTestDataCreator(
            new InMemoryAuthenticationAccountRepository(),
            new TestAuthenticationAccountFactory(
                new Md5PasswordManager()
            )
        );

        $this->createUserProfileApplicationService = new CreateUserProfileApplicationService(
            $this->userProfileRepository,
            $authenticationService,
            $scopeAuthorizationChecker
        );
    }

    public function test_適切なユーザー名と自己紹介文が入力された場合に、ユーザープロフィールを作成できる()
    {
        // given
        // アカウントを作成しておく
        $userId = TestUserIdFactory::createUserId();
        $this->authenticationAccountTestDataCreator->create(
            id: $userId
        );

        $userName = 'テストユーザー';
        $selfIntroductionText = 'テスト自己紹介文';
        $scope = Scope::EditAccount;

        // when
        $actualResult = $this->createUserProfileApplicationService->create(
            $userName,
            $selfIntroductionText,
            $scope->value
        );

        // then
        // ユーザープロフィールの作成が成功していることを確認
        $expectedResult = CreateUserProfileResult::createWhenSuccess();
        $this->assertEquals($expectedResult, $actualResult);

        // ユーザープロフィールが保存されていることを確認
        $expectedUserProfile = $this->userProfileRepository->findById($userId);
        $this->assertEquals($userName, $expectedUserProfile->name()->value);
        $this->assertEquals($selfIntroductionText, $expectedUserProfile->selfIntroductionText()->value);
    }

    public function test_不適切なユーザー名と自己紹介文が入力された場合に、ユーザープロフィールの作成に失敗する()
    {
        // given
        // アカウントを作成しておく
        $userId = TestUserIdFactory::createUserId();
        $this->authenticationAccountTestDataCreator->create(
            id: $userId
        );

        $不適切なユーザー名 = '';
        $不適切な自己紹介文 = str_repeat('a', 501);
        $scope = Scope::EditAccount;

        // when
        $actualResult = $this->createUserProfileApplicationService->create(
            $不適切なユーザー名,
            $不適切な自己紹介文,
            $scope->value
        );

        // then
        // ユーザープロフィールの作成が失敗していることを確認
        $expectedValidationErrorMessages = [
            new ValidationErrorMessageData(
                'userName',
                ['ユーザー名は1文字以上50文字以内で入力してください。'],
            ),
            new ValidationErrorMessageData(
                'selfIntroductionText',
                ['自己紹介文は500文字以内で入力してください。'],
            ),
        ];
        $expectedResult = CreateUserProfileResult::createWhenFailure($expectedValidationErrorMessages);
        $this->assertEquals($expectedResult, $actualResult);

        // ユーザープロフィールが保存されていないことを確認
        $expectedUserProfile = $this->userProfileRepository->findById($userId);
        $this->assertNull($expectedUserProfile);
    }
}