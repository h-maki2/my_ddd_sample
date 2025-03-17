<?php

namespace packages\application\registration\definitiveRegistration;

use packages\domain\model\authenticationAccount\UserId;
use packages\domain\model\definitiveRegistrationConfirmation\IDefinitiveRegistrationConfirmationRepository;

/**
 * ワンタイムトークンとワンタイムパスワードを削除するアプリケーションサービス
 */
class DeleteOnetTimeTokenAndPasswordApplicationService
{
    private IDefinitiveRegistrationConfirmationRepository $definitiveRegistrationConfirmationRepository;

    public function __construct(
        IDefinitiveRegistrationConfirmationRepository $definitiveRegistrationConfirmationRepository
    )
    {
        $this->definitiveRegistrationConfirmationRepository = $definitiveRegistrationConfirmationRepository;
    }

    public function handle(string $userIdString): void
    {
        $userId = new UserId($userIdString);
        $definitiveRegistrationConfirmation = $this->definitiveRegistrationConfirmationRepository->findById($userId);
        if ($definitiveRegistrationConfirmation === null) {
            return;
        }
    
        $this->definitiveRegistrationConfirmationRepository->delete($userId);
    }
}