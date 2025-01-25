<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous">
    </script>
        
    <title>モリコロロゲイン</title>
    <style>
        .user-row {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .user-row p {
            margin: 0;
            width: 300px; /* 固定幅を設定して揃える */
        }
        .user-row input[type="text"] {
            width: 50px; /* テキスト入力フィールドの幅を指定 */
        }
    </style>
</head>
<body>
<header>
    <h5 style="color: blue;">モリコロロゲイニング　減点入力</h5>
    <!-- ログアウトボタン -->
    <form action="{{ route('logout') }}" method="POST" >
        @csrf
            <button type="submit" style="float: right;">ログアウト</button>
    </form>
    <br>
    <hr>
</header>

<body>

<form action="{{ route('change_penalty') }}" method="POST">
    @csrf
    @foreach( $users as $user)
        <div class="user-row">
            <p>{{ $user->team_no }}:{{ $user->name }}-{{ $user->category->category_name}}コース{{ $user->category->class_name }}クラス</p>
            <input type="number" name="penalties[{{ $user->team_no }}]" value="{{ $user->penalty }}" min="-9999" step="1">
        </div>
    @endforeach

<!-- 変更ボタン -->
    <div class="ms-3">
        <button type="submit" class="btn btn-primary">変更</button>
    </div>
    </form>

<!-- 変更せず戻るボタン -->
<br>
<div class="ms-3">
    <a href="{{ route('staff_main') }}" class="btn btn-primary">変更せず戻る</a>
</div>
<hr>
<footer style="text-align: right;">
    <h8>© 2025 (特非)愛知県オリエンテーリング協会</h8>
</footer>

</body>
</html>
