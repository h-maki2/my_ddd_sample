<?php

namespace packages\test\helpers\domain\authenticationAccount;

use packages\domain\model\authenticationAccount\AuthenticationAccountCreated;
use packages\domain\model\authenticationAccount\IAuthenticationAccountRepository;
use packages\domain\model\authenticationAccount\UserEmail;
use packages\domain\model\authenticationAccount\UserId;
use packages\test\helpers\domain\authenticationAccount\password\Md5PasswordManager;

class TestAuthenticationAccountCreatedFactory
{
    private IAuthenticationAccountRepository $authenticationAccountRepository;
    private TestAuthenticationAccountFactory $testAuthenticationAccountFactory;

    public function __construct(
        IAuthenticationAccountRepository $authenticationAccountRepository,
        TestAuthenticationAccountFactory $testAuthenticationAccountFactory
    )
    {
        $this->authenticationAccountRepository = $authenticationAccountRepository;
        $this->testAuthenticationAccountFactory = $testAuthenticationAccountFactory;
    }

    public function create(
        ?UserId $userId = null,
        ?UserEmail $email = null,
    ): AuthenticationAccountCreated
    {

        $authenticationAccountTestDataCreator = new AuthenticationAccountTestDataCreator(
            $this->authenticationAccountRepository,
            $this->testAuthenticationAccountFactory
        );
        $authenticationAccount = $authenticationAccountTestDataCreator->create(
            email: $email ?? new UserEmail('test@example.com'),
            id: $userId ?? $this->authenticationAccountRepository->nextUserId()
        );

        return new AuthenticationAccountCreated(
            $authenticationAccount->id(),
            $authenticationAccount->email()
        );
    }
}