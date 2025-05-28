<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous">
    </script>
    <title>Document</title>
</head>
<body>
<!-- エラーメッセージを表示 -->
    @if ($errors->has('ini'))
        <script>
            alert('{{ $errors->first('ini') }}');
        </script>
    @endif

    <header>
        <h5>モリコロロゲイニング　ポイント設定画面（管理者）</h5>
    <form action="{{ route('logout') }}" method="POST" >
        @csrf
        <button type="submit" style="float: right;">ログアウト</button>
    </form>
    <br>
    <hr>
    </header>
    ログイン待機状態のON/OFF<br>
    <div style="display: flex; align-items: center;">
    @if ($flag == 1)
        <p class="btn btn-danger rounded-circle ms-3">ログイン待機</p>
    @else
    <p class="btn btn-success rounded-circle ms-3">ログインOK</p>
    @endif

    <form action="{{ url('/login_wait') }}" method="POST" class="ms-3">
    @csrf
        <button class="btn btn-primary ">ログインON/OFF</button>
    </form>
    </div>
    <br>
    
    ポイント設定ファイルのアップロード<br>
    <form action="{{ url('/pointdata_set') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <!-- CSVファイルの選択 -->
        <input type="file" name="csvFile" class="ms-3" id="csvFile">
        <button class="btn btn-primary ">CSVファイル読込</button>
    </form>
    <br>
    カテゴリー設定ファイルのアップロード<br>
    <form action="{{ url('/category_set') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <!-- CSVファイルの選択 -->
        <input type="file" name="csvFile" class="ms-3" id="csvFile">
        <button class="btn btn-primary ">CSVファイル読込</button>
    </form>
    <br>
    チーム設定ファイルのアップロード<br>
    <form action="{{ url('/team_set') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <!-- CSVファイルの選択 -->
        <input type="file" name="csvFile" class="ms-3" id="csvFile">
        <button class="btn btn-primary ">CSVファイル読込</button>
    </form>
    <br>
    取得写真のダミーデータのアップロード<br>
    <form action="{{ url('/dummy_get') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <!-- CSVファイルの選択 -->
        <input type="file" name="csvFile" class="ms-3" id="csvFile">
        <button class="btn btn-primary ">CSVファイル読込</button>
    </form>
    <br>
    Userテーブルの消去（admin@gmail.com 1234は残す）<br>
    <form action="{{ url('/clear_user') }}" method="POST" class="ms-3">
    @csrf
    <!-- CSVファイルの選択 -->
        <button class="btn btn-danger ">Userテーブルの消去</button>
    </form>
    <br>

    <form action="{{ url('/get_photo_download') }}" method="POST" class="ms-3">
    @csrf
    <select name="team_no" class="form-select ms-3" id="team_no" style="width: 200px;">
        <option value="">チームを選択</option>
        <option value="0">全チーム一括ダウンロード</option>
        @foreach ($users as $user)
            <option value="{{ $user->team_no }}">{{ $user->team_no }}:{{ $user->name }}</option>
        @endforeach
    </select>
    <button class="btn btn-primary">取得写真のダウンロード</button> 
    </form>
    <br>

    取得写真データの消去（getテーブルの全データおよびAWS-S3のget写真を消去）<br>
    <form action="{{ url('/clear_get') }}" method="POST" class="ms-3">
    @csrf
        <button class="btn btn-danger ">取得写真の消去</button>
    </form>
    <br>

    



</body>
</html>