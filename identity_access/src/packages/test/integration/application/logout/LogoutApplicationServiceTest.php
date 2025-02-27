<?php

use App\Models\AuthenticationInformation;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Passport;
use packages\adapter\oauth\authToken\LaravelPassportAccessTokenDeactivationService;
use packages\adapter\oauth\authToken\LaravelPassportRefreshokenDeactivationService;
use packages\adapter\oauth\authToken\LaravelPassportRefreshTokenDeactivationService;
use packages\adapter\persistence\eloquent\EloquentAuthenticationAccountRepository;
use packages\adapter\service\laravel\LaravelAuthenticationService;
use packages\adapter\transactionManage\EloquentTransactionManage;
use packages\application\authentication\logout\LogoutApplicationService;
use packages\domain\model\authenticationAccount\password\Argon2HashPasswordManager;
use packages\test\helpers\domains\authenticationAccount\AuthenticationAccountTestDataCreator;
use packages\test\helpers\domains\authenticationAccount\TestAuthenticationAccountFactory;
use packages\test\helpers\domains\oauth\authToken\AccessTokenTestDataCreator;
use packages\test\helpers\domains\oauth\authToken\AuthTokenTestDataCreator;
use packages\test\helpers\domains\oauth\authToken\TestAuthTokenService;
use Tests\TestCase;

class LogoutApplicationServiceTest extends TestCase
{
    use DatabaseTransactions;

    private EloquentAuthenticationAccountRepository $authAccountReposiotry;
    private LaravelPassportAccessTokenDeactivationService $accessTokenDeactivationService;
    private LaravelPassportRefreshTokenDeactivationService $refreshTokenDeactivationService;
    private LogoutApplicationService $logoutApplicationService;
    private LaravelAuthenticationService $authService;
    private TestAuthTokenService $testAuthTokenService;
    private AccessTokenTestDataCreator $accessTokenTestDataCreator;
    private TestAuthenticationAccountFactory $testAuthenticationAccountFactory;

    public function setUp(): void
    {
        parent::setUp();

        $this->authAccountReposiotry = new EloquentAuthenticationAccountRepository();
        $this->accessTokenDeactivationService = new LaravelPassportAccessTokenDeactivationService();
        $this->refreshTokenDeactivationService = new LaravelPassportRefreshTokenDeactivationService();
        $this->authService = new LaravelAuthenticationService();
        $this->testAuthTokenService = new TestAuthTokenService();

        $this->logoutApplicationService = new LogoutApplicationService(
            $this->accessTokenDeactivationService,
            $this->refreshTokenDeactivationService,
            $this->authService,
            new EloquentTransactionManage()
        );

        $this->testAuthenticationAccountFactory = new TestAuthenticationAccountFactory(new Argon2HashPasswordManager());
        $this->accessTokenTestDataCreator = new AccessTokenTestDataCreator(
            new AuthenticationAccountTestDataCreator(
                $this->authAccountReposiotry,
                $this->testAuthenticationAccountFactory
            )
        );
    }

    public function test_ログアウトするとアクセストークンとリフレッシュトークンが無効化される()
    {
        // given
        // アクセストークンとリフレッシュトークンを作成
        $authAccount = $this->testAuthenticationAccountFactory->create();
        $accessToken = $this->accessTokenTestDataCreator->create($authAccount);

        // when
        $this->logoutApplicationService->logout(
            $accessToken->value
        );

        // then
        // アクセストークンが無効化されていることを確認
        $this->assertTrue($this->testAuthTokenService->isAccessTokenDeactivated($accessToken));

        // リフレッシュトークンが無効化されていることを確認
        $this->assertTrue($this->testAuthTokenService->isRefreshTokenDeactivated($accessToken));
    }
}