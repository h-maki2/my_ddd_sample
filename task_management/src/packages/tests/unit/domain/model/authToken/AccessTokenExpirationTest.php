<?php

use packages\domain\model\authToken\AccessTokenExpiration;
use PHPUnit\Framework\TestCase;

class AccessTokenExpirationTest extends TestCase
{
    public function test_アクセストークンの有効期限を正しく設定できる()
    {
        // given
        // 有効期限は30分後の場合
        $expirationTimeStamp = 1800;

        // when
        $accessTokenExpiration = AccessTokenExpiration::create($expirationTimeStamp);

        // then
        // アクセストークンの有効期限が正しく設定されていることを確認
        $now = new DateTimeImmutable();
        $expectedExpirationDateTime = $now->modify('+30 minutes');
        $this->assertEquals($expectedExpirationDateTime->format('Y-m-d H:i'), $accessTokenExpiration->value->format('Y-m-d H:i'));
    }

    public function test_アクセストークンの有効期限が切れた場合に、isExpiredメソッドがtrueを返す()
    {
        // given
        // 有効期限が30分後の場合
        $expirationTimeStamp = 1800;
        $accessTokenExpiration = AccessTokenExpiration::create($expirationTimeStamp);

        // when
        // 有効期限を過ぎた時間を設定
        $now = new DateTimeImmutable();
        $expiredDateTime = $now->modify('+1 hour');
        $isExpired = $accessTokenExpiration->isExpired($expiredDateTime);

        // then
        // アクセストークンの有効期限が切れていることを確認
        $this->assertTrue($isExpired);
    }

    public function test_アクセストークンの有効期限が切れていない場合に、isExpiredメソッドがfalseを返す()
    {
        // given
        // 有効期限が30分後の場合
        $expirationTimeStamp = 1800;
        $accessTokenExpiration = AccessTokenExpiration::create($expirationTimeStamp);

        // when
        // 有効期限内の時間を設定
        $isExpired = $accessTokenExpiration->isExpired(new DateTimeImmutable());

        // then
        // アクセストークンの有効期限が切れていないことを確認
        $this->assertFalse($isExpired);
    }

    public function test_不適切なアクセストークンの有効期限でインスタンスを生成した場合に例外が発生する()
    {
        // given
        // 有効期限のタイムスタンプがマイナスの場合
        $expirationTimeStamp = -1;

        // when・then
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('アクセストークンの有効期限のタイムスタンプ値が不正です。');
        AccessTokenExpiration::create($expirationTimeStamp);
    }

    public function test_reconstructメソッドでインスタンスを再構築できる()
    {
        // given
        $now = new DateTimeImmutable();

        // when
        $accessTokenExpiration = AccessTokenExpiration::reconstruct($now);

        // then
        // 再構築されたインスタンスの値が正しいことを確認
        $this->assertEquals($now->format('Y-m-d H:i:s'), $accessTokenExpiration->stringValue());
    }
}