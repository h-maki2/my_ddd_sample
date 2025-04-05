<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use packages\domain\model\project\ProjectOverview;

class ProjectOverviewTest extends TestCase
{
    #[DataProvider('invalidProjectOverviewProvider')]
    public function test_プロジェクト概要が500文字以上の時に例外が発生する(string $invalidProjectOverView)
    {
        // when・then
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('プロジェクト概要が不正です。value: ' . $invalidProjectOverView);
        new ProjectOverview($invalidProjectOverView);
    }

    #[DataProvider('validProjectOverviewProvider')]
    public function test_有効なプロジェクト概要の時に例外が発生しない(string $projectOverviewString)
    {
        // when
        $projectOverview = new ProjectOverview($projectOverviewString);

        // then
        $this->assertEquals($projectOverviewString, $projectOverview->value);
    }

    public static function invalidProjectOverviewProvider(): array
    {
        return [
            [str_repeat('a', 501)],
            [str_repeat('あ', 501)],
        ];
    }

    public static function validProjectOverviewProvider(): array
    {
        return [
            ['プロジェクト概要'],
            ['プロジェクト概要1234567890'],
            ['プロジェクト概要あいうえおかきくけこさしすせそたちつてと'],
            [str_repeat('あ', 500)],
            [str_repeat('a', 500)],
        ];
    }
}
