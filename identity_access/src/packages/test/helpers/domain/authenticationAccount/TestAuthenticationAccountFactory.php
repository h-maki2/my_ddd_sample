<?php

namespace packages\test\helpers\domain\authenticationAccount;

use packages\adapter\persistence\inMemory\InMemoryAuthenticationAccountRepository;
use packages\domain\model\authenticationAccount\LoginRestriction;
use packages\domain\model\authenticationAccount\UserEmail;
use packages\domain\model\authenticationAccount\UserId;
use packages\domain\model\authenticationAccount\password\UserPassword;
use packages\domain\model\authenticationAccount\AuthenticationAccount;
use packages\domain\model\authenticationAccount\UnsubscribeStatus;
use packages\domain\model\authenticationAccount\DefinitiveRegistrationCompletedStatus;
use packages\domain\model\authenticationAccount\password\IPasswordManager;
use packages\test\helpers\domain\authenticationAccount\password\Md5PasswordManager;

class TestAuthenticationAccountFactory
{
    private IPasswordManager $passwordManager;

    public function __construct(IPasswordManager $passwordManager)
    {
        $this->passwordManager = $passwordManager;
    }

    public function create(
        ?UserEmail $email = null,
        ?UserPassword $password = null,
        ?DefinitiveRegistrationCompletedStatus $definitiveRegistrationCompletedStatus = null,
        ?UserId $id = null,
        ?LoginRestriction $loginRestriction = null,
        ?UnsubscribeStatus $unsubscribeStatus = null
    ): AuthenticationAccount
    {
        $authInfoRepository = new InMemoryAuthenticationAccountRepository();
        return AuthenticationAccount::reconstruct(
            $id ?? $authInfoRepository->nextUserId(),
            $email ?? TestUserEmailFactory::create(),
            $password ?? UserPassword::create('acbABC123!', $this->passwordManager),
            $definitiveRegistrationCompletedStatus ?? DefinitiveRegistrationCompletedStatus::Completed,
            $loginRestriction ?? LoginRestriction::initialization(),
            $unsubscribeStatus ?? UnsubscribeStatus::Subscribed
        );
    }
}