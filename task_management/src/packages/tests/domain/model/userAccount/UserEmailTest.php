<?php
declare(strict_types=1);

use packages\domain\model\userProfile\userAccount\UserEmail;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class UserEmailTest extends TestCase
{
    #[DataProvider('invalidEmailProvider')]
    public function test_不正な形式のメールアドレスが入力された場合に、例外が発生する($email)
    {
        // given

        // when・then
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('メールアドレスの形式が不正です。');
        $email = new UserEmail($email);
    }

    #[DataProvider('validEmailProvider')]
    public function test_正しい形式のメールアドレスが入力された場合に、インタンスを生成できる($emailString)
    {
        // given

        // when
        $email = new UserEmail($emailString);

        // when・then
        $this->assertEquals($emailString, $email->value);
    }

    public static function invalidEmailProvider()
    {
        return [
            ['@example.com'],
            ['user@'],
            [''],
            ['  '],
            ['　　'],
            ['テスト@test.com']
        ];
    }

    public static function validEmailProvider()
    {
        return [
            ['test@gmail.com'],
            ['test@example.com'],
            ['testTest@icloud.com']
        ];
    }
}