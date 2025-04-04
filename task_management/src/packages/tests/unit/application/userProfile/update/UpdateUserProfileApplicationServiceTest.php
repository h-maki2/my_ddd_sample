<?php

use App\Models\UserProfile;
use packages\application\common\validation\ValidationErrorMessageData;
use packages\application\userProfile\update\UpdateUserProfileApplicationService;
use packages\application\userProfile\update\UpdateUserProfileResult;
use packages\domain\model\authToken\AAuthTokenStore;
use packages\domain\model\authToken\IAuthTokenService;
use packages\domain\model\userProfile\SelfIntroductionText;
use packages\domain\model\userProfile\userAccount\IUserAccountService;
use packages\domain\model\userProfile\UserName;
use packages\domain\model\userProfile\UserProfileNotExistsException;
use packages\port\adapter\persistence\inMemory\InMemoryUserProfileRepository;
use packages\tests\helper\domain\model\authToken\TestAuthTokenFactory;
use packages\tests\helper\domain\model\userProfile\userAccount\TestUserAccountFactory;
use packages\tests\helper\domain\model\userProfile\UserProfileTestDataCreator;
use PHPUnit\Framework\TestCase;

class UpdateUserProfileApplicationServiceTest extends TestCase
{
    private InMemoryUserProfileRepository $userProfileRepository;
    private UpdateUserProfileApplicationService $updateUserProfileApplicationService;
    private UserProfileTestDataCreator $userProfileTestDataCreator;

    public function setUp(): void
    {
        $this->userProfileRepository = new InMemoryUserProfileRepository();

        $this->userProfileTestDataCreator = new UserProfileTestDataCreator($this->userProfileRepository);

        $authToken = TestAuthTokenFactory::create();
        $authTokenStore = $this->createMock(AAuthTokenStore::class);
        $authTokenStore
            ->method('get')
            ->willReturn($authToken);
        
        $authTokenService = $this->createMock(IAuthTokenService::class);

        $userAccountService = $this->createMock(IUserAccountService::class);
        $userAccountService
            ->method('userAccountFrom')
            ->with($authToken->accessToken) 
            ->willReturn(TestUserAccountFactory::create());

        
        $this->updateUserProfileApplicationService = new UpdateUserProfileApplicationService(
            $this->userProfileRepository,
            $authTokenStore,
            $authTokenService,
            $userAccountService,
        );
    }

    public function test_正常な名前と自己紹介文が入力された場合に、ユーザープロフィールを更新できる()
    {
        // given
        // ユーザープロフィールを作成して保存する
        $userAccount = TestUserAccountFactory::create();
        $this->userProfileTestDataCreator->create(
            $userAccount->userId,
            $userAccount->userEmail,
            new UserName('変更前のユーザー名'),
            new SelfIntroductionText('変更前の自己紹介文')
        );

        $userName = '変更後のユーザー名';
        $selfIntroductionText = '変更後の自己紹介文';

        // when
        $actualResult = $this->updateUserProfileApplicationService->update(
            $userName,
            $selfIntroductionText
        );

        // then
        $this->assertTrue($actualResult->isSuccess);

        // ユーザープロフィールが更新されていることを確認
        $userProfile = $this->userProfileRepository->findById($userAccount->userId);
        $this->assertEquals($userName, $userProfile->name()->value);
        $this->assertEquals($selfIntroductionText, $userProfile->selfIntroductionText()->value);

        // 下記のデータは更新されていないことを確認
        $this->assertEquals($userAccount->userEmail, $userProfile->userEmail());
        $this->assertEquals($userAccount->userId, $userProfile->userId());
    }

    public function test_無効なユーザー名と自己紹介が入力された場合に、バリデーションエラーが発生する()
    {
        // given
        // ユーザープロフィールを作成して保存する
        $userAccount = TestUserAccountFactory::create();
        $変更前のユーザープロフィール = $this->userProfileTestDataCreator->create(
            $userAccount->userId,
            $userAccount->userEmail,
            new UserName('変更前のユーザー名'),
            new SelfIntroductionText('変更前の自己紹介文')
        );

        // 無効なユーザー名と自己紹介文を作成
        $不適切なユーザー名 = '';
        $不適切な自己紹介文 = str_repeat('a', 501);

        // when
        $actualResult = $this->updateUserProfileApplicationService->update(
            $不適切なユーザー名,
            $不適切な自己紹介文
        );

        // then
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
        $expectedResult = UpdateUserProfileResult::createWhenFailure($expectedValidationErrorMessages);
        $this->assertEquals($expectedResult, $actualResult);

        // ユーザープロフィールが更新されていないことを確認
        $actualUserProfile = $this->userProfileRepository->findById($userAccount->userId);
        $this->assertEquals($変更前のユーザープロフィール, $actualUserProfile);
    }

    public function test_ユーザープロフィール更新の際に、ユーザープロフィールが存在しない場合は例外が発生する()
    {
        // given
        $userName = '変更後のユーザー名';
        $selfIntroductionText = '変更後の自己紹介文';

        // when・then
        $this->expectException(UserProfileNotExistsException::class);
        $this->expectExceptionMessage('ユーザープロフィールが見つかりません');
        $this->updateUserProfileApplicationService->update(
            $userName,
            $selfIntroductionText
        );
    }
}