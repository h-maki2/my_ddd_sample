<?php

namespace packages\adapter\messaging\kafka\listener;

use dddCommonLib\domain\model\common\IMessagingLogger;
use dddCommonLib\domain\model\notification\Notification;
use dddCommonLib\infrastructure\messaging\kafka\AKafkaConsumer;
use dddCommonLib\infrastructure\messaging\kafka\NotificationBrokerListener;
use packages\application\registration\provisionalRegistration\GeneratingOneTimeTokenAndPasswordApplicationService;
use packages\domain\model\authenticationAccount\AuthenticationAccountCreated;
use packages\messaging\kafka\LaravelMessagingLogger;

class GeneratingOneTimeTokenAndPasswordListener extends NotificationBrokerListener
{
    private GeneratingOneTimeTokenAndPasswordApplicationService $appService;

    public function __construct(
        AKafkaConsumer $consumer,
        IMessagingLogger $logger,
        GeneratingOneTimeTokenAndPasswordApplicationService $appService
    )
    {
        parent::__construct($consumer, $logger);
        $this->appService = $appService;
    }

    protected function filteredDispatch(Notification $notification): void
    {
        $this->appService->handle($notification);
    }

    protected function listensTo(): array
    {
        return [AuthenticationAccountCreated::class];
    }
}