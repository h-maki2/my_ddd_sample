<?php

namespace packages\application\registration\definitiveRegistration;

use DateTimeImmutable;
use dddCommonLib\domain\model\domainEvent\DomainEventPublisher;
use dddCommonLib\domain\model\eventStore\IEventStore;
use dddCommonLib\infrastructure\eventStore\StoredEventSubscriber;
use packages\application\common\exception\TransactionException;
use packages\domain\model\definitiveRegistrationConfirmation\IDefinitiveRegistrationConfirmationRepository;
use packages\domain\model\definitiveRegistrationConfirmation\OneTimePassword;
use packages\domain\model\definitiveRegistrationConfirmation\OneTimeTokenValue;
use packages\domain\model\definitiveRegistrationConfirmation\validation\DefinitiveRegistrationConfirmationValidation;
use packages\domain\model\authenticationAccount\IAuthenticationAccountRepository;
use packages\domain\model\authenticationAccount\UnsubscribeStatus;
use packages\domain\model\common\transactionManage\TransactionManage;
use packages\domain\service\registration\definitiveRegistration\DefinitiveRegistrationUpdate;

/**
 * 本登録済み更新を行うアプリケーションサービス
 */
class DefinitiveRegistrationApplicationService implements DefinitiveRegistrationInputBoundary
{
    private IDefinitiveRegistrationConfirmationRepository $definitiveRegistrationConfirmationRepository;
    private DefinitiveRegistrationConfirmationValidation $definitiveRegistrationConfirmationValidation;
    private IAuthenticationAccountRepository $authenticationAccountRepository;
    private TransactionManage $transactionManage;
    private IEventStore $eventStore;

    public function __construct(
        IAuthenticationAccountRepository $authenticationAccountRepository,
        IDefinitiveRegistrationConfirmationRepository $definitiveRegistrationConfirmationRepository,
        TransactionManage $transactionManage,
        IEventStore $eventStore
    )
    {
        $this->definitiveRegistrationConfirmationRepository = $definitiveRegistrationConfirmationRepository;
        $this->authenticationAccountRepository = $authenticationAccountRepository;
        $this->definitiveRegistrationConfirmationValidation = new DefinitiveRegistrationConfirmationValidation($this->definitiveRegistrationConfirmationRepository);
        $this->transactionManage = $transactionManage;
        $this->eventStore = $eventStore;
    }

    /**
     * 本登録済み更新を行う
     */
    public function handle(string $oneTimeTokenValueString, string $oneTimePasswordString): DefinitiveRegistrationResult
    {
        DomainEventPublisher::instance()->reset();
        DomainEventPublisher::instance()->subscribe(new StoredEventSubscriber($this->eventStore));

        if (!$this->definitiveRegistrationConfirmationValidation->validate($oneTimePasswordString, $oneTimeTokenValueString)) {
            return DefinitiveRegistrationResult::createWhenValidationError('ワンタイムトークンかワンタイムパスワードが無効です。');
        }

        $oneTimeTokenValue = OneTimeTokenValue::reconstruct($oneTimeTokenValueString);
        $oneTimePassword = OneTimePassword::reconstruct($oneTimePasswordString);
        $definitiveRegistrationConfirmation = $this->definitiveRegistrationConfirmationRepository->findByTokenValue($oneTimeTokenValue);

        $authAccount = $this->authenticationAccountRepository->findById(
            $definitiveRegistrationConfirmation->userId
        );

        try {
            $this->transactionManage->performTransaction(function () use ($authAccount, $definitiveRegistrationConfirmation, $oneTimePassword) {
                $authAccount->updateDefinitiveRegistrationCompleted(
                    $definitiveRegistrationConfirmation,
                    $oneTimePassword,
                    new DateTimeImmutable()
                );
                $this->authenticationAccountRepository->save($authAccount);
            });
        } catch (\Exception $e) {
            throw new TransactionException($e->getMessage());
        }

        return DefinitiveRegistrationResult::createWhenSuccess();
    }
}