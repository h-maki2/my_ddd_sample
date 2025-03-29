<?php

namespace packages\domain\service\authenticationAccount;

use BadMethodCallException;
use DateTimeImmutable;
use packages\domain\model\authenticationAccount\IAuthenticationAccountRepository;
use packages\domain\model\authenticationAccount\UserEmail;
use packages\domain\model\authenticationAccount\UserId;

class AuthenticationAccountService
{
    private IAuthenticationAccountRepository $authenticationAccountRepository;

    public function __construct(IAuthenticationAccountRepository $authenticationAccountRepository)
    {
        $this->authenticationAccountRepository = $authenticationAccountRepository;
    }

    /**
     * すでに存在するemailアドレスかどうかを判定
     */
    public function alreadyExistsEmail(UserEmail $email): bool
    {
        $authenticationAccount = $this->authenticationAccountRepository->findByEmail($email);

        return $authenticationAccount !== null;
    }

    /**
     * ユーザーが退会済みかどうかを判定
     */
    public function isUnsubscribed(UserId $userId): bool
    {
        $authenticationAccount = $this->authenticationAccountRepository->findById($userId);
        if ($authenticationAccount === null) {
            return true;
        }

        return false;
    }

    /**
     * アカウントロックされているかどうかを判定
     */
    public function isAccountLocked(UserId $userId): bool
    {
        $authenticationAccount = $this->authenticationAccountRepository->findById($userId);
        if ($authenticationAccount === null) {
            throw new BadMethodCallException('アカウントが退会済みか存在しません。userId: ' . $userId->value);
        }

        return $authenticationAccount->isRestricted(new DateTimeImmutable());
    }

    /**
     * 本登録済みかどうかを判定
     */
    public function hasCompletedRegistration(UserId $userId): bool
    {
        $authenticationAccount = $this->authenticationAccountRepository->findById($userId);
        if ($authenticationAccount === null) {
            throw new BadMethodCallException('アカウントが退会済みか存在しません。userId: ' . $userId->value);
        }

        return $authenticationAccount->hasCompletedRegistration();
    }
}