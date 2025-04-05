<?php
declare(strict_types=1);

use packages\domain\model\project\ProjectStatus;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class ProjectStatusTest extends TestCase
{
    #[DataProvider('validProjectStatusProvider')]
    public function test_有効なステータスが入力された場合にインスタンスを生成できる(string $statusString, string $expectedStringValue)
    {
        // when
        $projectStatus = ProjectStatus::from($statusString);

        // then
        $this->assertEquals($expectedStringValue, $projectStatus->stringValue());
    }

    public static function validProjectStatusProvider(): array
    {
        return [
            ['1', '未着手'],
            ['2', '進行中'],
            ['3', '完了'],
        ];
    }
}