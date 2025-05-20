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
    <!-- チーム番号とチーム名を表示 -->
    <h6 style="margin-left: 20px;">チーム番号:{{ $user->team_no }} {{ $user->name }}</h6>
    <!-- ログアウトボタン -->
    <form action="{{ route('logout') }}" method="POST" >
        @csrf
            <button type="submit" style="float: right;">ログアウト</button>
    </form>
    <br>
    <hr>
</header>
@foreach ($get_points as $point)
    @if ($point->setPoint && $point->setPoint->point_name)
    <!-- 通過ポイント番号とポイント名を表示 -->
    <!-- 状態を表示 -->
    <!-- $point->checked 0 未確認 1確認中 2OK 3NG 4仮登録 5手入力＝OK -->
        @if($point->checked == 0)
            <p class="btn btn-secondary rounded-circle ms-3" >確認待ち</p>
        @elseif($point->checked == 1)
            <p class="btn btn-warning rounded-circle ms-3"> 確認作業中</p>
        @elseif($point->checked == 2)
            <p class="btn btn-success rounded-circle ms-3" >OK</p>
        @elseif($point->checked == 3)
            <p class="btn btn-danger rounded-circle ms-3">NG</p>
        @elseif($point->checked == 4)
            <p class="btn btn-dark rounded-circle ms-3">ポイント番号不明</p>
        @elseif($point->checked == 5)
            <p class="btn btn-success rounded-circle ms-3">手入力し</p>
        @endif
<!-- ポイント番号とポイント名を表示 -->
        <p style="margin-left: 8px;">{{ $point->point_no }}:{{ $point->setPoint->point_name }}</p>
        <!-- 編集ボタンを表示 -->
        <!-- 写真 または　削除　-->
        <!-- cheked=5 の手入力の場合は　削除、写真がある場合は　写真を表示 -->
        @if($point->checked == 5)
            <a href="{{ route('team_point_delete', ['get_point_id' => $point->id, ]) }}" class="btn btn-danger">削除</a>
        @else
            <a href="{{ route('team_point_photo', ['get_point_id' => $point->id, ]) }}" class="btn btn-success">写真</a>
        @endif
        <!-- 状態変更 -->
        @if($point->checked == 0)
            <a href="{{ route('team_point_change_ok', ['get_point_id' => $point->id, ]) }}" class="btn btn-primary">okに変更</a>
            <a href="{{ route('team_point_change_ng', ['get_point_id' => $point->id, ]) }}" class="btn btn-danger">NGに変更</a>            
        @elseif($point->checked == 1)
            <p>確認中のため編集不可<p>
        @elseif($point->checked == 2)    
            <a href="{{ route('team_point_change_mikaku', ['get_point_id' => $point->id, ]) }}" class="btn btn-primary">未確認に変更</a>
            <a href="{{ route('team_point_change_ng', ['get_point_id' => $point->id, ]) }}" class="btn btn-danger">NGに変更</a>
        @elseif($point->checked == 3)
            <a href="{{ route('team_point_change_mikaku', ['get_point_id' => $point->id, ]) }}" class="btn btn-primary">未確認に変更</a>
            <a href="{{ route('team_point_change_ng', ['get_point_id' => $point->id, ]) }}" class="btn btn-success">NGに変更</a>
        @elseif($point->checked == 4)
            <p>ポイント番号不明のため編集不可</p>
        @endif
        </div> 
    @endif
    <hr>
@endforeach

    </body>
    </html>
