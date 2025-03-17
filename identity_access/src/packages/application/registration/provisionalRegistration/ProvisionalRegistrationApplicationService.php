<?php

namespace packages\application\registration\provisionalRegistration;

use dddCommonLib\domain\model\domainEvent\DomainEventPublisher;
use dddCommonLib\domain\model\eventStore\IEventStore;
use dddCommonLib\infrastructure\eventStore\StoredEventSubscriber;
use Exception;
use packages\application\common\exception\TransactionException;
use packages\domain\model\authenticationAccount\AuthenticationAccount;
use packages\domain\model\definitiveRegistrationConfirmation\IDefinitiveRegistrationConfirmationRepository;
use packages\domain\model\definitiveRegistrationConfirmation\OneTimeToken;
use packages\domain\model\definitiveRegistrationConfirmation\validation\OneTimeTokenValidation;
use packages\domain\model\authenticationAccount\IAuthenticationAccountRepository;
use packages\domain\model\authenticationAccount\password\Argon2HashPasswordManager;
use packages\domain\model\authenticationAccount\password\IPasswordManager;
use packages\domain\model\authenticationAccount\UserEmail;
use packages\domain\model\authenticationAccount\password\UserPassword;
use packages\domain\model\authenticationAccount\validation\UserEmailValidation;
use packages\domain\model\authenticationAccount\validation\UserPasswordConfirmationValidation;
use packages\domain\model\authenticationAccount\validation\UserPasswordValidation;
use packages\domain\model\common\transactionManage\TransactionManage;
use packages\domain\model\common\validator\ValidationHandler;
use packages\domain\model\email\IEmailSender;
use packages\domain\service\authenticationAccount\AuthenticationAccountService;
use packages\domain\service\registration\provisionalRegistration\ProvisionalRegistrationUpdate;

/**
 * ユーザー登録のアプリケーションサービス
 */
class ProvisionalRegistrationApplicationService implements ProvisionalRegistrationInputBoundary
{
    private IAuthenticationAccountRepository $authenticationAccountRepository;
    private IDefinitiveRegistrationConfirmationRepository $definitiveRegistrationConfirmationRepository;
    private IPasswordManager $passwordManager;
    private TransactionManage $transactionManage;
    private IEventStore $eventStore;
    private AuthenticationAccountService $authenticationAccountService;

    public function __construct(
        IDefinitiveRegistrationConfirmationRepository $definitiveRegistrationConfirmationRepository,
        IAuthenticationAccountRepository $authenticationAccountRepository,
        TransactionManage $transactionManage,
        IPasswordManager $passwordManager,
        IEventStore $eventStore
    )
    {
        $this->authenticationAccountRepository = $authenticationAccountRepository;
        $this->definitiveRegistrationConfirmationRepository = $definitiveRegistrationConfirmationRepository;
        $this->passwordManager = $passwordManager;
        $this->transactionManage = $transactionManage;
        $this->eventStore = $eventStore;
        $this->authenticationAccountService = new AuthenticationAccountService($this->authenticationAccountRepository);
    }

    /**
     * ユーザー登録を行う
     */
    public function userRegister(
        string $inputedEmail, 
        string $inputedPassword,
        string $inputedPasswordConfirmation
    ): ProvisionalRegistrationResult
    {
        DomainEventPublisher::instance()->reset();
        DomainEventPublisher::instance()->subscribe(new StoredEventSubscriber($this->eventStore));

        $validationHandler = new ValidationHandler();
        $validationHandler->addValidator(new UserEmailValidation($inputedEmail, $this->authenticationAccountRepository));
        $validationHandler->addValidator(new UserPasswordValidation($inputedPassword));
        $validationHandler->addValidator(new UserPasswordConfirmationValidation($inputedPassword, $inputedPasswordConfirmation));
        
        if (!$validationHandler->validate()) {
            return ProvisionalRegistrationResult::createWhenValidationError(
                $validationHandler->errorMessages()
            );
        }

        $userEmail = new UserEmail($inputedEmail);
        $userPassword = UserPassword::create($inputedPassword, $this->passwordManager);

        try {
            $this->transactionManage->performTransaction(function () use ($userEmail, $userPassword) {
                $authenticationAccount = AuthenticationAccount::create(
                    $this->authenticationAccountRepository->nextUserId(),
                    $userEmail,
                    $userPassword,
                    $this->authenticationAccountService
                );
                $this->authenticationAccountRepository->save($authenticationAccount);
            });
        } catch (\Exception $e) {
            throw new TransactionException($e->getMessage());
        }

        return ProvisionalRegistrationResult::createWhenSuccess();
    }
}