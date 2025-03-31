<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ユーザー登録</title>
</head>
<body>
    <h1>ユーザー登録</h1>
    <form action="/profile/create" method="post">
        @csrf
        <div><input type="text" name="userName" placeholder="ユーザー名" value="{{ old('userName', '') }}"></div>
        <div><input type="text" name="selfIntroductionText" placeholder="自己紹介文" value="{{ old('selfIntroductionText', '') }}"></div>
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div><input type="submit" value="送信"></div>
    </form>
</body>
</html>