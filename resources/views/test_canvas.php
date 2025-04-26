<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>canvasで画像圧縮してサーバーへ送信する、をテスト</title>
</head>
<body>
    <form id="upload-form" action="{{ route('canvas_upload_test') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="image" class="form-label">画像を選択または撮影し「アップロード」ボタンをタップしてください。</label>
                <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
            </div>
            <button type="submit" class="btn btn-primary">アップロード</button>
        </form>
</body>
</html>