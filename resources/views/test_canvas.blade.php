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
    <title>canvasで画像圧縮してサーバーへ送信する、をテスト</title>
    <style>
    #preview {
        display: none;}
    </style>
</head>

<!-- javascriptで画像に圧縮をかける -->


<body>

<!-- 原文のまま -->
<form action="" method="post" id="imageForm">
    @csrf
    <img src="" id="preview" />
    <!-- canvas要素の生成 -->
    <canvas id="canvas"></canvas>
    <!-- 画像ファイルの入力→onChangeでcanvasDraw()を実行 -->
    <input type="file" id="imageSelect" onChange="canvasDraw();" />
    <!-- ボタンクリックでimageUpload()を実行 -->
    <input type="button" onClick="imageUpload();" value="アップロード" />
</form>

</body>
</html>

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

// アップロードボタンがクリックされたときに呼ばれる関数
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

</script>