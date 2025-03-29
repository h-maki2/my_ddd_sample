<?php

namespace packages\application\changePassword;

use DateTimeImmutable;
use packages\domain\model\authenticationAccount\IAuthenticationAccountRepository;
use packages\domain\model\authenticationAccount\password\Argon2HashPasswordManager;
use packages\domain\model\authenticationAccount\password\IPasswordManager;
use packages\domain\model\authenticationAccount\UnsubscribeStatus;
use packages\domain\model\authenticationAccount\password\UserPassword;
use packages\domain\model\authenticationAccount\validation\UserPasswordValidation;
use packages\domain\model\oauth\authToken\AccessToken;
use packages\domain\model\oauth\client\ClientId;
use packages\domain\model\oauth\client\IClientFetcher;
use packages\domain\model\oauth\client\RedirectUrl;
use packages\domain\model\oauth\scope\IScopeAuthorizationChecker;
use packages\domain\model\oauth\scope\Scope;
use packages\domain\model\oauth\scope\ScopeList;
use packages\domain\service\authenticationAccount\AuthenticationService;
use packages\domain\service\oauth\ClientService;
use packages\domain\service\oauth\LoggedInUserIdFetcher;
use RuntimeException;

class ChangePasswordApplicationService implements ChangePasswordApplicationInputBoundary
{
    private IAuthenticationAccountRepository $authenticationAccountRepository;
    private LoggedInUserIdFetcher $loggedInUserIdFetcher;
    private IPasswordManager $passwordManager;

    public function __construct(
        IAuthenticationAccountRepository $authenticationAccountRepository,
        LoggedInUserIdFetcher $loggedInUserIdFetcher,
        IPasswordManager $passwordManager
    ) {
        $this->authenticationAccountRepository = $authenticationAccountRepository;
        $this->loggedInUserIdFetcher = $loggedInUserIdFetcher;
        $this->passwordManager = $passwordManager;
    }

    public function changePassword(
        string $scopeString,
        string $passwordString
    ): ChangePasswordResult
    {
        $scope = Scope::from($scopeString);
        $userId = $this->loggedInUserIdFetcher->fetch($scope);

        $passwordValidation = new UserPasswordValidation($passwordString);
        if (!$passwordValidation->validate()) {
            return ChangePasswordResult::createWhenFaild($passwordValidation->errorMessageList());
        }

        $authAccount = $this->authenticationAccountRepository->findById($userId);
        if ($authAccount === null) {
            throw new RuntimeException('ユーザーが見つかりません');
        }

        $password = UserPassword::create($passwordString,  $this->passwordManager);
        $authAccount->changePassword($password, new DateTimeImmutable());
        $this->authenticationAccountRepository->save($authAccount);

        return ChangePasswordResult::createWhenSuccess();
    }
}