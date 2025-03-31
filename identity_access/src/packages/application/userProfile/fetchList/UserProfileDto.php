<?php

namespace packages\application\userProfile\fetchList;

class UserProfileDto
{
    readonly string $userId;
    readonly string $userName;
    readonly string $selfIntroductionText;
    readonly string $email;

    public function __construct(
        string $userId,
        string $userName,
        string $selfIntroductionText,
        string $email
    )
    {
        $this->userId = $userId;
        $this->userName = $userName;
        $this->selfIntroductionText = $selfIntroductionText;
        $this->email = $email;
    }
}