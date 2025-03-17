<?php

namespace packages\application\registration\provisionalRegistration;

use dddCommonLib\domain\model\domainEvent\DomainEventPublisher;
use dddCommonLib\domain\model\eventStore\IEventStore;
use dddCommonLib\domain\model\notification\Notification;
use dddCommonLib\infrastructure\eventStore\StoredEventSubscriber;
use packages\domain\model\authenticationAccount\AuthenticationAccountCreated;
use packages\domain\model\authenticationAccount\IAuthenticationAccountRepository;
use packages\domain\model\authenticationAccount\UnsubscribeStatus;
use packages\domain\model\authenticationAccount\UserEmail;
use packages\domain\model\authenticationAccount\UserId;
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
    private IEventStore $eventStore;
    private IAuthenticationAccountRepository $authenticationAccountRepository;

    public function __construct(
        IEmailSender $emailSender,
        IDefinitiveRegistrationConfirmationRepository $definitiveRegistrationConfirmationRepository,
        TransactionManage $transactionManage,
        IEventStore $eventStore,
        IAuthenticationAccountRepository $authenticationAccountRepository
    )
    {
        $this->emailSender = $emailSender;
        $this->definitiveRegistrationConfirmationRepository = $definitiveRegistrationConfirmationRepository;
        $this->transactionManage = $transactionManage;
        $this->eventStore = $eventStore;
        $this->authenticationAccountRepository = $authenticationAccountRepository;
    }

    public function handle(string $userId): void
    {
        DomainEventPublisher::instance()->reset();
        DomainEventPublisher::instance()->subscribe(new StoredEventSubscriber($this->eventStore));

        $userId = new UserId($userId);

        $definitiveRegistrationConfirmation = $this->definitiveRegistrationConfirmationRepository->findById($userId);
        if ($definitiveRegistrationConfirmation !== null) {
            return;
        }

        $authAccount = $this->authenticationAccountRepository->findById($userId, UnsubscribeStatus::Subscribed);

        $this->transactionManage->performTransaction(function () use ($definitiveRegistrationConfirmation, $authAccount) {
            $definitiveRegistrationConfirmation = DefinitiveRegistrationConfirmation::create(
                $authAccount->id(),
                new OneTimeTokenExistsService($this->definitiveRegistrationConfirmationRepository),
                $authAccount->email()
            );

            $this->definitiveRegistrationConfirmationRepository->save($definitiveRegistrationConfirmation);
        });
    }
}