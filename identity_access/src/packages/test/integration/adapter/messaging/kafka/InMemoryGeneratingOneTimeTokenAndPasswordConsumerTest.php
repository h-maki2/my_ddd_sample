<?php

use packages\adapter\persistence\inMemory\InMemoryDefinitiveRegistrationConfirmationRepository;
use packages\application\registration\provisionalRegistration\GeneratingOneTimeTokenAndPasswordApplicationService;
use packages\domain\model\email\IEmailSender;
use packages\domain\model\email\SendEmailDto;
use packages\test\helpers\adapter\transactionManage\TestTransactionManage;
use Tests\TestCase;

class InMemoryGeneratingOneTimeTokenAndPasswordConsumerTest extends TestCase
{
    private InMemoryDefinitiveRegistrationConfirmationRepository $definitiveRegistrationConfirmationRepository;
    private TestTransactionManage $transactionManage;
    private IEmailSender $emailSender;
    private SendEmailDto $catchedSendEmailDto;
    private GeneratingOneTimeTokenAndPasswordApplicationService $appService;

    public function setUp(): void
    {
        parent::setUp();

        $emailSender = $this->createMock(IEmailSender::class);
        $emailSender
            ->method('send')
            ->with($this->callback(function (SendEmailDto $sendEmailDto) {
                $this->catchedSendEmailDto = $sendEmailDto;
                return true;
            }));
        $this->emailSender = $emailSender;

        $this->definitiveRegistrationConfirmationRepository = new InMemoryDefinitiveRegistrationConfirmationRepository();
        $this->appService = new GeneratingOneTimeTokenAndPasswordApplicationService(
            $this->emailSender,
            $this->definitiveRegistrationConfirmationRepository,
            new TestTransactionManage()
        );
    }
}