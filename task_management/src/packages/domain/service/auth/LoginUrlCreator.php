<?php

namespace packages\domain\service\auth;

use packages\domain\model\auth\AOneTimeTokenSessionService;
use packages\domain\model\auth\IAuthorizationRequestUrlBuildService;
use packages\domain\model\auth\OneTimeToken;
use packages\domain\model\auth\Scope;

abstract class LoginUrlCreator
{
    protected AOneTimeTokenSessionService $oneTimeTokenSessionService;

    protected const RESPONSE_TYPE = 'code';

    public function __construct(
        AOneTimeTokenSessionService $oneTimeTokenSessionService,
    ) {
        $this->oneTimeTokenSessionService = $oneTimeTokenSessionService;
    }

    public function createLoginUrl(): string
    {
        return $this->baseLoginUrl() . '?' . http_build_query([
            'client_id' => $this->clientId(),
            'redirect_uri' => $this->redirectUrl(),
            'response_type' => self::RESPONSE_TYPE,
            'state' => $this->state(),
            'scope' => $this->scope(),
        ]);
    }

    abstract protected function baseLoginUrl(): string;

    abstract protected function clientId(): string;

    abstract protected function redirectUrl(): string;

    protected function state(): string
    {
        $oneTimeToken = OneTimeToken::create();
        $this->oneTimeTokenSessionService->save($oneTimeToken);
        return $oneTimeToken->value;
    }

    protected function scope(): string
    {
        return Scope::ReadAccount->value . ' ' . Scope::EditAccount->value . ' ' . Scope::DeleteAccount->value;
    }
}