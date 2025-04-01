<?php
declare(strict_types=1);

use packages\domain\model\userProfile\validation\SelfIntroductionTextValidation;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SelfIntroductionTextValidationTest extends TestCase
{
    #[DataProvider('validSelfIntroductionTextProvider')]
    public function test_自己紹介文が適切な場合に、バリデーションエラーが発生しない(string $selfIntroductionText)
    {
        // given
        $selfIntroductionTextValidation = new SelfIntroductionTextValidation($selfIntroductionText);

        // when
        $result = $selfIntroductionTextValidation->validate();

        // then
        $this->assertTrue($result);
        $this->assertEquals([], $selfIntroductionTextValidation->errorMessageList());
    }

    #[DataProvider('invalidSelfIntroductionTextProvider')]
    public function test_自己紹介文が不適切な場合に、バリデーションエラーが発生する(string $selfIntroductionText)
    {
        // given
        $selfIntroductionTextValidation = new SelfIntroductionTextValidation($selfIntroductionText);

        // when
        $result = $selfIntroductionTextValidation->validate();

        // then
        $this->assertFalse($result);
        $this->assertEquals(['自己紹介文は500文字以内で入力してください。'], $selfIntroductionTextValidation->errorMessageList());
    }

    public static function validSelfIntroductionTextProvider(): array
    {
        return [
            ['あいうえお'],
            ['1234567890'],
            ['!@#$%^&*()_+'],
            ['あいうえお1234567890!@#$%^&*()_+'],
            [str_repeat('あ', 500)],
            ['']
        ];
    }

    public static function invalidSelfIntroductionTextProvider(): array
    {
        return [
            [str_repeat('あ', 501)],
            [str_repeat('a', 501)],
            [str_repeat(' ', 501)],
            [str_repeat('　', 501)],
        ];
    }
}