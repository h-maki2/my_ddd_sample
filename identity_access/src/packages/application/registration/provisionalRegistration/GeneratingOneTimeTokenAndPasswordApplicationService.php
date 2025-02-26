<?php

namespace packages\application\registration\provisionalRegistration;

use dddCommonLib\domain\model\notification\Notification;
use packages\domain\model\authenticationAccount\AuthenticationAccountCreated;
use packages\domain\model\common\transactionManage\TransactionManage;
use packages\domain\model\definitiveRegistrationConfirmation\DefinitiveRegistrationConfirmation;
use packages\domain\model\definitiveRegistrationConfirmation\IDefinitiveRegistrationConfirmationRepository;
use packages\domain\model\email\DefinitiveRegistrationConfirmationEmailDtoFactory;
use packages\domain\model\email\IEmailSender;
use packages\domain\service\registration\definitiveRegistration\OneTimeTokenExistsService;

class GeneratingOneTimeTokenAndPasswordApplicationService
{
    private IEmailSender $emailSender;
    private IDefinitiveRegistrationConfirmationRepository $definitiveRegistrationConfirmationRepository;
    private TransactionManage $transactionManage;

    public function __construct(
        IEmailSender $emailSender,
        IDefinitiveRegistrationConfirmationRepository $definitiveRegistrationConfirmationRepository,
        TransactionManage $transactionManage
    )
    {
        $this->emailSender = $emailSender;
        $this->definitiveRegistrationConfirmationRepository = $definitiveRegistrationConfirmationRepository;
        $this->transactionManage = $transactionManage;
    }

    public function handle(Notification $notification): void
    {
        $authenticationAccountCreated = $this->authenticationAccountCreatedFrom($notification);

        $definitiveRegistrationConfirmation = DefinitiveRegistrationConfirmation::create(
            $authenticationAccountCreated->userId,
            new OneTimeTokenExistsService($this->definitiveRegistrationConfirmationRepository)
        );

        $this->transactionManage->performTransaction(function () use ($definitiveRegistrationConfirmation, $authenticationAccountCreated) {
            $this->definitiveRegistrationConfirmationRepository->save($definitiveRegistrationConfirmation);

            $this->emailSender->send(
                DefinitiveRegistrationConfirmationEmailDtoFactory::create(
                    $authenticationAccountCreated->email,
                    $definitiveRegistrationConfirmation->oneTimeToken(),
                    $definitiveRegistrationConfirmation->oneTimePassword()
                )
            );
        });
    }

    private function authenticationAccountCreatedFrom(Notification $notification): AuthenticationAccountCreated
    {
        return $notification->toDomainEvent();
    }
}