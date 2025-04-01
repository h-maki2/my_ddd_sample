<?php

use packages\domain\model\authToken\AccessToken;
use packages\domain\model\authToken\AccessTokenExpiration;
use PHPUnit\Framework\TestCase;

class AccessTokenTest extends TestCase
{
    public function test_正常なアクセストークン値とアクセストークンの有効期限を入力した場合に、インスタンスを生成できる()
    {
        // given
        // 有効期限は30分後の場合
        $expirationTimeStamp = 1800;
        $accessTokenValue = 'access_token_value';

        // when
        $accessToken = new AccessToken($accessTokenValue, AccessTokenExpiration::create($expirationTimeStamp));

        // then
        // アクセストークンの値が正しく設定されていることを確認
        $this->assertEquals($accessTokenValue, $accessToken->value);

        // 有効期限は切れていないことを確認
        $this->assertFalse($accessToken->isExpired());
    }

    public function test_アクセストークン値が空文字列の場合に、例外が発生する()
    {
        // given
        $accessTokenValue = '';
        $expirationTimeStamp = 1800;

        // when・then
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('アクセストークン値が空です。');
        new AccessToken($accessTokenValue, AccessTokenExpiration::create($expirationTimeStamp));
    }
}