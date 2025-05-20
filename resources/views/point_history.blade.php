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
    <h5 style="color: blue;">モリコロロゲイニング　ポイント履歴一覧</h5>
    <!-- 戻るボタン -->
    <div class="ms-3">
    <a href="{{ route('staff_main') }}" class="btn btn-primary ">戻る</a>
    </div>
    
    <!-- ログアウトボタン -->
    <form action="{{ route('logout') }}" method="POST" >
        @csrf
            <button type="submit" style="float: right;">ログアウト</button>
    </form>
    <br>
    <hr>
</header>

<body>

@foreach($results as $result)

{{ $result['point_no'] }} {{ $result['point_name'] }}<br>
{{ $result['point_history'] }}
<hr>

@endforeach


</body>
</html>
