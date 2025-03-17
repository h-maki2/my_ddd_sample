<?php

namespace packages\adapter\messaging\kafka\listener\definitiveRegistration;

use dddCommonLib\domain\model\common\IMessagingLogger;
use dddCommonLib\domain\model\notification\Notification;
use dddCommonLib\domain\model\notification\NotificationReader;
use dddCommonLib\infrastructure\messaging\kafka\AKafkaConsumer;
use dddCommonLib\infrastructure\messaging\kafka\NotificationBrokerListener;
use packages\application\definitiveRegistration\SendDefinitiveRegistrationCompleteEmailApplicationService;
use packages\application\definitiveRegistration\SendDefinitiveRegistrationConfirmationEmailApplicationService;
use packages\application\registration\provisionalRegistration\GeneratingOneTimeTokenAndPasswordApplicationService;
use packages\domain\model\authenticationAccount\AuthenticationAccountCreated;
use packages\messaging\kafka\LaravelMessagingLogger;

class SendDefinitiveRegistrationCompleteEmailListener extends NotificationBrokerListener
{
    private SendDefinitiveRegistrationCompleteEmailApplicationService $appService;

    public function __construct(
        AKafkaConsumer $consumer,
        IMessagingLogger $logger,
        SendDefinitiveRegistrationCompleteEmailApplicationService $appService
    )
    {
        parent::__construct($consumer, $logger);
        $this->appService = $appService;
    }

    protected function filteredDispatch(Notification $notification): void
    {
        $notificationReader = new NotificationReader($notification);
        $this->appService->handle(
            $notificationReader->eventStringValue('email'),
        );
    }

    protected function listensTo(): array
    {
        return ['packages\domain\model\definitiveRegistrationConfirmation\DefinitiveRegistrationCompleted'];
    }
}