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
    <h5 style="color: blue;">モリコロロゲイニング　スタッフ操作</h5>
    <!-- ログアウトボタン -->
    <form action="{{ route('logout') }}" method="POST" >
        @csrf
            <button type="submit" style="float: right;">ログアウト</button>
    </form>
    <br>
    <hr>
</header>

<body>
<div class="ms-2">
    <!-- 現状表示 -->
    <h6>未確認数：{{ $unchecked_count }}／総数：{{ $total_count }}（確認中：{{ $checking_count }}）</h6>
    <!-- 未確認写真判定ボタン -->
    <a href="{{ route('next_get_point') }}" class="btn btn-primary">未確認写真判定</a>
    <br><br>
    <!-- 成績速報ボタン -->
    <a href="{{ route('result') }}" class="btn btn-primary">成績速報</a>
    <br><br>
    <!-- 減点入力ボタン -->
    <a href="{{ route('input_penalty') }}" class="btn btn-primary">減点入力</a>
    <br><br>
    <!-- 確認中リセットボタン -->
    <a href="{{ route('reset_checking') }}" class="btn btn-primary">確認中リセット</a>
    <br><br>
    <!-- NGリセットボタン -->
    <a href="{{ route('reset_ng') }}" class="btn btn-primary">NGリセット</a>
</div>
<hr>
<footer style="text-align: right;">
    <h8>© 2025 (特非)愛知県オリエンテーリング協会</h8>
</footer>
</body>
</html>
