<?php

namespace packages\application\userProfile\fetch\fetchLoggedInUserProfile;

class FetchLoggedInUserProfileResult
{
    readonly string $userId;
    readonly string $userName;
    readonly string $selfIntroductionText;
    readonly string $email;
    readonly bool $isExistsUserProfile;

    private function __construct(
        string $userId, 
        string $userName, 
        string $selfIntroductionText,
        string $email,
        bool $isExistsUserProfile
    )
    {
        $this->userId = $userId;
        $this->userName = $userName;
        $this->selfIntroductionText = $selfIntroductionText;
        $this->email = $email;
        $this->isExistsUserProfile = $isExistsUserProfile;
    }

    public static function createWhenNotFound(): self
    {
        return new self('', '', '', '', false);
    }

    public static function createWhenFound(
        string $userId, 
        string $userName, 
        string $selfIntroductionText,
        string $email
    ): self
    {
        return new self($userId, $userName, $selfIntroductionText, $email, true);
    }
}