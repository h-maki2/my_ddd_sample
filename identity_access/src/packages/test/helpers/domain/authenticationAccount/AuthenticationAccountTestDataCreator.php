<?php

namespace packages\test\helpers\domains\authenticationAccount;

use packages\domain\model\authenticationAccount\LoginRestriction;
use packages\domain\model\authenticationAccount\IAuthenticationAccountRepository;
use packages\domain\model\authenticationAccount\UserEmail;
use packages\domain\model\authenticationAccount\UserId;
use packages\domain\model\authenticationAccount\UserName;
use packages\domain\model\authenticationAccount\password\UserPassword;
use packages\domain\model\authenticationAccount\AuthenticationAccount;
use packages\domain\model\authenticationAccount\UnsubscribeStatus;
use packages\domain\model\authenticationAccount\DefinitiveRegistrationCompletedStatus;

class AuthenticationAccountTestDataCreator
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
        ?UserEmail $email = null,
        ?UserPassword $password = null,
        ?DefinitiveRegistrationCompletedStatus $definitiveRegistrationCompletedStatus = null,
        ?UserId $id = null,
        ?LoginRestriction $loginRestriction = null,
        ?UnsubscribeStatus $unsubscribeStatus = null
    ): AuthenticationAccount
    {
        $authenticationAccount = $this->testAuthenticationAccountFactory->create(
            $email,
            $password,
            $definitiveRegistrationCompletedStatus,
            $id,
            $loginRestriction,
            $unsubscribeStatus
        );

        $this->authenticationAccountRepository->save($authenticationAccount);

        return $authenticationAccount;
    }
}