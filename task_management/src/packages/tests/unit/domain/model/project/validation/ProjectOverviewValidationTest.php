<?php
declare(strict_types=1);

use packages\domain\model\project\validation\ProjectOverviewValidation;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class ProjectOverviewValidationTest extends TestCase
{
    #[DataProvider('invalidProjectOverviewProvider')]
    public function test_プロジェクト概要が500文字以上の時にバリデーションエラーが発生する(string $invaliProjectOverView)
    {
        // given
        $projectOverviewValidation = new ProjectOverviewValidation($invaliProjectOverView);

        // when
        $result = $projectOverviewValidation->validate();

        // then
        $this->assertFalse($result);
        $this->assertEquals(['プロジェクト概要は500文字以内で入力してください。'], $projectOverviewValidation->errorMessageList());
        $this->assertEquals('projectOverview', $projectOverviewValidation->fieldName());
    }

    #[DataProvider('validProjectOverviewProvider')]
    public function test_有効なプロジェクト概要の時にバリデーションエラーが発生しない(string $projectOverview)
    {
        // given
        $projectOverviewValidation = new ProjectOverviewValidation($projectOverview);

        // when
        $result = $projectOverviewValidation->validate();

        // then
        $this->assertTrue($result);
        $this->assertEquals([], $projectOverviewValidation->errorMessageList());
        $this->assertEquals('projectOverview', $projectOverviewValidation->fieldName());
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