<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous">
    </script>
    
    <title>モリコロロゲイン</title>
    
</head>
<header>
    <h5 style="color: blue; margin-left: 20px;">モリコロロゲイニング</h5>
    <hr>
</header>

<body>
<div class="container text-center mt-5">
    <p>ログイン状態が切れました。</p>
    <p>お手数ですが、ログインしなおしてください。</p>
    <br>
    <br>
    <a href="{{ route('login') }}" class="btn btn-primary" onclick="clearSession()">ログイン</a>
</div>

<script>
        function clearSession() {
            // セッションストレージをクリア
            sessionStorage.clear();
            // ローカルストレージをクリア
            localStorage.clear();
        }
    </script>

</body>
</html>
