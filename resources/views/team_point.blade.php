<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous">
    </script>
        <style>
        .form-group {
            display: flex;
            align-items: center;
        }
        .form-group label {
            margin-right: 10px;
        }
    </style>
    <title>モリコロロゲイン</title>
</head>
<body>
<header>
    <h5 style="color: blue;">モリコロロゲイニング　通過ポイント編集</h5>
    <!-- ログアウトボタン -->
    <form action="{{ route('logout') }}" method="POST" >
        @csrf
            <button type="submit" style="float: right;">ログアウト</button>
    </form>
    <br>
    <hr>
</header>
<p>チーム番号：{{ $user->team_no }}</p>
@foreach ($get_points as $point)
<p>
<p>通過ポイント番号：{{ $point->point_no }}</p>
@if ($point->setPoint && $point->setPoint->point_name)
<p>ポイント名：{{ $point->setPoint->point_name }}</p>
@endif
@endforeach

    </body>
    </html>
