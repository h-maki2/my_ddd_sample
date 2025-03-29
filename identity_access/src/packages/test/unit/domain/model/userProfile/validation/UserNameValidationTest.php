<?php
declare(strict_types=1);

use packages\domain\model\userProfile\validation\UserNameValidation;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class UserNameValidationTest extends TestCase
{
    #[DataProvider('invalidUserNameLengthProvider')]
    public function test_ユーザー名が不適切な文字数に場合に、バリデーションエラーが発生する(string $userName)
    {
        // given
        $userNameValidation = new UserNameValidation($userName);

        // when
        $result = $userNameValidation->validate();

        // then
        $this->assertFalse($result);
        $this->assertEquals(['ユーザー名は1文字以上50文字以内で入力してください。'], $userNameValidation->errorMessageList());
    }

    #[DataProvider('invalidUserNameProvider')]
    public function test_ユーザー名が空白文字列の場合に、バリデーションエラーが発生する(string $userName)
    {
        // given
        $userNameValidation = new UserNameValidation($userName);

        // when
        $result = $userNameValidation->validate();

        // then
        $this->assertFalse($result);
        $this->assertEquals(['ユーザー名に空白文字列のみは使用できません。'], $userNameValidation->errorMessageList());
    }

    #[DataProvider('validUserNameProvider')]
    public function test_ユーザー名が適切な場合に、バリデーションエラーが発生しない(string $userName)
    {
        // given
        $userNameValidation = new UserNameValidation($userName);

        // when
        $result = $userNameValidation->validate();

        // then
        $this->assertTrue($result);
        $this->assertEquals([], $userNameValidation->errorMessageList());
    }

    public static function invalidUserNameLengthProvider(): array
    {
        return [
            [''],
            [str_repeat('a', 51)],
            [str_repeat('あ', 51)],
        ];
    }

    public static function invalidUserNameProvider(): array
    {
        return [
            [' '],
            ['  '],
            ['　　　'],
            [' 　 '],
        ];
    }

    public static function validUserNameProvider(): array
    {
        return [
            ['テスト　　名前'],
            ['test name'],
            ['test　名前'],
            [str_repeat('a', 50)],
            [str_repeat('あ', 50)],
            ['あ'],
            ['a'],
        ];
    }
}