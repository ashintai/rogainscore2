<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>画像の情報表示</title>
</head>

<body>
<h1>画像情報</h1>
    <p>横幅: {{ $width }} px</p>
    <p>高さ: {{ $height }} px</p>
    <p>容量: {{ $filesize }} バイト</p>

    <h2>アップロードされた画像</h2>
    <img src="{{ $imageUrl }}" alt="アップロードされた画像" style="max-width: 100%; height: auto;">
</body>

</html>