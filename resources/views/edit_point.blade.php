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
        <h5>モリコロロゲイニング　ポイント番号修正</h5>
        <h6>チーム番号:{{ $user->team_no }} {{ $user->name }}<h6>
    <form action="{{ route('logout') }}" method="POST" >
        @csrf
        <button type="submit" style="float: right;">ログアウト</button>
    </form>
    <br>
    <!-- 戻るボタン -->
    <hr>
    </header>

        <p>{{$get_point->id}}</p>
        <p>{{$get_point->team_no}}</p>
        <p>{{$get_point->point_no}}</p>
        <p>{{$get_point->checked}}</p>
        <p>{{$get_point->photo_filename}}</p>
        <div class="image-container">
            <img src="{{ $get_point->photo_filename }}" alt="取得写真">
        </div>
        
    <!-- 変更後のポイント番号を選択 -->
    <!-- ドロップダウンリスト設定 -->
        <hr>
        <h5>変更後のポイントをリストで選択して[変更]ボタンをタップしてください</h5>
        <select id="point" onchange="updateImage()">
            <!-- オプションを動的に生成 -->
            @foreach ($set_points as $set_point)
                <option value="{{ $set_point->id }}" data-image-key="set_{{ $set_point->point_no }}.JPG">{{ $set_point->point_no }}: {{ $set_point->point_name }}
                </option>
            @endforeach
        </select>
        <div class="image-container">
            <img id="point-image" src="" alt="ポイント画像" style="max-width: 100%;">
        </div>
        <!-- 変更ボタン -->
        <form id="upload-form" action="{{ route('change_point') }}" method="POST" >
        @csrf
            <input type="hidden" id="selected_point" name="point">
            <input type="hidden" name="get_point_id" value="{{ $get_point->id }}">
            <button type="submit" class="btn btn-primary">変更</button>
        </form>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>

<!-- 画像のURLを取得する関数 -->
<script>
async function getImageUrl(key) {
    const response = await fetch(`/image-url?key=${key}`);
    const data = await response.json();
    return data.url;
}

// セッションで渡される設定ポイント番号を変数に取得
const pointno = @json(session('pointno'));

function updateImage() {
    const select = document.getElementById('point');
    const selectedOption = select.options[select.selectedIndex];
    const key = selectedOption.getAttribute('data-image-key');
    getImageUrl(key).then(url => {
        const imageElement = document.getElementById('point-image');
        imageElement.src = url;
    });
    // 選択されたポイントの値を隠しフィールドに設定
document.getElementById('selected_point').value = select.value;
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

    </body>
    </html>
