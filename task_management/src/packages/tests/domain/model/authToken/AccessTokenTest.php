<?php

use packages\domain\model\authToken\AccessToken;
use PHPUnit\Framework\TestCase;

class AccessTokenTest extends TestCase
{
    public function test_正常なアクセストークン値とアクセストークンの有効期限を入力した場合に、インスタンスを生成できる()
    {
        // given
        // 有効期限が今から1分後の場合
        $now = new DateTimeImmutable();
        $oneMinuteLater = $now->modify('+1 minute');
        $accessTokenValue = 'access_token_value';

        // when
        $accessToken = new AccessToken($accessTokenValue, $oneMinuteLater->getTimestamp());

        // then
        // アクセストークンの値が正しく設定されていることを確認
        $this->assertEquals($accessTokenValue, $accessToken->value);

        // 有効期限は切れていないことを確認
        $this->assertFalse($accessToken->isExpired());
    }

    public function test_有効期限が切れているタイムスタンプを使ってインスタンスを生成した場合に、例外が発生する()
    {
        // given
        // 有効期限が切れている場合
        $now = new DateTimeImmutable();
        $oneMinuteAgo = $now->modify('-1 minute');
        $accessTokenValue = 'access_token_value';

        // when・then
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('アクセストークンの有効期限が切れています。');
        new AccessToken($accessTokenValue, $oneMinuteAgo->getTimestamp());
    }

    public function test_アクセストークン値が空文字列の場合に、例外が発生する()
    {
        // given
        $accessTokenValue = '';
        $now = new DateTimeImmutable();
        $oneMinuteLater = $now->modify('+1 minute');

        // when・then
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('アクセストークン値が空です。');
        new AccessToken($accessTokenValue, $oneMinuteLater->getTimestamp());
    }
}