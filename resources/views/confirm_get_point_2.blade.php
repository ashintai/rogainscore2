<!-- 本番用　取得写真の登録確認画面
ダブったときだけ呼ばれる
次のパラメータを受け取る
user:ユーザ情報
set_point:Setテーブル
get_point:あたらしく登録しようとしている取得写真のGetテーブル
get_point_before: 前に登録された取得写真のGetテーブル
-->    

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
<h6 style="color: red;">{{$set_point->point_no}}：{{$set_point->point_name}}の見本写真は以下です。</h6>
<!-- 設定ポイントの写真 -->
@if ( $set_photo_filename )
    <!-- <p>設定ポイント写真: {{ $set_photo_filename }}</p> -->
    <div class="d-flex justify-content-center">
        <img src="{{ $set_photo_filename }}" alt="設定ポイント写真" style="max-width: 80%;" >
    </div>
@endif
<hr>
<!-- 前の写真 -->
<h6 style="color: red;">このポイントにはすでに下の写真が登録されています！</h6>
@if ( $get_point_before )
    <div class="d-flex justify-content-center">
        <img src="{{ $get_point_before->photo_filename }}" alt="前の写真" style="max-width: 80%;">
    </div>
@endif
<hr>

<!-- 新しい取得ポイントの写真 -->
<h6 style="color: red;">{{$set_point->point_no}}：{{$set_point->point_name}}として新しくこの写真を登録しますか？</h6>
@if ( $get_point )
    <div class="d-flex justify-content-center"> 
        <img src="{{ $get_point->photo_filename }}" alt="取得写真" style="max-width: 80%;">
    </div>
@endif

<hr>

<div class="container mt-3">
    <div style="display: flex; justify-content: space-between; align-items: center;">
    <!-- このまま変更ボタン -->
    <!-- flag=1でcomfirm_get_pointへ戻る -->
        <form action="{{ route('exchange_get') }}" method="POST">
            @csrf
            <input type="hidden" name="set_point_no" value="{{ $set_point->point_no }}">
            <input type="hidden" name="get_point_id" value="{{ $get_point->id }}">
            <input type="hidden" name="get_point_before_id" value="{{ $get_point_before->id }}">
            <button type="submit" class="btn btn-primary" >このまま変更</button>
        </form>

        <!-- 戻るボタン -->
        <!-- 変更せずにget_point画面で戻る　flag=1 取得写真が仮登録された状態 -->
        <form action="{{ route('get_point' , ['flag' => 1]) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-primary" >変更せず戻る</button>
        </form>
    </div>
</div>
<h8>■「写真一覧＆編集」から前の写真またはこの写真のポイント番号を修正してください。</h8>
<hr>
<footer style="text-align: right;">
    <h8>© 2025 (特非)愛知県オリエンテーリング協会</h8>
</footer>

</body>
</html>
