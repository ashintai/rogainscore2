<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous">
    </script>
    
    <style>
        .user-info {
            justify-content: space-between;
            align-items: center;
        }
        .user-info p {
            margin: 0;
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
    <h5 style="color: blue;">モリコロロゲイニング　スタッフ操作</h5>
    <!-- ログアウトボタン -->
    <form action="{{ route('logout') }}" method="POST" >
        @csrf
            <button type="submit" style="float: right;">ログアウト</button>
    </form>
    <!-- 戻る -->
    <div class="ms-3">
        <a href="{{ route('staff_main') }}" class="btn btn-primary">戻る</a>
    </div>
    
    <hr>
</header>

@foreach($users as $user)
    <div class="user-info">
        <p>{{ $user->team_no }}:{{ $user->name }}({{ $user->member_num }}名)-{{ $user->category->category_name }}/{{$user->category->class_name}}：
        @if($state[$user->team_no] !== '未確認数：0')
            <span class="text-danger">{{ $state[$user->team_no] }}</span>
        @else
            {{ $state[$user->team_no] }}
        @endif    
        </p>
        <div class="ms-auto" style ="display: flex; justify-content: flex-end ; gap: 8px;">
            <a href="{{ route('team_edit', $user->id) }}" class="btn btn-primary">登録情報編集</a>
            @if($user->role == 0)
                <a href="{{ route('team_point', $user->id) }}" class="btn btn-success">通過ポイント編集</a>
            @elseif($user->role == 3)
                <!-- 強制ロック解除は 確認してから-->
                <form action="{{ route('team_point_unlock', $user->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('この操作は行わないでください。本当に強制ロック解除しますか？');">
                    @csrf
                    <button type="submit" class="btn btn-danger">強制ロック解除</button>
                </form>
            @endif
        </div>
    </div>
    <hr>
@endforeach

<!-- 戻る -->
<div class="ms-3">
    <a href="{{ route('staff_main') }}" class="btn btn-primary">戻る</a>
</div>
<hr>
<footer style="text-align: right;">
    <h8>© 2025 (特非)愛知県オリエンテーリング協会</h8>
</footer>










</body>
</html>
