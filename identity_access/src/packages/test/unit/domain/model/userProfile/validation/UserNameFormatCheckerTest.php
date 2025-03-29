<?php
declare(strict_types=1);

use packages\domain\model\userProfile\validation\UserNameFormatChecker;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class UserNameFormatCheckerTest extends TestCase
{
    #[DataProvider('validUserNameLengthProvider')]
    public function test_ユーザー名が適切な文字数の場合に、invalidUserNameLengthメソッドの戻り値がfalseになる(string $userName)
    {
        // given

        // when
        $result = UserNameFormatChecker::invalidUserNameLength($userName);

        // then
        $this->assertFalse($result);
    }

    #[DataProvider('invalidUserNameLengthProvider')]
    public function test_ユーザー名が不適切な文字数の場合に、invalidUserNameLengthメソッドの戻り値がtrueになる(string $userName)
    {
        // given

        // when
        $result = UserNameFormatChecker::invalidUserNameLength($userName);

        // then
        $this->assertTrue($result);
    }

    #[DataProvider('validUserNameProvider')]
    public function test_ユーザー名が適切な形式の場合に、onlyWhiteSpaceメソッドの戻り値がfalseになる(string $userName)
    {
        // given

        // when
        $result = UserNameFormatChecker::onlyWhiteSpace($userName);

        // then
        $this->assertFalse($result);
    }

    #[DataProvider('invalidUserNameProvider')]
    public function test_ユーザー名が不適切な形式の場合に、onlyWhiteSpaceメソッドの戻り値がtrueになる(string $userName)
    {
        // given

        // when
        $result = UserNameFormatChecker::onlyWhiteSpace($userName);

        // then
        $this->assertTrue($result);
    }

    public static function validUserNameLengthProvider(): array
    {
        return [
            [str_repeat('a', 50)],
            [str_repeat('あ', 50)],
            ['あ'],
            ['a'],
            ['テスト　名前']
        ];
    }

    public static function invalidUserNameLengthProvider(): array
    {
        return [
            [''],
            [str_repeat('a', 51)],
            [str_repeat('あ', 51)],
        ];
    }

    public static function validUserNameProvider(): array
    {
        return [
            ['テスト　　名前'],
            ['test name'],
            ['test 名前']
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
}