<?php

namespace packages\adapter\messaging\kafka\listener;

use dddCommonLib\domain\model\common\IMessagingLogger;
use dddCommonLib\domain\model\notification\Notification;
use dddCommonLib\domain\model\notification\NotificationReader;
use dddCommonLib\infrastructure\messaging\kafka\AKafkaConsumer;
use dddCommonLib\infrastructure\messaging\kafka\NotificationBrokerListener;
use packages\application\definitiveRegistrationConfirmation\SendDefinitiveRegistrationConfirmationEmailApplicationService;
use packages\application\registration\provisionalRegistration\GeneratingOneTimeTokenAndPasswordApplicationService;
use packages\domain\model\authenticationAccount\AuthenticationAccountCreated;
use packages\messaging\kafka\LaravelMessagingLogger;

class SendDefinitiveRegistrationConfirmationEmailListener extends NotificationBrokerListener
{
    private SendDefinitiveRegistrationConfirmationEmailApplicationService $appService;

    public function __construct(
        AKafkaConsumer $consumer,
        IMessagingLogger $logger,
        SendDefinitiveRegistrationConfirmationEmailApplicationService $appService
    )
    {
        parent::__construct($consumer, $logger);
        $this->appService = $appService;
    }

    protected function filteredDispatch(Notification $notification): void
    {
        $notificationReader = new NotificationReader($notification);
        $this->appService->handle(
            $notificationReader->eventStringValue('oneTimeToken'),
            $notificationReader->eventStringValue('oneTimePassword'),
            $notificationReader->eventStringValue('expirationHours'),
            $notificationReader->eventStringValue('email'),
        );
    }

    protected function listensTo(): array
    {
        return ['packages\domain\model\definitiveRegistrationConfirmation\ProvisionalRegistrationCompleted'];
    }
}