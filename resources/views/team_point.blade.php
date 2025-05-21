<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous">
    </script>
        <style>
        .form-group {
            display: block;
        }
        .form-group label {
            margin-right: 10px;
        }
        hr {
            margin-top: 4px !important;
            margin-bottom: 4px !important;
        }
    </style>
    <title>モリコロロゲイン</title>
</head>
<body>

<!-- アラームの表示 -->
@if(session('message'))
    <script>
        alert("{{ session('message') }}");
    </script>
@endif

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
    <div class="d-flex align-items-center" style="gap: 8px;" >
    
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
            <p class="btn btn-success rounded-circle ms-3">手入力</p>
        @endif
    <!-- ポイント番号とポイント名を表示 -->
        <h6 class="mb-0" style="margin-left: 8px;" >{{ $point->point_no }}:{{ $point->setPoint->point_name }}</h6>
    

    <!-- 写真 または　削除　-->
    <!-- cheked=5 の手入力の場合は　削除、写真がある場合は　写真を表示 -->
        <div class="ms-auto">    
        @if($point->checked == 5)
                <a href="{{ route('team_point_delete', ['get_id' => $point->id, 'user_id' => $user->id  ]) }}" class="btn btn-danger">削除</a>
            @else
                <a href="{{ route('team_point_photo', ['id' => $point->id, ]) }}" class="btn btn-success">写真</a>
            @endif
            <!-- 状態変更 -->
            <!-- @if($point->checked == 0)
                <a href="{{ route('team_point_change_ok', ['id' => $point->id, ]) }}" class="btn btn-primary">okに変更</a>
                <a href="{{ route('team_point_change_ng', ['id' => $point->id, ]) }}" class="btn btn-danger">NGに変更</a>            
            @elseif($point->checked == 1)
                <p>確認中のため編集不可<p>
            @elseif($point->checked == 2)    
                <a href="{{ route('team_point_change_mikaku', ['id' => $point->id, ]) }}" class="btn btn-primary">未確認に変更</a>
                <a href="{{ route('team_point_change_ng', ['id' => $point->id, ]) }}" class="btn btn-danger">NGに変更</a>
            @elseif($point->checked == 3)
                <a href="{{ route('team_point_change_mikaku', ['id' => $point->id, ]) }}" class="btn btn-primary">未確認に変更</a>
                <a href="{{ route('team_point_change_ng', ['id' => $point->id, ]) }}" class="btn btn-success">NGに変更</a>
            @elseif($point->checked == 4)
                <p>ポイント番号不明のため編集不可</p>
            @endif -->
        </div>
    </div>
    <hr>
    @endif
@endforeach

<!-- 手入力 -->
<!-- ポイント番号を手入力する -->
<h5>手入力でポイント番号を追加したい場合はポイント番号を入れ、「入力」をタップ後、ポイント名を確認して「登録」をタップ</h5>

<form class="ms-4" action="{{ route('team_point_input' , ['id' => $user->id ] ) }}" method="POST" >
    @csrf
    <div class="form-group">
        <div class="mb-3">
            <label for="point_no">ポイント番号:</label>
            <input type="text" id="point_no" name="point_no" required maxlength="4" inputmode="numeric" pattern="\d*" size="5">
            <button type="button" id = "button_input" class="btn btn-primary">入力</button>
        </div>
        <div style="display:flex;" >
            <label for="point_name_disp" class="align-middle">ポイント名:</label>
            <p id="point_name_disp" class="align-middle">  ポイント名 </p>
            <button type="submit" id = "button_submit" class="btn btn-success ms-4" style="display:none;" >登録</button>
        </div>
        <div>
            <p class="align-middle">手入力のポイント番号は、OKとして登録されます。</p>
        </div>
    </div>
</form>

<!-- 戻る -->
<div class="ms-3">
    <a href="{{ route('staff_main') }}" class="btn btn-primary">戻る</a>
</div>
<hr>
<footer style="text-align: right;">
    <h8>© 2025 (特非)愛知県オリエンテーリング協会</h8>
</footer>

</body>

<script>
    // PHPからポイント番号とポイント名の対応表をJavaScriptに渡す
    const pointList = @json($set_point_list);
    
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('button_input').addEventListener('click', function() {
            const inputNo = document.getElementById('point_no').value;
            if(pointList[inputNo]){
                name = pointList[inputNo];
                document.getElementById('button_submit').style.display = 'inline-block'; // 表示
            }else{
                name = '該当なし';
                document.getElementById('button_submit').style.display = 'none'; // 非表示
            }
            document.getElementById('point_name_disp').textContent = name;
        });
    });
</script>



</html>
