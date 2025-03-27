<?php

namespace packages\application\login;

use packages\domain\model\auth\AOneTimeTokenSessionService;
use packages\domain\model\auth\IAuthCodeFetcher;
use packages\domain\model\auth\IAuthorizationRequestUrlBuildService;
use packages\domain\service\auth\AuthorizationRequestUrlBuilder;
use packages\domain\service\auth\LoginUrlCreator;

class LoginApplicationService
{
    private AOneTimeTokenSessionService $oneTimeTokenSessionService;
    private LoginUrlCreator $loginUrlCreator;

    public function __construct(
        AOneTimeTokenSessionService $oneTimeTokenSessionService,
        LoginUrlCreator $loginUrlCreator
    )
    {
        $this->oneTimeTokenSessionService = $oneTimeTokenSessionService;
        $this->loginUrlCreator = $loginUrlCreator;
    }

    public function createLoginUrl(): string
    {
        return $this->loginUrlCreator->createLoginUrl();
    }
}