<?php

namespace packages\application\userProfile\update;

class DisplayUpdateUserProfileFormResult
{
    readonly string $userName;
    readonly string $selfIntroductionText;

    public function __construct(string $userName, string $selfIntroductionText)
    {
        $this->userName = $userName;
        $this->selfIntroductionText = $selfIntroductionText;
    }
}