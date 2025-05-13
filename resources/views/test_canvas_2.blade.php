<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>画像の情報表示</title>
</head>

<body>
<h1>画像情報</h1>
<p>結果：{{ $result }}</p>
    <p>横幅 {{ $width }}:px</p>
    <p>高さ:{{ $height }}:  px</p>
    <p>容量: {{ $filesize }}: バイト</p>

    <img src="{{ $base64Image }}" alt="pload">
    <p>設定ポイント番号: {{ $set_point_no }}</p>
<!-- 画像UP画面へ戻る  -->
<a href="{{ route('canvas_test') }}" class="btn btn-primary">画像選択画面へ戻る</a>
</body>


</html>