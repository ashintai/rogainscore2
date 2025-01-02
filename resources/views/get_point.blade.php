<!-- View:get_point> -->
<!-- このViewはController:get_pointから以下のパラメータを受け取る
flag:0ログイン直後　1アップロード後　2写真一覧から変更要請　3新規本登録から　4ダブり変更なし　5ダブり変更あり　
set_point_no:表示すべき設定ポイント番号　何もなければ０
set_points:設定ポイント一覧データ
get_point_id:ロックオンされているget_point_id なにもなければ　０
get_photo_filename:ロックオンされている取得写真ファイル名　何もなければnull
user: ログインしているユーザー -->

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous">
    </script>
    
    <title>モリコロロゲイン</title>
    <style>
        /* ドロップダウンリストの高さを制限 */
        select {
            height: auto;
            overflow: hidden;
        }
        select:focus {
            height: auto;
            overflow: auto;
        }
    </style>
</head>
<body>

<!-- 取得写真が登録された場合の確認メッセージ -->
 @if( $flag == 1 )
    <script>
        // JavaScriptでアラートを表示
        alert('写真が登録されました');
    </script>
@endif

<header>
    <h5 style="color: blue;">モリコロロゲイニング　ポイント通過登録</h5>
    <h6>チーム番号:{{ $user->team_no }} {{ $user->name }}</h6>
    <!-- 各ボタンを横並びに表示 -->
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <!-- すべての取得写真の一覧の表示・編集画面 -->
        <a href="{{ route('all_images') }}" class="btn btn-primary">写真一覧&編集</a>
        <!-- 成績速報画面へのボタン -->
        <a href="{{ route('result') }}" class="btn btn-primary">成績速報</a>
        <br>
        <!-- ログアウトボタン -->
        <form action="{{ route('logout') }}" method="POST" >
            @csrf
            <button type="submit" style="float: right;">ログアウト</button>
        </form>
    </div>
    <hr>
</header>

<!-- 入力確認 -->
<!-- <p>フラグ: {{ $flag }}</p>
<p>設定ポイント番号: {{ $set_point_no }}</p>
<p>取得ポイントid: {{ $get_point_id }}</p>
<p>写真ファイル名:{{ $get_photo_filename}} </p> -->
<div class="container mt-1">
    <h6 style="color: blue;">登録するポイントをリストから選択してください</h6>
    <!-- 設定ポイントの選択リスト生成 -->
    <select id="point" onchange="updateImage()" >
        <!-- オプションを動的に生成 -->
        @foreach ($set_points as $set_point)
            <option value="{{ $set_point->point_no }}" data-image-key="set_{{ $set_point->point_no }}.JPG">{{ $set_point->point_no }}: {{ $set_point->point_name }}
            </option>
        @endforeach
    </select>
    <!-- 選択されたポイント写真の表示 -->
    <div class="d-flex justify-content-center mt-3">
        <img id="point-image" src="" alt="ポイント画像" style="max-width: 80%;">
    </div>
</div>
<hr>
<!-- 取得写真の表示　取得写真が送られてきた場合のみ -->
@if ( $get_photo_filename )
    <!-- <p>get_photo_filename: {{ $get_photo_filename }}</p> -->
    <div class="container mt-1">
        <h6 style="color: blue; mt-1">登録しようとしている写真</h6>
        <div class="d-flex justify-content-center mt-3">
            <img src="{{ $get_photo_filename }}" alt="取得写真" style="max-width: 80%;">
        </div>
    </div>
@endif
<!-- アップロードに関するエラー表示 -->
<!-- セッションにエラーが記録されている -->    
@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    <div>
        <img src="{{ session('image_url') }}" alt="アップロードされた画像" style="max-width: 100%;">
    </div>
@endif
<!-- Uploadが失敗の場合は、セッションにエラーが記録されている -->
@if ($errors->any())
    <div class="alert alert-danger">
        アップロードに失敗しました。もう一度お試しください。
        <!-- <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul> -->
    </div>
@endif

<!-- アップロードボタンまたは登録ボタン -->
@if ($get_point_id)
<!-- 取得ポイントが送られたきた場合は登録ボタン および登録せず戻るボタン -->
<!-- このボタンを押すと、現在選択されている設定ポイント番号set_point_noとロックオンされている取得ポイントget_point_idが結びつく -->
<div class="container mt-1">
    <div style="display: flex; justify-content: space-between; align-items: center;" >
        <form id="upload-form" action="{{ route('confirm_get_point') }}" method="POST" >
            @csrf
            <input type="hidden" id="selected_point" name="set_point_no" >
            <input type="hidden" name="get_point_id" value = "{{ $get_point_id }}" >
            <input type="hidden" name="flag" value = "0" >
            <button type="submit" class="btn btn-primary">登録する</button>
        </form>
        <!-- 登録せず戻るボタン -->
        <!-- get_point画面で戻る　flag=0で初期状態画面へ -->
        <form action="{{ route('store_session_data') }}" method="POST">
            @csrf
            <input type="hidden" name="flag" value="0">
            <input type="hidden" name="set_point_no" value="1">
            <input type="hidden" name="get_point_id" value="0">
            <button type="submit" class="btn btn-primary" >登録せず戻る</button>
        </form>
    </div>
</div>
@else
<!-- ファイル選択とアップロード ボタン-->
    <div class="container mt-1">
        <h6 style="color: blue;">写真のアップロード</h6>
        <!-- $image に画像データ　を入れて H.upload_imageへ-->
        <form id="upload-form" action="{{ route('upload_image') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <input type="hidden" id="selected_point" name="set_point_no">
                <label for="image" class="form-label">画像を選択または撮影し「アップロード」ボタンをタップしてください。</label>
                <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
            </div>
            <button type="submit" class="btn btn-primary">アップロード</button>
        </form>
    </div>
@endif

<hr>

<footer style="text-align: right;">
    <h8>© 2025 (特非)愛知県オリエンテーリング協会</h8>
</footer>

<script>
    async function getImageUrl(key) {
        const response = await fetch(`/image-url?key=${key}`);
        const data = await response.json();
        return data.url;
    }

    // セッションで渡される設定ポイント番号を変数に取得
    const pointno = @json($set_point_no);

    function updateImage() {
        // 現在選択されている設定ポイントを取得
        const select = document.getElementById('point');
        const selectedValue = select.value;

        // console.log('Selected Value:', selectedValue);
        // 選択されている設定ポイント番号をformのhidden input にセット    
        document.getElementById('selected_point').value = selectedValue;

        // console.log('Hidden Field Value:', document.getElementById('selected_point').value);

            const selectedOption = select.options[select.selectedIndex];
            const key = selectedOption.getAttribute('data-image-key');
            getImageUrl(key).then(url => {
                const imageElement = document.getElementById('point-image');
                imageElement.src = url;
            });
    }

    // ページロード時に最初の画像を表示
    document.addEventListener('DOMContentLoaded', function() {
        const select = document.getElementById('point');
        if (pointno){
            // セッションに設定ポイントが存在する場合、その値
            select.value = pointno;
        }
        updateImage();
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
</body>
</html>