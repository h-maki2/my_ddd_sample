<?php
declare(strict_types=1);

use packages\domain\model\project\validation\ProjectNameValidation;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class ProjectNameValidationTest extends TestCase
{
    #[DataProvider('invalidProjectNameProvider')]
    public function test_プロジェクト名が空の時、100文字以上の時にバリデーションエラーが発生する(string $inValidProjectName)
    {
        // given
        $projectNameValidation = new ProjectNameValidation($inValidProjectName);

        // when
        $result = $projectNameValidation->validate();

        // then
        $this->assertFalse($result);
        $this->assertEquals(['プロジェクト名は1文字以上100文字以内で入力してください。'], $projectNameValidation->errorMessageList());
        $this->assertEquals('projectName', $projectNameValidation->fieldName());
    }

    #[DataProvider('validProjectNameProvider')]
    public function test_有効なプロジェクト名の時にバリデーションエラーが発生しない(string $projectName)
    {
        // given
        $projectNameValidation = new ProjectNameValidation($projectName);

        // when
        $result = $projectNameValidation->validate();

        // then
        $this->assertTrue($result);
        $this->assertEquals([], $projectNameValidation->errorMessageList());
        $this->assertEquals('projectName', $projectNameValidation->fieldName());
    }

    public static function invalidProjectNameProvider(): array
    {
        return [
            [''], // 空文字
            [str_repeat('a', 101)],
            [str_repeat('あ', 101)],
        ];
    }

    public static function validProjectNameProvider(): array
    {
        return [
            ['プロジェクト名'],
            ['プロジェクト名1234567890'],
            ['プロジェクト名あいうえおかきくけこさしすせそたちつてと'],
            [str_repeat('あ', 100)],
            [str_repeat('a', 100)],
        ];
    }
}