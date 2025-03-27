<?php

namespace packages\application\login;

use packages\domain\model\auth\AOneTimeTokenSessionService;
use packages\domain\model\auth\AuthenticationException;
use packages\domain\model\auth\IAuthCodeFetcher;
use packages\domain\model\auth\IAuthorizationRequestUrlBuildService;
use packages\domain\model\auth\OneTimeToken;
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

    public function login(string $authCode, string $oneTimeToken)
    {
        $oneTimeToken = OneTimeToken::reconstruct($oneTimeToken);
        $otherOneTimeToken = $this->oneTimeTokenSessionService->get();
        if (!$oneTimeToken->equals($otherOneTimeToken)) {
            throw new AuthenticationException('oneTimeTokenが一致しません。');
        }
    }
}