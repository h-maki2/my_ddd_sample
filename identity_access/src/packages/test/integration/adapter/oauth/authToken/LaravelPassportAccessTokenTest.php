<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use packages\adapter\persistence\eloquent\EloquentAuthenticationAccountRepository;
use packages\domain\model\authenticationAccount\password\Argon2HashPasswordManager;
use packages\test\helpers\authenticationAccount\AuthenticationAccountTestDataCreator;
use packages\test\helpers\authenticationAccount\TestAuthenticationAccountFactory;
use packages\test\helpers\oauth\authToken\AccessTokenTestDataCreator;
use Tests\TestCase;

class LaravelPassportAccessTokenTest extends TestCase
{
    private AccessTokenTestDataCreator $accessTokenTestDataCreator;
    private TestAuthenticationAccountFactory $testAuthenticationAccountFactory;

    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
        $this->testAuthenticationAccountFactory = new TestAuthenticationAccountFactory(new Argon2HashPasswordManager());
        $this->accessTokenTestDataCreator = new AccessTokenTestDataCreator(
            new AuthenticationAccountTestDataCreator(
                new EloquentAuthenticationAccountRepository(),
                $this->testAuthenticationAccountFactory
                )
        );
    }

    public function test_アクセストークンからデコードしたIDを取得できる()
    {
        // given
        $authAccount = $this->testAuthenticationAccountFactory->create();
        $accessToken = $this->accessTokenTestDataCreator->create($authAccount);

        // when
        $accessTokenId = $accessToken->id();

        // then
        $this->assertIsString($accessTokenId);
    }
}