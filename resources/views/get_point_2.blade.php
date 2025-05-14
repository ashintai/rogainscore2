<!-- 本番用改良　取得写真の登録画面
スマホ上で画像圧縮して設定番号と同時に送信する -->

<!-- View:get_point> -->
<!-- このViewはController:get_pointから以下のパラメータを受け取る
set_points:設定ポイント一覧データ
user: ログインしているユーザー -->

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

<!-- 前画面からのメッセージ表示 -->
@if( $flag == 1 )
    <script>
        // JavaScriptでアラートを表示
        alert('写真が登録されました');
    </script>
@endif

@if( $flag == 2 )
    <script>
        // JavaScriptでアラートを表示
        alert('ポイント番号が変更されました');
    </script>
@endif

@if( $flag == 3 )
    <script>
        // JavaScriptでアラートを表示
        alert('現在スタッフが編集中です。すこしたってから再度操作してください');
    </script>
@endif


<header>
    <h5 style="color: blue; margin-left: 20px;">モリコロロゲイニング　ポイント通過登録</h5>
    <h6 style="margin-left: 20px;">チーム番号:{{ $user->team_no }} {{ $user->name }}</h6>
    <!-- 各ボタンを横並びに表示 -->
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <!-- すべての取得写真の一覧の表示・編集画面 -->
        <a href="{{ route('all_images') }}" class="btn btn-primary" style="margin-left: 10px;">写真一覧&編集</a>
        <!-- 成績速報画面へのボタン -->
        <a href="{{ route('result') }}" class="btn btn-primary">成績速報</a>
        <br>
        <!-- ログアウトボタン -->
        <form action="{{ route('logout') }}" method="POST" style="margin-right: 10px;">
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

<!-- 取得写真の読み込み -->
<div class="container mt-1">
    <h6 style="color: blue;">撮影した写真の登録</h6>
    <form action="{{ route('confirm_get_point') }}" method="post" id="imageForm"  enctype="multipart/form-data">
        @csrf
        <!-- 設定ポイント番号を送る -->
        <input type="hidden" id="selected_point" name="set_point_no" >
        <input type="hidden" id="canvasImage" name="image"> <!-- 隠しフィールド -->
        <img src="" id="preview" />
        <!-- 画像ファイルの入力→onChangeでcanvasDraw()を実行 -->
        <div class=mb-3>
            <input type="file" id="imageSelect" onChange="canvasDraw();" />
        </div>
        <!-- ボタンクリックでimageUpload()を実行 -->
        <!-- canvas要素の生成 -->
        <div class="d-flex justify-content-center mt-3">
            <canvas id="canvas"></canvas>
        </div>
        <div class="d-flex justify-content-center mt-3">
            <input type="button" onClick="prepareAndSubmitForm();" value="登録" />
        </div>
        </form>
</div>


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

    // ページロード時にSetポイント番号をはじめにする
        document.addEventListener('DOMContentLoaded', function() {
        const select = document.getElementById('point');
        select.selectedIndex = 0;
        updateImage();
    });
</script>

<!-- 画像圧縮関係のJavaScript -->
<script>
    // 画像が選択された時に呼び出される関数
function canvasDraw() {

    // id=imageSelect の特性propatyのfileの1番目[0]を変数fileへ取得
    var file = $("#imageSelect").prop("files")[0];
                
    //画像ファイルかチェック
    // file["type"]はファイルの種類を表すプロパティ
    if (file["type"] != "image/jpeg" && file["type"] != "image/png" && file["type"] != "image/gif") {
        alert("画像ファイルを選択してください");
        $("#imageSelect").val(''); //選択したファイルをクリア
        return ;
    }


    // FileReaderで　ローカルファイルをjavascriptで処理できるようになる
    var fr = new FileReader();
    // onloadはファイルが選択されてから処理する関数を定義
    fr.onload = function() {
        //選択した画像を一旦imgタグに表示
        $("#preview").attr('src', fr.result);
                    
        //imgタグに表示した画像をimageオブジェクトとして取得
        var image = new Image();
        image.onload = function() {
                                
            //縦横比を維持した縮小サイズを取得
            var w = 600;
            var ratio = w / image.width;
            var h = image.height * ratio;
                        
            //canvasに描画
            var canvas = $("#canvas");
            var ctx = canvas[0].getContext('2d');
            $("#canvas").attr("width", w);
            $("#canvas").attr("height", h);
            ctx.drawImage(image, 0, 0, w, h);
        };
        image.src = fr.result;
    };
    fr.readAsDataURL(file);
}

// 登録ボタンがクリックされたときに呼ばれる関数
// この関数はつかわれていない
function imageUpload() {
    // <form>の要素を変数formへ代入し操作する
    var form = $("#imageForm").get(0);
    // <form>の入力内容を変数formDataへ読み込む
    var formData = new FormData(form);

    //画像処理してformDataに追加
    if ($("#canvas").length) {
        //canvasに描画したデータを取得
        var canvasImage = $("#canvas").get(0);
                    
        //オリジナル容量(画質落としてない場合の容量)を取得
        canvas.toBlob(function(blob){
            // Blobデータをアップロード用に追加
            formData.append("selectImage", blob, "image.jpg"); //アップロード用blobデータを取得
            formData.append("_token", "{{ csrf_token() }}"); //CSRFトークンを追加
            //formDataをPOSTで送信
        $.ajax({
            async: false,
            type: "POST",
            url: "{{ route('canvas_upload_test') }}",
            data: formData,
            dataType: "text",
            cache: false,
            contentType: false,
            processData: false,
            error: function (XMLHttpRequest) {
                console.log(XMLHttpRequest);
                alert("アップロードに失敗しました");
            },
            success: function (res) {
                if(res !== "OK") {
                    console.log(res);
                    alert("アップロードに失敗しました");
                } else {
                    alert("アップロードに成功しました");
                }
            }
        });
    },"image/jpeg", 0.9); // 画質を90%に圧縮
    } 
}

// 登録がクリックされた時呼ばれる関数その２
// こちらの関数が使われる
function prepareAndSubmitForm() {
    // <canvas>要素を取得
    var canvas = document.getElementById("canvas");

    // canvas.toBlobを使用してBlobデータを生成
    canvas.toBlob(function(blob) {
        // BlobデータをBase64形式に変換
        var reader = new FileReader();
        reader.onloadend = function() {
            // 隠しフィールドにBase64データを設定
            document.getElementById("canvasImage").value = reader.result;

            // フォームを送信
            document.getElementById("imageForm").submit();
        };
        reader.readAsDataURL(blob); // BlobをBase64形式に変換
    }, "image/jpeg", 0.9); // JPEG形式で画質90%に圧縮
}

</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
</body>
</html>