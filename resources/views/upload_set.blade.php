<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
<title>Document</title>
</head>
<body>
<header>
        <h5>モリコロロゲイニング　ポイント設定画面（管理者）</h5>
    <form action="{{ route('logout') }}" method="POST" >
        @csrf
        <button type="submit" style="float: right;">ログアウト</button>
    </form>
    <br>
    <hr>
    </header>  
<form action="{{ route('logout') }}" method="POST" >
        @csrf
        <button type="submit">ログアウト</button>
    </form>
    設定写真のアップロード画面<br>
    <br>
    
    <form action="{{ url('/import_set') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="file" name="files[]" multiple >
        <button type="submit" class="btn btn-primary ">設定写真のUPLOAD開始</button>
    </form>
    

</body>
</html>