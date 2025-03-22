<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログイン</title>
</head>
<body>
  <h1>ログイン</h1>
  <form action="/login" method="post">
    @csrf
    <div><input type="text" name="email" placeholder="メールアドレス" value="{{ old('email', '') }}"></div>
    <div><input type="password" name="password" placeholder="パスワード" value="{{ old('password', '') }}"></div>
    @if ($errors->any())
      <div>
        <ul>
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
        </ul>
      </div>
    @endif
    <div><input type="submit" value="ログイン"></div>
  </form>
</body>
</html>