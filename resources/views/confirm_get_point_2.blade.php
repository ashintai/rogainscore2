<!-- 本番用　取得写真の登録確認画面

<!-- 取得写真の登録確認画面 
次のパラメータを受け取る
flag:0　新規登録したので確認だけー＞get_pointへ　1　ダブったので、変更するか確認する　このまま変更ー＞confirm_get_pointへ　戻るー＞get_pointへ
set_point_no:設定ポイント番号
set_point_name:設定ポイント名
set_photo_filename:設定ポイント写真
get_point_id:ロックオンされているレコードのid
get_photo_filename:ロックオンされている写真（仮登録または一覧）
before_photo_filename:だぶっている場合、前の写真 -->
    

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous">
    </script>
    
    <title>モリコロロゲイン２</title>
</head>
<body>
<header>
    <h5 style="color: blue;">モリコロロゲイニング　ポイント通過登録</h5>
    <h6>チーム番号:{{ $user->team_no }} {{ $user->name }}<h6>
    <!-- ログアウト -->
    <form action="{{ route('logout') }}" method="POST" >
        @csrf
        <button type="submit" style="float: right;">ログアウト</button>
    </form>
    <br>
    <hr>
</header>

<!-- ポイント番号とポイント名 -->
<!-- <p>設定ポイント番号: {{ $set_point_no }}</p>
<p>設定ポイント名: {{ $set_point_name }}</p> -->

<h6 style="color: red;">このポイントにはすでに下の写真が登録されています！</h6>
<!-- 前の写真 -->
@if ( $before_photo_filename )
    <!-- <p>前の写真: {{ $before_photo_filename }}</p> -->
    <div class="d-flex justify-content-center">
        <img src="{{ $before_photo_filename }}" alt="前の写真" style="max-width: 80%;">
    </div>
@endif
<hr>
<h6 style="color: red;">{{$set_point_no}}：{{$set_point_name}}として新しくこの写真を登録しますか？</h6>

<!-- 取得ポイントの写真 -->
@if ( $get_photo_filename )
    <!-- <p>取得写真: {{ $get_photo_filename }}</p> -->
    <div class="d-flex justify-content-center"> 
        <img src="{{ $get_photo_filename }}" alt="取得写真" style="max-width: 80%;">
    </div>
@endif
<br>
<h6 style="color: red;">{{$set_point_no}}：{{$set_point_name}}の見本写真は以下です。</h6>
<!-- 設定ポイントの写真 -->
@if ( $set_photo_filename )
    <!-- <p>設定ポイント写真: {{ $set_photo_filename }}</p> -->
    <div class="d-flex justify-content-center">
        <img src="{{ $set_photo_filename }}" alt="設定ポイント写真" style="max-width: 80%;" >
    </div>
@endif
<div class="container mt-3">
    <div style="display: flex; justify-content: space-between; align-items: center;">
    <!-- このまま変更ボタン -->
    <!-- flag=1でcomfirm_get_pointへ戻る -->
        <form action="{{ route('confirm_get_point') }}" method="POST">
            @csrf
            <input type="hidden" name="flag" value="1">
            <input type="hidden" name="set_point_no" value="{{ $set_point_no }}">
            <input type="hidden" name="get_point_id" value="{{ $get_point_id }}">
            <button type="submit" class="btn btn-primary" >このまま変更</button>
        </form>

        <!-- 戻るボタン -->
        <!-- 変更せずにget_point画面で戻る　flag=1 取得写真が仮登録された状態 -->
        <form action="{{ route('store_session_data') }}" method="POST">
            @csrf
            <input type="hidden" name="flag" value="0">
            <input type="hidden" name="set_point_no" value="{{ $set_point_no }}">
            <input type="hidden" name="get_point_id" value="{{ $get_point_id }}">
            <button type="submit" class="btn btn-primary" >変更せず戻る</button>
        </form>
    </div>
</div>
<h8>■「このまま変更」の場合は「写真一覧＆編集」から前の写真のポイント番号を修正してください。</h8>
<br>
<h8>■「変更せず戻る」の場合は、次の画面でポイント番号を変更してください。</h8>
<hr>
<footer style="text-align: right;">
    <h8>© 2025 (特非)愛知県オリエンテーリング協会</h8>
</footer>

</body>
</html>
