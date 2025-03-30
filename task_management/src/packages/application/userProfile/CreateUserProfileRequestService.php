<?php

namespace packages\application\userProfile;

use packages\domain\model\authToken\AccessToken;

interface CreateUserProfileRequestService
{
    public function send(
        AccessToken $accessToken,
        string $name,
        string $selfIntroductionText
    ): CreateUserProfileResponseData;
}