<?php

use packages\domain\model\project\ProjectId;
use PHPUnit\Framework\TestCase;

class ProjectIdTest extends TestCase
{
    public function test_プロジェクトIDが空文字列の場合に例外がスローされる()
    {
        // given
        $projectIdString = '';

        // when・then
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('プロジェクトIDが空です。');
        ProjectId::reconstruct($projectIdString);
    }

    public function test_プロジェクトIDが指定の接頭辞から始まらない場合に例外が発生する()
    {
        // given
        // 先頭が「TA-P-」ではないプロジェクトID
        $projectIdString = '123456-TA-P';

        // when・then
        // 先頭が「TA-P-」ではないプロジェクトIDの場合、例外がスローされることを確認
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('プロジェクトIDが不正です。value: ' . $projectIdString);
        ProjectId::reconstruct($projectIdString);
    }

    public function test_プロジェクトIDが接頭辞のみの場合に例外は発生する()
    {
        // given
        // 接頭辞のみのプロジェクトID
        $projectIdString = 'TA-P-';

        // when・then
        // 接頭辞のみのプロジェクトIDの場合、例外がスローされることを確認
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('プロジェクトIDが不正です。value: ' . $projectIdString);
        ProjectId::reconstruct($projectIdString);
    }

    public function test_正常にプロジェクトIDのインスタンスを生成できる()
    {
        // given
        $projectIdString = '123456';

        // when
        $projectId = ProjectId::create($projectIdString);

        // then
        // プロジェクトIDのインスタンスが正しく生成されていることを確認
        $this->assertEquals('TA-P-' . $projectIdString, $projectId->value);
    }

    public function test_正常にプロジェクトIDのインスタンスを復元できる()
    {
        // given
        $projectIdString = 'TA-P-123456';

        // when
        $projectId = ProjectId::reconstruct($projectIdString);

        // then
        // プロジェクトIDのインスタンスが正しく復元されていることを確認
        $this->assertEquals($projectIdString, $projectId->value);
    }

    public function test_他のプロジェクトIDと等しい場合にequalsメソッドがtrueを返す()
    {
        // given
        $projectIdString = 'TA-P-123456';
        $projectId1 = ProjectId::reconstruct($projectIdString);
        $projectId2 = ProjectId::reconstruct($projectIdString);

        // when
        $result = $projectId1->equals($projectId2);

        // then
        // equalsメソッドがtrueを返すことを確認
        $this->assertTrue($result);
    }

    public function test_他のプロジェクトIDと等しくない場合にequalsメソッドがfalseを返す()
    {
        // given
        $projectId1 = ProjectId::reconstruct('TA-P-123456');
        $projectId2 = ProjectId::reconstruct('TA-P-654321');

        // when
        $result = $projectId1->equals($projectId2);

        // then
        // equalsメソッドがfalseを返すことを確認
        $this->assertFalse($result);
    }
}