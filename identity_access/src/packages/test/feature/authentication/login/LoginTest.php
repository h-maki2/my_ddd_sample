<?php

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use packages\adapter\oauth\client\LaravelPassportClientFetcher;
use packages\adapter\persistence\eloquent\EloquentAuthenticationAccountRepository;
use packages\adapter\presenter\common\json\JsonResponseStatus;
use packages\domain\model\authenticationAccount\UserEmail;
use packages\domain\model\authenticationAccount\password\UserPassword;
use packages\domain\model\authenticationAccount\DefinitiveRegistrationCompletedStatus;
use packages\domain\model\authenticationAccount\LoginRestriction;
use packages\domain\model\authenticationAccount\password\Argon2HashPasswordManager;
use packages\domain\model\oauth\client\ClientId;
use packages\domain\model\oauth\client\RedirectUrl;
use packages\domain\model\oauth\scope\Scope;
use packages\domain\model\oauth\scope\ScopeList;
use packages\test\helpers\domain\authenticationAccount\AuthenticationAccountTestDataCreator;
use packages\test\helpers\domain\authenticationAccount\password\Md5PasswordManager;
use packages\test\helpers\domain\authenticationAccount\TestAccessTokenCreator;
use packages\test\helpers\domain\authenticationAccount\TestAuthenticationAccountFactory;
use packages\test\helpers\domain\oauth\client\ClientTestDataCreator;
use Tests\TestCase;

class LoginTest extends TestCase
{
    private EloquentAuthenticationAccountRepository $eloquentAuthenticationAccountRepository;
    private AuthenticationAccountTestDataCreator $authenticationAccountTestDataCreator;
    private LaravelPassportClientFetcher $laravelPassportClientFetcher;

    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
        $this->eloquentAuthenticationAccountRepository = new EloquentAuthenticationAccountRepository();
        $this->authenticationAccountTestDataCreator = new AuthenticationAccountTestDataCreator(
            $this->eloquentAuthenticationAccountRepository,
            new TestAuthenticationAccountFactory(new Argon2HashPasswordManager())
        );
        $this->laravelPassportClientFetcher = new LaravelPassportClientFetcher();
    }

    public function test_メールアドレスとパスワードが異なる場合に、404エラーがレスポンスされる()
    {
        // given
        // 認証アカウントを作成する
        $this->authenticationAccountTestDataCreator->create(
            new UserEmail('test@example.com'),
            UserPassword::create('abcABC123!',  new Md5PasswordManager())
        );


        $存在しないメールアドレス = 'invalidEmail@example.com';
        $存在しないパスワード = 'abcABC123_';

        // クライアントを作成する
        $redirectUrl = config('app.url') . '/auth/callback';
        $clientData = ClientTestDataCreator::create(
            redirectUrl: $redirectUrl
        );

        // when
        $scopeString = Scope::ReadAccount->value . ' ' . Scope::EditAccount->value . ' ' . Scope::DeleteAccount->value;
        $response = $this->post('/api/login', [
            'email' => $存在しないメールアドレス,
            'password' => $存在しないパスワード,
            'client_id' => $clientData->id,
            'redirect_url' => $clientData->redirect,
            'response_type' => 'code',
            'state' => 'abcdefg',
            'scope' => $scopeString
        ],[
            'Accept' => 'application/vnd.example.v1+json',
        ]);

        // then
        $response->assertStatus(401);
        $response->assertJson([
            'success' => false,
            'error' => [
                'code' => 'Unauthorized',
                'details' => [
                    'accountLocked' => false
                ]
            ]
        ]);
    }

    public function test_正しいメールアドレスとパスワードの場合に、認可コード取得URLを取得できる()
    {
        // 認証アカウントを作成する
        $this->authenticationAccountTestDataCreator->create(
            email: new UserEmail('invalid_address@example.com'),
            password: UserPassword::create('abcABC123!',  new Argon2HashPasswordManager()),
            definitiveRegistrationCompletedStatus: DefinitiveRegistrationCompletedStatus::Completed,
            loginRestriction: LoginRestriction::initialization()
        );

        // クライアントを作成する
        $redirectUrl = config('app.url') . '/auth/callback';
        $client = ClientTestDataCreator::create(
            redirectUrl: $redirectUrl
        );

        // when
        // 正しいメールアドレスとパスワードを入力してログインする
        $email = 'invalid_address@example.com';
        $password = 'abcABC123!';
        $responseType = 'code';
        $state = 'abcdefg';
        $scopeString = Scope::ReadAccount->value . ' ' . Scope::EditAccount->value . ' ' . Scope::DeleteAccount->value;
        $response = $this->post('/api/login', [
            'email' => $email,
            'password' => $password,
            'client_id' => $client->id,
            'redirect_url' => $client->redirect,
            'response_type' => $responseType,
            'state' => $state,
            'scope' => $scopeString
        ],[
            'Accept' => 'application/vnd.example.v1+json',
        ]);

        // then
        $response->assertStatus(200);

        $clientData = $this->laravelPassportClientFetcher->fetchById(new ClientId($client->id));
        $expectedAuthorizationUrl = $clientData->urlForObtainingAuthorizationCode(
            new RedirectUrl($client->redirect),
            $responseType,
            $state,
            ScopeList::createFromString($scopeString)
        );
        $expectedData = [
            'success' => true,
            'data' => [
                'authorizationUrl' => $expectedAuthorizationUrl
            ]
        ];
        $response->assertJson($expectedData);
    }
}