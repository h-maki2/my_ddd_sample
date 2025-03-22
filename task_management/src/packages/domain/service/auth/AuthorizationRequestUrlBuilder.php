<?php

namespace packages\domain\service\auth;

use packages\domain\model\auth\AOneTimeTokenSessionService;
use packages\domain\model\auth\IAuthorizationRequestUrlBuildService;
use packages\domain\model\auth\OneTimeToken;

class AuthorizationRequestUrlBuilder
{
    private AOneTimeTokenSessionService $oneTimeTokenSessionService;
    private IAuthorizationRequestUrlBuildService $authorizationRequestUrlBuildService;

    public function __construct(
        AOneTimeTokenSessionService $oneTimeTokenSessionService,
        IAuthorizationRequestUrlBuildService $authorizationRequestUrlBuildService
    ) {
        $this->oneTimeTokenSessionService = $oneTimeTokenSessionService;
        $this->authorizationRequestUrlBuildService = $authorizationRequestUrlBuildService;
    }

    /**
     * 認可リクエスト用のURLを生成
     */
    public function build(string $email, string $password): ?string
    {
        $oneTimeToken = OneTimeToken::create();
        $this->oneTimeTokenSessionService->save(
            $oneTimeToken
        );

        return $this->authorizationRequestUrlBuildService->build(
            $email,
            $password,
            $oneTimeToken->value
        );
    }
}