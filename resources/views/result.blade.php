<!-- 成績速報画面 -->
<!-- コントローラからcategoriseをもらう -->
<!-- 表示するカテゴリーをリストで選択すると、非同期通信でそのカテゴリーの成績を表示する -->
<!-- 参加者とスタッフの両方から呼ばれるので戻るときは振り分けが必要 -->
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


<body>
    <header>
        <h5 style="color: blue; margin-left: 20px;">モリコロロゲイニング　成績速報</h5>
        <h6 style="margin-left: 20px;">チーム番号:{{ $user->team_no }} {{ $user->name }}</h6>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-left:5px">
            <!-- 戻るボタン -->
            <!-- 参加者とスタッフで戻り先は違うが、そこはH.indexが判断してくれる -->
            <a href="{{ route('checkpoint') }}" class="btn btn-primary" style="margin-left: 20px;">戻る</a>
            <!-- ログアウト -->
            <form action="{{ route('logout') }}" method="POST" style="margin-right: 10px;">
                @csrf
                <button type="submit" >ログアウト</button>
            </form>
        </div>
        <hr>
    </header>

    <!-- カテゴリ選択リスト -->
    <div class="container mt-1">

        <select id="category" onchange="updateResult()">
            <!-- オプションを動的に生成 -->
            <option value="-1">カテゴリを選択してください</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}">{{ $category->category_name }}コース：{{ $category->class_name }}クラス</option>
            @endforeach
        </select>
    </div>


    <!-- 結果表示 -->
    <div id="result" style="margin-left: 10px;">
        <!-- 結果を表示するためのコンテナ -->
    </div>

    <hr>
    <footer style="text-align: right;">
        <h8>© 2025 (特非)愛知県オリエンテーリング協会</h8>
    </footer>
</body>

<script>
    function updateResult() {
        // 選択されたカテゴリのIDを取得
        const select = document.getElementById('category');
        const selectedCategoryId = select.value;

    // alertを使用してselectedCategoryIdの値を表示
    // alert(`選択されたカテゴリID: ${selectedCategoryId}`);


        // サーバーにリクエストを送信して結果を取得
        fetch(`/get-results?category_id=${selectedCategoryId}`)
            .then(response => response.json())
            .then(data => {
                // 結果を表示するためのコンテナを取得
                const resultContainer = document.getElementById('result');

                alert( 'ここまできたよ');

                // 結果を表示
                resultContainer.innerHTML = '';
                data.results.forEach(result => {
                    const resultElement = document.createElement('div');
                    resultElement.innerHTML = `<hr>${result.team_no}:${result.team_name}(${result.member_num}名) 総得点:${result.total_score}点(減点${result.penalty}点) <br>   (確認待ち-${result.checking_num} NG-${result.ng_num} ポイント番号不明-${result.unknown_num}) <br> <span style="color: green;">${result.result_str}</span>`;
                    resultContainer.appendChild(resultElement);
                });
            })
            .catch(error => {
                console.error('Error fetching results:', error);
            });
    }
</script>
</html>
