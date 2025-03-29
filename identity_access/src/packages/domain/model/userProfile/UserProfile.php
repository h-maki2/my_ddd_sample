<?php

namespace packages\domain\model\userProfile;

use DomainException;
use packages\domain\model\authenticationAccount\UserId;
use packages\domain\service\userProfile\UserProfileService;

class UserProfile
{
    private UserId $userId;
    private UserName $userName;
    private SelfIntroductionText $selfIntroductionText;

    private function __construct(
        UserId $userId, 
        UserName $userName, 
        SelfIntroductionText $selfIntroductionText
    )
    {
        $this->userId = $userId;
        $this->userName = $userName;
        $this->selfIntroductionText = $selfIntroductionText;
    }

    public static function create(
        UserId $userId, 
        UserName $userName, 
        SelfIntroductionText $selfIntroductionText,
        UserProfileService $userProfileService
    ): self
    {
        if ($userProfileService->isExists($userId)) {
            throw new DomainException('ユーザープロフィールが既に存在します。userId: ' . $userId->value);
        }

        return new self($userId, $userName, $selfIntroductionText);
    }

    public static function reconstruct(
        UserId $userId, 
        UserName $userName, 
        SelfIntroductionText $selfIntroductionText
    ): self
    {
        return new self($userId, $userName, $selfIntroductionText);
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