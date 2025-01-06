<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous">
    </script>
    <style>
        .image-container img {
            width: 80%; /* 画像の幅を画面の80%に設定 */
            height: auto; /* アスペクト比を維持 */
        }
    </style>
    
    <title>モリコロロゲイン</title>
</head>
<body>
<header>
    <h5 style="color: blue; margin-left: 20px;">モリコロロゲイニング　通過ポイント一覧</h5>
    <h6 style="margin-left: 20px;">チーム番号:{{ $user->team_no }} {{ $user->name }}<h6>
    <!-- ボタンを横並びに表示 -->
    <div style="display: flex; justify-content: space-between; align-items: center;">
    <!-- 戻るボタン -->
    <!-- get_point画面で戻る　flag=0で初期状態画面へ -->
        <form action="{{ route('store_session_data') }}" method="POST" style="margin-left: 20px;">
            @csrf
            <input type="hidden" name="flag" value="0">
            <input type="hidden" name="set_point_no" value="0">
            <input type="hidden" name="get_point_id" value="0">
            <button type="submit" class="btn btn-primary" >戻る</button>
        </form>
    <!-- ログアウトボタン -->
        <form action="{{ route('logout') }}" method="POST" style="margin-right: 20px;">
            @csrf
            <button type="submit" style="float: right;">ログアウト</button>
        </form>
    </div>
    <hr>
</header>

<body>
    @foreach($get_points as $get_point)
        <div style="display: flex; align-items: center;">
        @if($get_point->checked == 0)
            <h6 class="ms-3">ポイント番号: {{ $get_point->point_no }} -  {{ $get_point->setPoint->point_name}}</h6>
            <p class="btn btn-secondary rounded-circle ms-3" >確認待ち</p>
        @elseif($get_point->checked == 1)
            <h6 class="ms-3">ポイント番号: {{ $get_point->point_no }} -  {{ $get_point->setPoint->point_name}}</h6>
            <p class="btn btn-warning rounded-circle ms-3"> 確認作業中</p>
        @elseif($get_point->checked == 2)
            <h6 class="ms-3">ポイント番号: {{ $get_point->point_no }} -  {{ $get_point->setPoint->point_name}}</h6>
            <p class="btn btn-success rounded-circle ms-3" >OK</p>
        @elseif($get_point->checked == 3)
            <h6 class="ms-3">ポイント番号: {{ $get_point->point_no }} -  {{ $get_point->setPoint->point_name}}</h6>
            <p class="btn btn-danger rounded-circle ms-3">NG</p>
        @elseif($get_point->checked == 4)
            <p class="btn btn-dark rounded-circle ms-3">ポイント番号不明</p>
        @endif
        </div>
        <div class="image-container d-flex justify-content-center">
            <img src="{{ $get_point->photo_filename }}" alt="取得写真">
        </div>
        
        <!-- ポイント番号入れ替えボタン -->
        <!-- get_point_id と　set_point_no をget_point画面へ行く -->
        <!-- 確認中は番号変更ボタンは表示しない -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-left:5px">
            <div>
                @if($get_point->checked != 1)
                    <form action="{{ route('store_session_data') }}" method="POST" class="mt-1">
                    @csrf
                        <input type="hidden" name="flag" value="0" >
                        @if ($get_point->checked == 4)
                        <!-- ポイント番号不明の場合はset_point_no=0で、store_session_dataで第一レコードへ変換 -->
                            <input type="hidden" name="set_point_no" value="0" >
                        @else
                            <input type="hidden" name="set_point_no" value="{{ $get_point->point_no }}" >
                        @endif
                        <input type="hidden" name="get_point_id" value="{{ $get_point->id }}">
                        <button type="submit" class="btn btn-primary ms-3" >ポイント番号変更</button>
                    </form> 
                @endif
            </div>
            <div>  
                <form action="{{ route('download_get_photo') }}" method="POST" class="mt-1" style="margin-right: 10px;">
                    @csrf
                    <input type="hidden" name="filename" value="{{ $get_point->photo_filename }}">
                    <button type="submit" class="btn btn-primary ms-3" >この写真をダウンロード</button>  
                </form>
                    <!-- <a href="{{ route('download_get_photo', ['filename' => urlencode( $get_point->photo_filename ) ]) }}" class="btn btn-primary">この写真をダウンロード</a> -->
            </div>
        </div>
        <hr>

    @endforeach

<!-- 戻るボタン -->
<!-- get_point画面で戻る　flag=0で初期状態画面へ -->
    <form action="{{ route('store_session_data') }}" method="POST" class="d-flex justify-content-center">
        @csrf
        <input type="hidden" name="flag" value="0">
        <input type="hidden" name="set_point_no" value="0">
        <input type="hidden" name="get_point_id" value="0">
        <button type="submit" class="btn btn-primary" >戻る</button>
    </form>
    <hr>
    <footer style="text-align: right;">
        <h8>© 2025 (特非)愛知県オリエンテーリング協会</h8>
    </footer>
</body>
</html>   


