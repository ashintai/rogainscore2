<!-- 本番用改良　一覧表からポイント番号変更の画面 -->
<!-- 以下のパラメータを受ける -->
<!-- set_points:設定ポイント一覧データ -->
<!-- user: ログインしているユーザーレコード -->
<!-- set_point_no 現在の設定ポイント番号 -->
<!-- get_point_id　ロックオンされているget_pointのid -->
<!-- get_photo_filename ロックオンされているGetファイル名 -->

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous">
    </script>
     <!-- ｊQueryの読み込み -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    
    <title>モリコロロゲイン２</title>
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
        #preview {
        display: none;}
    </style>
    
</head>
<body>

<header>
    <h5 style="color: blue; margin-left: 20px;">モリコロロゲイニング　ポイント番号変更</h5>
    <h6 style="margin-left: 20px;">チーム番号:{{ $user->team_no }} {{ $user->name }}</h6>
    <!-- 各ボタンを横並びに表示 -->
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <!-- flag= 0 でで戻るボタン -->
        <a href="{{ route('all_images' , ['flag' => 0 ] ) }}" class="btn btn-primary">戻る</a>
        <br>
        <!-- ログアウトボタン -->
        <form action="{{ route('logout') }}" method="POST" style="margin-right: 10px;">
            @csrf
            <button type="submit" style="float: right;">ログアウト</button>
        </form>
    </div>
    <hr>
</header>

<div class="container mt-1">
    <h6 style="color: blue;">変更するポイントをリストから選択してください</h6>
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

<!-- 取得写真の表示  -->
<h6 style="color: blue;">ポイント番号を変更する写真</h6>
@if ( $get_photo_filename )
    <div class="d-flex justify-content-center mt-3">
        <img src="{{ $get_photo_filename }}" alt="取得ポイント写真" style="max-width: 80%;" >
    </div>
@endif

<hr>

<!-- 変更ボタン -->
<form action="{{ route('all_images_change_get') }}" method="post" id="imageForm"  >
        @csrf
        <!-- 設定ポイント番号を送る -->
        <input type="hidden" id="selected_point" name="set_point_no" >
        <input type="hidden" id="get_point" name="get_point_id"  value ="{{ $get_point_id }}">
        <div class="d-flex justify-content-center mt-3">
            <input type="submit" class="btn btn-primary ms-3"  value="ポイント番号変更" />
        </div>
</form>


<footer style="text-align: right;">
    <h8>© 2025 (特非)愛知県オリエンテーリング協会</h8>
</footer>

<!-- 設定ポイント写真の切り替えjavascript -->
<script>
    async function getImageUrl(key) {
        const response = await fetch(`/image-url?key=${key}`);
        const data = await response.json();
        return data.url;
    }

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
                // キャッシュバスターを付与
                const cacheBuster = Date.now();
                imageElement.src = url + '?t=' + cacheBuster;
                });
    }

    // ページロード時にSetポイント番号を現在の設定にする
    document.addEventListener('DOMContentLoaded', function() {
        const select = document.getElementById('point');
        const initialValue = "{{ $set_point_no }}"; // サーバーから渡された値

        // optionsをループして一致するvalueを選択
        for (let i = 0; i < select.options.length; i++) {
            if (select.options[i].value == initialValue) {
                select.selectedIndex = i;
                break;
            }
        }
        updateImage();
    });

    
</script>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
</body>
</html>