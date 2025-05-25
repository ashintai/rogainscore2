<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous">
    </script>
    <title>モリコロロゲイン</title>
    <!-- スタッフがポイント一覧から写真を選んで状態を変化させるための画面 -->
    <!-- ２つの写真を横にならべて表示するためのcss -->
    <style>
        .image-container {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;/* 画像の上端を揃える */
            flex-wrap: wrap;/* 画像が横に並びきれない場合は次の行に移動 */
        }

        .responsive-image{
            width: 350px;/* 画像の最大幅を設定 */
            height: auto;/* 高さを自動に設定して縦横比を維持 */
            object-fit: contain;/* 画像の縦横比を維持しながらコンテナに収める */
        }

        @media (max-width: 768px) {
            .responsive-image {
                max-width: 100%; /* 画像の最大幅を100%に設定 */
                margin-bottom: 10px;
            }
        }

        /* .image-container img {
            width: 48%; /* 画像の幅を調整 
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
    <h5 style="color: blue; margin-left: 20px;">モリコロロゲイニング　判定変更</h5>
    <!-- チェック中はログアウトや他のページに遷移しない -->
    <h6 style="color: red; margin-left: 10px;">ブラウザの再読み込み、戻るを行わないでください！！</h6>
    <hr>
</header>  
    
    <!-- 渡されたデータを表示 -->
    <p>チーム番号: {{ $team_no }}：{{ $team_name }}<br>
    ポイント番号: {{ $point_no }}：{{ $point_name }}</p>
    
        <!-- 状態表示 -->
        @if($checked == 0)
            <p class="btn btn-secondary rounded-circle ms-3" >確認待ち</p>
        @elseif($checked == 1)
            <p class="btn btn-warning rounded-circle ms-3"> 確認作業中</p>
        @elseif($checked == 2)
            <p class="btn btn-success rounded-circle ms-3" >OK</p>
        @elseif($checked == 3)
            <p class="btn btn-danger rounded-circle ms-3">NG</p>
        @elseif($checked == 4)
            <p class="btn btn-dark rounded-circle ms-3">ポイント番号不明</p>
        @elseif($checked == 5)
            <p class="btn btn-success rounded-circle ms-3">手入力OK</p>
        @endif

    <div style="display: flex">
        <div style="width: 45%; margin-left:5px;">
            <p>取得写真</p>
            <div >
                <img src="{{ $get_photo_url }}" alt="取得写真がありません" style="width: 100%; height:auto;">
            </div>
        </div>
        <div style="width: 45%; margin-left:5px;" >
            <p>見本写真</p>
            <div >
                <img src="{{ $set_photo_url }}" alt="正解写真がありません" style="width: 100%; height:auto;">
            </div>
        </div>
    </div>
    <!-- <div class="container">
        <div class="text-container">
            <p>取得写真</p>
            <p>正解写真</p>
        </div>
        <div class="image-container">
            <img src="{{ $get_photo_url }}" alt="取得写真" class="responsive-image">
            <img src="{{ $set_photo_url }}" alt="正解写真" class="responsive-image">
        </div>
    </div> -->

    <hr>
    <!-- 判定ボタン -->
<div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
    <form action="{{ route('team_point_change') }}" method="POST" style="flex: 1; text-align: left; margin: 0 16px;">
        @csrf
        <input type="hidden" name="user_id" value="{{ $user_id }}">
        <input type="hidden" name="get_id" value="{{ $get_id }}">
        <input type="hidden" name="flag" value=2 >
        <button type="submit" name="result" value="ok" class="btn btn-success" >ＯＫ</button>
    </form>
    
    <form action="{{ route('team_point_change') }}" method="POST" style="flex: 1; text-align: center;">
        @csrf
        <input type="hidden" name="user_id" value="{{ $user_id }}">
        <input type="hidden" name="get_id" value="{{ $get_id }}">
        <input type="hidden" name="flag" value=1 >
        <button type="submit" name="result" value="ok" class="btn btn-primary" >未確認</button>
    </form>

    <form action="{{ route('team_point_change') }}" method="POST" style="flex: 1; text-align: right; margin: 0 16px;">
        @csrf
        <input type="hidden" name="user_id" value="{{ $user_id }}">
        <input type="hidden" name="get_id" value="{{ $get_id }}">
        <input type="hidden" name="flag" value=3 >
        <button type="submit" name="result" value="ok" class="btn btn-danger" >ＮＧ</button>
    </form>

    </div>

    <hr>
        <form action="{{ route('team_point_change') }}" method="POST" style="text-align: center;">
        @csrf
        <input type="hidden" name="user_id" value="{{ $user_id }}">
        <input type="hidden" name="get_id" value="{{ $get_id }}">
        <input type="hidden" name="flag" value=0 >
        <button type="submit" name="result" value="ok" class="btn btn-primary" >戻る</button>
    </form>
        
    <hr>
    <footer style="text-align: right;">
        <h8>© 2025 (特非)愛知県オリエンテーリング協会</h8>
    </footer>

</body>
</html>