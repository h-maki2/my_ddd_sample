<?php

namespace packages\application\authenticationAccount\fetch;

use DomainException;
use packages\domain\model\authenticationAccount\IAuthenticationAccountRepository;
use packages\domain\model\oauth\scope\Scope;
use packages\domain\service\oauth\LoggedInUserIdFetcher;

class FetchAuthenticationAccountApplicationService
{
    private IAuthenticationAccountRepository $authenticationAccountRepository;
    private LoggedInUserIdFetcher $loggedInUserIdFetcher;

    public function __construct(
        IAuthenticationAccountRepository $authenticationAccountRepository,
        LoggedInUserIdFetcher $loggedInUserIdFetcher
    ) {
        $this->authenticationAccountRepository = $authenticationAccountRepository;
        $this->loggedInUserIdFetcher = $loggedInUserIdFetcher;
    }

    public function handle(string $scopeString): FetchAuthenticationAccountResult
    {
        $userId = $this->loggedInUserIdFetcher->fetch(Scope::from($scopeString));
        $authenticationAccount = $this->authenticationAccountRepository->findById($userId);

        if ($authenticationAccount === null) {
            throw new DomainException('アカウントが退会済みです。');
        }

        return new FetchAuthenticationAccountResult(
            $authenticationAccount->id()->value,
            $authenticationAccount->email()->value
        );
    }
}