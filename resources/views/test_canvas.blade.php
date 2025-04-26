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
</head>

<!-- javascriptで画像に圧縮をかける -->


<body>
    <form id="upload-form" action="{{ route('canvas_upload_test') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <img src="" id="preview" />
            <canvas id="canvas" ></canvas>
            <div class="mb-3">
                <label for="image" class="form-label">画像を選択または撮影し「アップロード」ボタンをタップしてください。</label>
                <input type="file" class="form-control" id="imageSelect"  onChange="canvasDraw();" name="image" accept="image/*" required >
            </div>
            <input type="button" onClick="" value="アップロード" />
        </form>
</body>





</html>

<script>
    function canvasDraw() {

    // id=imageSelect の特性propatyのfileの1番目[0]を変数fileへ取得
    var file = $("#imageSelect").prop("files")[0];
 
    var fr = new FileReader();
    fr.onload = function() {
            //選択した画像を一旦imgタグに表示
            $("#preview").attr('src', fr.result);
                        
            //imgタグに表示した画像をimageオブジェクトとして取得
            var image = new Image();
            image.src = $("#preview").attr('src');
                        
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

        fr.readAsDataURL(file);

</script>