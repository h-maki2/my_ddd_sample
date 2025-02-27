<?php

namespace packages\messaging\kafka\consumer\generatingOneTimeTokenAndPassword;

use dddCommonLib\domain\model\notification\Notification;
use dddCommonLib\infrastructure\messaging\kafka\KafkaMessageConsumer;
use packages\application\registration\provisionalRegistration\GeneratingOneTimeTokenAndPasswordApplicationService;
use packages\domain\model\authenticationAccount\AuthenticationAccountCreated;
use packages\messaging\kafka\LaravelMessagingLogger;

class GeneratingOneTimeTokenAndPasswordConsumer extends KafkaMessageConsumer
{
    private GeneratingOneTimeTokenAndPasswordApplicationService $appService;

    public function __construct(
        GeneratingOneTimeTokenAndPasswordApplicationService $appService,
        bool $testable = false
    )
    {
        parent::__construct(
            config('app.consumerGroupId'),
            config('app.kafkaHostName'),
            config('app.topickName'),
            new LaravelMessagingLogger(),
            testable: $testable
        );

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