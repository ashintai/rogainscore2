<!-- 本番用改良　写真一覧からの写真取得画面ー手入力対応 -->
<!-- スマホ上で画像圧縮して設定番号と同時に送信する --> 

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
    <h5 style="color: blue; margin-left: 20px;">モリコロロゲイニング　手入力写真登録</h5>
    <h6 style="margin-left: 20px;">チーム番号:{{ $user->team_no }} {{ $user->name }}</h6>
    <!-- 各ボタンを横並びに表示 -->
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <!-- 戻る -->
        <a href="{{ route('all_images' , ['flag' => 0 ] ) }}" class="btn btn-primary" style="margin-left: 10px;">戻る</a>
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
    <h6 style="color: blue;">手入力されたポイント</h6>
    <h6>{{ $set_point_no}} : {{ $set_point_name }}</h6>
    <!-- 設定ポイントの写真表示 -->
    <div class="d-flex justify-content-center mt-3">
        <img src="{{ $url }}"  alt="ポイント画像" style="max-width: 80%; ">
    </div">
<hr>

<!-- 取得写真の読み込み -->
<div class="container mt-1">
    <!-- 画像の一時置き場 -->
    <img src="" id="preview" />
    <h6 style="color: blue;">撮影した写真の登録（注）すでに一度登録した場合でもここには表示されません。</h6>
    <form action="{{ route('all_images_change_photo') }}" method="post" id="imageForm"  enctype="multipart/form-data">
        @csrf
        <!-- 設定ポイント番号を送る -->
        <input type="hidden" id="selected_point" name="set_point_no" value="{{ $set_point_no }}">
        <input type="hidden" id="get_point_id" name="get_point_id" value="{{ $get_point_id }}">
        <input type="hidden" id="canvasImage" name="image"> <!-- 隠しフィールド -->
        
        <!-- 画像ファイルの入力→onChangeでcanvasDraw()を実行 -->
        <div class=mb-3>
            <input type="file" id="imageSelect" onChange="canvasDraw();" />
        </div>
        <!-- ボタンクリックでimageUpload()を実行 -->
        <!-- canvas要素の生成 -->
        <div class="d-flex justify-content-center mt-3">
            <canvas id="canvas" style="max-width: 80%; margin-left: 10px;" ></canvas>
        </div>
        <div class="d-flex justify-content-center mt-3">
            <input type="button" id="toroku" onClick="prepareAndSubmitForm();" value="登録" class="btn btn-primary" style="display: none;"/>
        </div>
        </form>
</div>

<hr>
<footer style="text-align: right;">
    <h8>© 2025 (特非)愛知県オリエンテーリング協会</h8>
</footer>


<!-- 画像圧縮関係のJavaScript -->
<script>
    // 画像が選択された時に呼び出される関数
function canvasDraw() {

    // id=imageSelect の特性propatyのfileの1番目[0]を変数fileへ取得
    var file = $("#imageSelect").prop("files")[0];
                
    //画像ファイルかチェック
    // file["type"]はファイルの種類を表すプロパティ
    if (
        file["type"] != "image/jpeg" && 
        file["type"] != "image/png" && 
        file["type"] != "image/gif" &&
        file["type"] != "image/heif" &&
        file["type"] != "image/heic"
    ){
        alert("画像ファイルを選択してください。(jpeg, png, gif, heif, heic)");
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

            // 登録ボタンを表示
            document.getElementById("toroku").style.display = "inline-block";
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
    var actionUrl = form.action;
    // <form>のaction属性を取得
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
            url: actionUrl ,
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