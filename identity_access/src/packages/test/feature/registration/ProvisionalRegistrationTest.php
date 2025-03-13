<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use packages\adapter\persistence\eloquent\EloquentAuthenticationAccountRepository;
use packages\adapter\persistence\eloquent\EloquentDefinitiveRegistrationConfirmationRepository;
use packages\adapter\persistence\eloquent\EloquentEventStore;
use packages\domain\model\authenticationAccount\UserEmail;
use Tests\TestCase;

class ProvisionalRegistrationTest extends TestCase
{
    use DatabaseTransactions;

    private EloquentEventStore $eventStore;
    private EloquentDefinitiveRegistrationConfirmationRepository $definitiveRegistrationConfirmationRepository;
    private EloquentAuthenticationAccountRepository $authenticationAccountRepository;

    private string $pidOfConsumer;
    private string $pidOfCdc;

    protected function setUp(): void
    {
        parent::setUp();
        $this->eventStore = new EloquentEventStore();
        $this->definitiveRegistrationConfirmationRepository = new EloquentDefinitiveRegistrationConfirmationRepository();
        $this->authenticationAccountRepository = new EloquentAuthenticationAccountRepository();

        // イベントを受信するリスナを起動させておく
        $this->pidOfConsumer = exec('php artisan app:generating-oneTimeToken-and-password-consumer > output_consumer.txt 2>&1 &');
        $this->pidOfCdc = exec('php artisan app:cdc-listener > output_cdc.txt 2>&1 &');

        sleep(1);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        // リスナを停止させる
        exec("kill -9 $this->pidOfConsumer");
        exec("kill -9 $this->pidOfCdc");
    }

    public function test_メールアドレスとパスワードを入力してユーザー登録を行う()
    {
        // given
        $userEmail = 'test@exmaple.com';
        $userPassword = 'abcABC123!';
        $userPasswordConfirmation = 'abcABC123!';

        // when
        $response = $this->post('/provisional_register', [
            'email' => $userEmail,
            'password' => $userPassword,
            'passwordConfirmation' => $userPasswordConfirmation
        ]);

        // then
        $response->assertStatus(200);
        // ユーザー仮登録完了画面に遷移することを確認する
        $content = htmlspecialchars_decode($response->getContent());
        $this->assertStringContainsString('<title>ユーザー仮登録完了</title>', $content);

        $authAccount = $this->authenticationAccountRepository->findByEmail(new UserEmail($userEmail));

        $retryCount = 0;
        while ($retryCount < 10) {
            $definitiveRegistrationConfirmation = $this->definitiveRegistrationConfirmationRepository->findById($authAccount->id());
            if ($definitiveRegistrationConfirmation !== null) {
                break;
            }
            sleep(2);
            $retryCount++;
        }

        $this->assertNotNull($definitiveRegistrationConfirmation);
    }

    public function test_メールアドレスの形式とパスワードの形式が不正な場合にユーザー登録に失敗する()
    {
        // given
        $userEmail = 'test'; // メールアドレスの形式が異なる
        $userPassword = 'abcABC123'; // パスワードの形式が異なる
        $userPasswordConfirmation = 'abcABC123';

        // when
        $response = $this->post('/provisional_register', [
            'email' => $userEmail,
            'password' => $userPassword,
            'passwordConfirmation' => $userPasswordConfirmation
        ]);

        // then
        // ユーザー登録画面にリダイレクトされることを確認
        $response->assertStatus(302);
    }
}