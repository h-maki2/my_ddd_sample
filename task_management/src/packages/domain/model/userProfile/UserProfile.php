<?php

namespace packages\domain\model\userProfile;

use DateTimeImmutable;
use DomainException;
use packages\domain\model\userProfile\userAccount\UserAccountData;
use packages\domain\model\userProfile\userAccount\UserEmail;
use packages\domain\model\userProfile\userAccount\UserId;
use packages\domain\service\userProfile\UserProfileService;

class UserProfile
{
    private UserId $userId;
    private UserName $userName;
    private SelfIntroductionText $selfIntroductionText;
    private UserEmail $userEmail;

    private function __construct(
        UserId $userId, 
        UserName $userName, 
        SelfIntroductionText $selfIntroductionText,
        UserEmail $userEmail
    )
    {
        $this->userId = $userId;
        $this->userName = $userName;
        $this->selfIntroductionText = $selfIntroductionText;
        $this->userEmail = $userEmail;
    }

    public static function create(
        UserAccountData $userAccountData,
        UserName $userName, 
        SelfIntroductionText $selfIntroductionText,
        UserProfileService $userProfileService
    ): self
    {
        $userId = $userAccountData->userId;
        if ($userProfileService->isExists($userId)) {
            throw new DomainException('ユーザープロフィールが既に存在します。userId: ' . $userId->value);
        }

        return new self($userId, $userName, $selfIntroductionText, $userAccountData->userEmail);
    }

    public static function reconstruct(
        UserId $userId, 
        UserName $userName, 
        SelfIntroductionText $selfIntroductionText,
        UserEmail $userEmail
    ): self
    {
        return new self($userId, $userName, $selfIntroductionText, $userEmail);
    }

    public function userId(): UserId
    {
        return $this->userId;
    }

    public function name(): UserName
    {
        return $this->userName;
    }

    public function selfIntroductionText(): SelfIntroductionText
    {
        return $this->selfIntroductionText;
    }

    public function userEmail(): UserEmail
    {
        return $this->userEmail;
    }

    /**
     * ユーザー名を変更する
     */
    public function changeName(UserName $userName): void
    {
        $this->userName = $userName;
    }

    /**
     * 自己紹介文を変更する
     */
    public function changeSelfIntroductionText(SelfIntroductionText $selfIntroductionText): void
    {
        $this->selfIntroductionText = $selfIntroductionText;
    }
}