<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous">
    </script>
    
    <title>モリコロロゲイン</title>
    
</head>
<body>

<header>
    <h5 style="color: blue; margin-left: 20px;">モリコロロゲイニング　ログイン待機</h5>
    
        <!-- ログアウトボタン -->
        <form action="{{ route('logout') }}" method="POST" style="margin-right: 10px;">
            @csrf
            <button type="submit" style="float: right;">ログアウト</button>
        </form>
    <br>
    <hr>
</header>

<body>
    <h5 style="text-align: center;">ただいまの時間はログインできません。</h5>
    <h5 style="text-align: center;">スタート直前にログインできます。しばらくお待ちください。</h5>
    <form action="{{ route('logout') }}" method="POST" style="text-align: center;">
        @csrf
        <button type="submit" class="btn btn-primary" >ログアウト</button>
    </form>

</body>
