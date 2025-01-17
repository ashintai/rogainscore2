<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous">
    </script>
        <style>
        .form-group {
            display: flex;
            align-items: center;
        }
        .form-group label {
            margin-right: 10px;
        }
    </style>
    <title>モリコロロゲイン</title>
</head>
<body>
<header>
    <h5 style="color: blue;">モリコロロゲイニング　スタッフ操作</h5>
    <!-- ログアウトボタン -->
    <form action="{{ route('logout') }}" method="POST" >
        @csrf
            <button type="submit" style="float: right;">ログアウト</button>
    </form>
    <br>
    <hr>
</header>
<p>チーム番号：{{ $user->team_no }}</p>

<form action="{{ route('team_update', $user->id) }}" method="POST">
    @csrf
    <div style="display:flex" class="mb-2">
        <label for="name" class="form-label" style="width: 110px">チーム名</label>
        <input type="text" class="form-control" id="name" name="name" value="{{ $user->name }}">
    </div>
    <div style="display:flex" class="mb-2">
        <label for="member_num" class="form-label" style="width: 110px">メンバー数</label>
        <input type="number" class="form-control" id="member_num" name="member_num" value="{{ $user->member_num }}" min="1" max="5">
    </div>
    <div style="display:flex" class="mb-2">
        <label for="category" class="form-label" style="width: 110px">カテゴリ</label>
        <select class="form-select" id="category" name="category_id">
            @foreach($categories as $category)
                <option value="{{ $category->id }}" @if($user->category_id == $category->id) selected @endif>{{ $category->category_name }}/{{ $category->class_name }}</option>
            @endforeach
        </select>
    </div>
    <div style="display:flex" class="mb-2">
        <label for="email" class="form-label" style="width: 110px">メールアドレス</label>
        <input type="email" class="form-control" id="email" name="email" value="{{ $user->email }}">
    </div>
    <button type="submit" class="btn btn-primary ms-4">更新</button>
    </form>

<hr>
<!-- 戻る -->
<div class="ms-3">
    <a href="{{ route('team_index') }}" class="btn btn-primary">更新せず戻る</a>
</div>
<footer style="text-align: right;">
    <h8>© 2025 (特非)愛知県オリエンテーリング協会</h8>
</footer>



