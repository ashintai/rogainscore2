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

    <hr>
    </header> 
    <p>{{ $change_point_no}}</p>
    <p>{{ $set_point_name}}</p>
    <p>{{ $change_photo_filename}}</p>
    <p>{{ $set_photo_filename}}</p>
    <p>{{ $before_photo_filename}}</p>
    
</body>
</html>