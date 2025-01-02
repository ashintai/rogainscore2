<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous">
    </script>
    <title>モリコロロゲイン</title>
    <!-- ２つの写真を横にならべて表示するためのcss -->
    <style>
        .image-container {
            display: flex;
            justify-content: space-between;
        }

        .responsive-imag{
            max-width: 45%;
            height: auto;
        }

        /* .image-container img {
            width: 48%; /* 画像の幅を調整 */
            height: auto;
            object-fit: cover; 
        } */
        .text-container {
            display: flex;
            justify-content: space-between;
        }
        .text-container p {
            width: 48%; /* テキストの幅を調整 */
        }
        .button-container {
            display: flex;
            justify-content: flex-end;
            gap: 20px; /* ボタンの間のスペースを設定 */
        }
    </style>
</head>
<body>
<header>
    <h5 style="color: blue;">モリコロロゲイニング　写真判定</h5>
    <!-- チェック中はログアウトや他のページに遷移しない -->
    <h6 style="color: red;">ブラウザの再読み込み、戻るを行わないでください！！</h6>
    <hr>
</header>  
    
    <!-- 渡されたデータを表示 -->
    <p>チーム番号: {{ $team_no }}：{{ $team_name }}</p>
    <p>ポイント番号: {{ $next_point->point_no }}：{{ $set_point_name }}</p>
    <div class="container">
        <div class="text-container">
            <p>取得写真</p>
            <p>正解写真</p>
        </div>
        <div class="image-container">
            <img src="{{ $get_photo_url }}" alt="取得写真" class="responsive-image">
            <img src="{{ $set_photo_url }}" alt="正解写真" class="responsive-image">
        </div>
    </div>

    <!-- 判定ボタン -->
    <form action="{{ route('change_checked') }}" method="POST">
        @csrf
        <br>
        <input type="hidden" name="get_id" value="{{ $next_point->id }}">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-left:5px">
            <button type="submit" name="result" value="ok" class="btn btn-success">ＯＫ</button>
            <button type="submit" name="result" value="ng" class="btn btn-danger">ＮＧ</button>
            <!-- <button type="submit" name="result" value="skip" class="btn btn-success">保留</button> -->
        </div>
        </form>
    <hr>
    <!-- <p>設定写真: {{ $set_photo_url }}</p>
    <p>取得写真: {{ $get_photo_url }}</p> -->
    <!-- 写真判定終了ボタン     -->
    <form action="{{ route('change_checked') }}" method="POST">
        @csrf
        <input type="hidden" name="get_id" value="{{ $next_point->id }}">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-left:5px">
            <button type="submit" name="result" value="cancel" class="btn btn-primary">写真判定終了</button>
        </div>
    </form>
    <h6 style="color: red;">終了するとこの写真は未確認のままになります。</h6>
        
    <hr>
    <footer style="text-align: right;">
        <h8>© 2025 (特非)愛知県オリエンテーリング協会</h8>
    </footer>

</body>
</html>