<?php
declare(strict_types=1);

use packages\domain\model\project\ProjectName;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class ProjectNameTest extends TestCase
{
    #[DataProvider('invalidProjectNameProvider')]
    public function test_不正なプロジェクト名の時に例外が発生する(string $projectName)
    {
        // when・then
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('プロジェクト名が不正です。 value: ' . $projectName);
        new ProjectName($projectName);
    }

    #[DataProvider('validProjectNameProvider')]
    public function test_有効なプロジェクト名の時にインスタンスを生成できる(string $projectNameString)
    {
        // given

        // when
        $projectName = new ProjectName($projectNameString);

        // then
        $this->assertEquals($projectNameString, $projectName->value);
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