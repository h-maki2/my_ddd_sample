<?php

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

        // ユーザープロフィールを登録する
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

    public function test_ユーザー名を変更する()
    {
        // given
        // アカウントを登録しておく
        $userId = $this->authenticationAccountRepository->nextUserId();
        $this->authAccountTestDataCreator->create(
            id: $userId
        );

        // ユーザープロフィールを登録する
        $変更前のユーザー名 = new UserName('変更前のユーザー名');
        $selfIntroductionText = new SelfIntroductionText('test-self-introduction-text');
        $userProfile = $this->userProfileTestDataCreator->create(
            userId: $userId,
            userName: $変更前のユーザー名,
            selfIntroductionText: $selfIntroductionText
        );

        // when
        // ユーザープロフィールを変更する
        $変更後のユーザー名 = new UserName('変更後のユーザー名');
        $userProfile->changeName($変更後のユーザー名);
        $this->userProfileRepository->save($userProfile);

        // then
        // ユーザープロフィールが変更されていることを確認する
        $userProfile = $this->userProfileRepository->findById($userId);
        $this->assertEquals(
            $変更後のユーザー名,
            $userProfile->name()
        );

        // ユーザー名以外は変更されていないことを確認する
        $this->assertEquals(
            $selfIntroductionText,
            $userProfile->selfIntroductionText()
        );
    }

    public function test_自己紹介文を変更する()
    {
        // given
        // アカウントを登録しておく
        $userId = $this->authenticationAccountRepository->nextUserId();
        $this->authAccountTestDataCreator->create(
            id: $userId
        );

        // ユーザープロフィールを登録する
        $変更前の自己紹介文 = new SelfIntroductionText('変更前の自己紹介文');
        $userName = new UserName('test-user-name');
        $userProfile = $this->userProfileTestDataCreator->create(
            userId: $userId,
            selfIntroductionText: $変更前の自己紹介文,
            userName: $userName
        );

        // when
        // ユーザープロフィールを変更する
        $変更後の自己紹介文 = new SelfIntroductionText('変更後の自己紹介文');
        $userProfile->changeSelfIntroductionText($変更後の自己紹介文);
        $this->userProfileRepository->save($userProfile);

        // then
        // ユーザープロフィールが変更されていることを確認する
        $userProfile = $this->userProfileRepository->findById($userId);
        $this->assertEquals(
            $変更後の自己紹介文,
            $userProfile->selfIntroductionText()
        );

        // 自己紹介文以外は変更されていないことを確認する
        $this->assertEquals(
            $userName,
            $userProfile->name()
        );
    }
}