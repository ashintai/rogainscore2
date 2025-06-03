<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\Set_point;
use App\Models\Category;
use App\Models\User;
use App\Models\Get_point;
use Illuminate\Support\Str;
// use Intervention\Image\ImageManagerStatic as Image;
// use Intervention\Image\Facades\Image;
use Intervention\Image\ImageManager;


// use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Drivers\Imagick\Driver;


use Illuminate\Http\File;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * ログインに成功後最初に入るページ
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        
                
        $user = Auth::user();

        if(!Auth::check() ){
            return redirect()->route('logout');
        }

        // 役割が１：管理者の場合,ポイント設定画面へ直リンク
        // 引数$flag = 0でログイン可　1でログイン不可
        if(Auth::check() && $user->role == 1){
            if( $user->team_no == NULL || $user->team_no == 0){
                $flag = 0;
            }else{
                $flag = 1;
            }

            // 取得写真DL用にチームデータも渡す
            $users = User::where('role', 0)->get();
            return view( 'set_point' , compact('flag' , 'users') );
        }
        
        // 役割が２：スタッフの場合、チェック画面へ直リンク
        if(Auth::check() && $user->role == 2){
            return redirect()->route('staff_main');
        }
    
        // ログイン待機状態ではwait_login画面へ
        $user = User::where('role', 1)->first();
        if($user->team_no == 1){
            return view('wait_login');
        }

        // 役割が０（それ以外）、参加者メイン画面のコントローラーへ
        // flag=0で渡して、ログイン直後であることを伝える
        // set_point_no は　最初レコードの番号を渡す
        // $set_point = Set_point::first();
        // session(['set_point_no' => $set_point->point_no]);
        // $get_point_id はログイン直後はなし
        // session(['get_point_id' => 0]);
        // ログイン直後はflag=0 前で写真登録なしで入る
        return redirect()->route('get_point',['flag' => 0]);
    }


    // 参加者ログイン待機状態のON/OFF
    public function login_wait()
    {
        // userテーブルの管理者（role=1）のteam_no がNULL　または　0のときログイン可
        // それ以外1はログイン不可
        $user = User::where('role', 1)->first();
        if($user->team_no == NULL || $user->team_no == 0){
            $user->team_no = 1;
            $flag = 1;
        }else{
            $user->team_no = 0;
            $flag = 0;
        }
        $user->save();
        return redirect()->route('index');
    }



// ユーザーテーブルの消去
public function clear_user(){
    User::truncate();
    $user = new User();
    $user->name = '管理者';
    $user->email = 'admin@gmail.com';
    $user->password = bcrypt('1234');
    $user->role = 1;
    $user->team_no = 0;
    $user->category_id = 0;
    $user->member_num = 0;
    $user->save();
    return redirect()->route('index');
}

// 取得写真の消去およびAWS-S3のget写真の消去
public function clear_get(){
    \App\Models\Get_point::truncate();


    // ここ途中でエラーがでてうまくいかないので保留
    // 'get'で始まるファイルをリストアップ
    // 一旦、バケットにある全ファイルのリストを取得
    // $files = Storage::disk('s3')->files('');
    // getで始まるファイルのみを削除
    // foreach ($files as $file) {
    //     if (Str::startsWith(basename($file), 'get')) {
    //         Storage::disk('s3')->delete($file);
    //     }
    // }

    // 管理者画面へ戻る
    return redirect()->route('index');
}



// 取得写真のダウンロード
public function get_photo_download(Request $request)
{
    $team_no = $request->input('team_no');

    if (!$team_no) {
        return back()->withErrors(['ini' => 'チーム番号が指定されていません。']);
    }
    $get_points = Get_point::where('team_no', $team_no)->get();

    if ($get_points->isEmpty()) {
        return back()->withErrors(['ini' => '指定されたチームの取得写真がありません。']);
    }
    
    $zipFileName = 'team_no_' . $team_no . '.zip';
    $zipFilePath = public_path($zipFileName);
    $zip = new \ZipArchive();
    if ($zip->open($zipFilePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
        return back()->withErrors(['ini' => 'ZIPファイルの作成に失敗しました。']);
    }

    $disk = Storage::disk('s3');
    foreach ($get_points as $get_point) {
        $s3url = $get_point->photo_filename;
        if (filter_var($s3url, FILTER_VALIDATE_URL)) {
            $parsedUrl = parse_url($s3url);
            $s3path = ltrim($parsedUrl['path'], '/');
        } else {
            $s3path = $s3url;
        }

        try {
            if ($disk->exists($s3path)) {
                $fileContent = $disk->get($s3path);
                $zip->addFromString(basename($s3path), $fileContent);
            }
        } catch (\Exception $e) {
        // エラー時は何もしない、continue; で次のファイルへ
        continue;
        }
    }
    $zip->close();
    
    return response()->download($zipFilePath, $zipFileName)->deleteFileAfterSend(true);

}

/**
 * ポイント設定ファイル（CSV）を読み込んでDBのテーブルにセットする
 */
    public function pointdata_set(Request $request)
    {
   // CSVファイルの読み込み

    // 拡張子が.csvでないファイルを添付しようとした場合
    $app_file = $request->file('csvFile');
    // ファイルがアップロードされているか確認
    if (!$app_file) {
        return back()->withErrors(['ini' => 'ファイルがアップロードされていません。']);
    }

    // 拡張子がcsvであることを確認
    if ($app_file->getClientOriginalExtension() !== 'csv') {
        return back()->withErrors(['ini' => 'ファイルはCSV形式である必要があります。']);
    }

    // MIMEタイプがtext/csvであることを確認
    if ($app_file->getMimeType() !== 'text/csv') {
        return back()->withErrors(['ini' => 'ファイルのタイプが不正です。']);
    }

    // CSVファイルのカラム位置指定
    $csv_point_no = 0;
    $csv_point_name = 1;
    $csv_score = 2;
    $csv_sns_score = 3;
    $csv_lati = 4;
    $csv_log= 5;


    if ($request->hasFile('csvFile')){
        
        // set_point テーブルをクリア
        Set_point::truncate();
        // 指定されたCSVファイル名を取得
        $file=$request->file('csvFile');
        $path=$file->getRealPath();
        // ファイルを開く
        $fp=fopen($path, 'r');
        // ヘッダ行をスキップ
        fgetcsv($fp);

        // １行ずつCSVファイルを読込み
        while(( $csvData = fgetcsv($fp)) !== FALSE){
        // 空白行はスキップする
            if (
                empty($csvData[$csv_point_no]) ||
                empty($csvData[$csv_point_name]) ||
                empty($csvData[$csv_score])
            ) {
                continue;
            }
            // 新しいSet_pointインスタンス
            $set_point = new Set_point();
            // ポイント番号
            $set_point->point_no = $csvData[$csv_point_no];
            // ポイント名
            $set_point->point_name=$csvData[$csv_point_name];
            // 得点
            $set_point->score =$csvData[$csv_score];
            // SNS得点
            $set_point->sns_score=$csvData[$csv_sns_score];
            //GPS　緯度
            $set_point->gps_lati=$csvData[$csv_lati];
            //GPS 経度
            $set_point->gps_long=$csvData[$csv_log];

            
            // DBへ挿入
            $set_point->save();
        }
        // ファイルを閉じる        
        fclose($fp);
    }else{
        // ここにCSVファイルがなかったときの処理
        return back()->withErrors([
            'ini' => ['CSVファイルがありません'],
        ]);
    }
    return redirect()->route('index');
    
}

/**
 * カテゴリー設定ファイル（CSV）を読み込んでDBのテーブルにセットする
 */
public function category_set(Request $request)
{
// CSVファイルの読み込み

// 拡張子が.csvでないファイルを添付しようとした場合
$app_file = $request->file('csvFile');
// ファイルがアップロードされているか確認
if (!$app_file) {
    return back()->withErrors(['ini' => 'ファイルがアップロードされていません。']);
}

// 拡張子がcsvであることを確認
if ($app_file->getClientOriginalExtension() !== 'csv') {
    return back()->withErrors(['ini' => 'ファイルはCSV形式である必要があります。']);
}

// MIMEタイプがtext/csvであることを確認
if ($app_file->getMimeType() !== 'text/csv') {
    return back()->withErrors(['ini' => 'ファイルのタイプが不正です。']);
}

// CSVファイルのカラム位置指定
$csv_category_id = 0;
$csv_category_name = 1;
$csv_class_name = 2;

if ($request->hasFile('csvFile')){
    // カテゴリーテーブルをクリア
    Category::truncate();
    // 指定されたCSVファイル名を取得
    $file=$request->file('csvFile');
    $path=$file->getRealPath();
    // ファイルを開く
    $fp=fopen($path, 'r');
    // ヘッダ行をスキップ
    fgetcsv($fp);

    // １行ずつCSVファイルを読込み
    while(( $csvData = fgetcsv($fp)) !== FALSE){
    
        // 新しいSet_pointインスタンス
        $category = new Category();
        // カテゴリーid
        $category->category_id = $csvData[$csv_category_id];
        // ポイント名
        $category->category_name=$csvData[$csv_category_name];
        // 得点
        $category->class_name =$csvData[$csv_class_name];
                
        // DBへ挿入
        $category->save();
    }
    // ファイルを閉じる        
    fclose($fp);
}else{
    // ここにCSVファイルがなかったときの処理
    return back()->withErrors([
        'ini' => ['CSVファイルがありません'],
    ]);
}
return redirect()->route('index');
}

/**
 * チーム設定ファイル（CSV）を読み込んでDBのテーブルにセットする
 */
public function team_set(Request $request)
{
// CSVファイルの読み込み

// 拡張子が.csvでないファイルを添付しようとした場合
$app_file = $request->file('csvFile');
// ファイルがアップロードされているか確認
if (!$app_file) {
    return back()->withErrors(['ini' => 'ファイルがアップロードされていません。']);
}

// 拡張子がcsvであることを確認
if ($app_file->getClientOriginalExtension() !== 'csv') {
    return back()->withErrors(['ini' => 'ファイルはCSV形式である必要があります。']);
}

// MIMEタイプがtext/csvであることを確認
if ($app_file->getMimeType() !== 'text/csv') {
    return back()->withErrors(['ini' => 'ファイルのタイプが不正です。']);
}

// CSVファイルのカラム位置指定
$csv_team_name = 0;
$csv_mailadress = 1;
$csv_password = 2;
$csv_team_no = 3;
$csv_category_id = 4;
$csv_member_num = 5;
$csv_role = 6;

if ($request->hasFile('csvFile')){
    // 指定されたCSVファイル名を取得
    $file=$request->file('csvFile');
    $path=$file->getRealPath();
    // ファイルを開く
    $fp=fopen($path, 'r');
    // ヘッダ行をスキップ
    fgetcsv($fp);

    // １行ずつCSVファイルを読込み
    while(( $csvData = fgetcsv($fp)) !== FALSE){
    
        // 新しいSet_pointインスタンス
        $user = new User();
        // チーム名
        $user->name = $csvData[$csv_team_name];
        // メールアドレス
        $user->email=$csvData[$csv_mailadress];
        // パスワード
        $user->password=$csvData[$csv_password];
        // 役割
        $user->role=$csvData[$csv_role];
        // チーム番号
        $user->team_no=$csvData[$csv_team_no];
        // カテゴリーid
        $user->category_id=$csvData[$csv_category_id];
        // メンバー数
        $user->member_num=$csvData[$csv_member_num];


                
        // DBへ挿入
        $user->save();
    }
    // ファイルを閉じる        
    fclose($fp);
}else{
    // ここにCSVファイルがなかったときの処理
    return back()->withErrors([
        'ini' => ['CSVファイルがありません'],
    ]);
}
return redirect()->route('index');
}


/**
 * 取得ダミーデータのファイル（CSV）を読み込んでDBのテーブルにセットする
 */
public function dummy_get(Request $request)
{
// CSVファイルの読み込み

// 拡張子が.csvでないファイルを添付しようとした場合
$app_file = $request->file('csvFile');
// ファイルがアップロードされているか確認
if (!$app_file) {
    return back()->withErrors(['ini' => 'ファイルがアップロードされていません。']);
}

// 拡張子がcsvであることを確認
if ($app_file->getClientOriginalExtension() !== 'csv') {
    return back()->withErrors(['ini' => 'ファイルはCSV形式である必要があります。']);
}

// MIMEタイプがtext/csvであることを確認
if ($app_file->getMimeType() !== 'text/csv') {
    return back()->withErrors(['ini' => 'ファイルのタイプが不正です。']);
}

// CSVファイルのカラム位置指定
$csv_point_no = 0;
$csv_team_no = 1;

if ($request->hasFile('csvFile')){
    // 指定されたCSVファイル名を取得
    $file=$request->file('csvFile');
    $path=$file->getRealPath();
    // ファイルを開く
    $fp=fopen($path, 'r');
    // ヘッダ行をスキップ
    fgetcsv($fp);

    // １行ずつCSVファイルを読込み
    while(( $csvData = fgetcsv($fp)) !== FALSE){
    
        // 新しいGet_pointインスタンス
        $get_point = new Get_point();
        // 設定ポイント番号
        $get_point->point_no  = $csvData[$csv_point_no];
        // チーム番号
        $get_point->team_no = $csvData[$csv_team_no];
        // 写真ファイルへのURL
        $get_point->photo_filename = "https://rogain.s3.amazonaws.com/get_" . $get_point->point_no . "_" . $get_point->team_no . ".JPG";
        // checked フラグのセット
        $get_point->checked = 0;
                
        // DBへ挿入
        $get_point->save();
    }
    // ファイルを閉じる        
    fclose($fp);
}else{
    // ここにCSVファイルがなかったときの処理
    return back()->withErrors([
        'ini' => ['CSVファイルがありません'],
    ]);
}
return redirect()->route('index');
}



/**
 * 写真判定画面
 * 
 */
// 未チェックの写真を取得
public function next_get_point()
{
    // 最初の未チェック写真を探す
//  Get_pointテーブルのchekckedが０（確認中）でかつリレーションするuserテーブルのroleが3（ロック中）以外のものを取得
    $next_point = Get_point::where('checked', 0)
    ->where('point_no', '!=', 0) // point_noが0のものは除外
    ->whereHas('user', function($query) {
        $query->where('role', '!=', 3);
    })
    ->first();

    // $next_point = Get_point::where('checked', 0)->first();

    // チームがロック中role=3 の場合は、チェックできない
    
    // チェック中フラグを立てる
    if ($next_point) {
        // checkedカラムの内容を書き換える
        $next_point->checked = 1; // 確認中
        $next_point->save(); // データベースに保存
    
        // 設定写真のURLを生成
        $key = "set_" . $next_point->point_no . ".JPG";
        $set_photo_url = Storage::disk('s3')->url($key);
        // 設定ポイントの名前
        $set_point = Set_point::where('point_no', $next_point->point_no)->first();
        if($set_point){
            $set_point_name = $set_point->point_name;
        } else {
            $set_point_name = '不明'; // 設定ポイントが見つからない場合のデフォルト値
        }
                
        // $set_photo_url = "https://rogain.s3.amazonaws.com/set_" . $next_point->point_no . ".JPG"; 
        // 取得写真のURL
        $get_photo_url = $next_point->photo_filename ;
        // チーム番号とチーム名,メンバー数を準備する
        $team_no = $next_point->team_no;
        $team_name = $next_point->user ? $next_point->user->name : '不明';//リレーションでUsrからチーム名を取得
        $member_num = $next_point->user ? $next_point->user->member_num : '0'; // リレーションでUsrからメンバー数を取得 

        // Bladeへ渡す
        return view('check_point', compact('next_point','team_no' , 'team_name' , 'member_num' ,'set_point_name' ,'set_photo_url' , 'get_photo_url'));

    } else {
        // 未チェックの写真がない場合はスタッフメイン画面へ戻る
        return redirect()->route('staff_main');
    }
}

// チェック結果を登録
public function change_checked(Request $request)
{
    // 対象のgetテーブルのid は$get_id で送られてくる。
    $get_id = request('get_id');
    // チェック結果は$result で送られてくる。oh,ng,skipのいずれか
    $result = request('result');

    // \Log::debug($get_id);
    // \Log::debug($result);

    // getテーブルのid からレコードを取得
    $get_point = Get_point::find($get_id);

    if ($get_point) {
        // チェック結果を更新
        if ($result === 'ok'){
            $get_point->checked = 2; // OK
        } elseif ($result === 'ng') {
            $get_point->checked = 3; // NG
        } else{
            $get_point->checked = 0;
        }
        // データベースに保存
        $get_point->save();
        $message = 'チェック結果を登録しました';
    }else{
        $message = 'エラーが発生しました';
    }
    if ($result === 'cancel') {
        return redirect()->route('staff_main')->with('message', $message);
    } else {
        return redirect()->route('next_get_point')->with('message', $message);
    }
    
}

// 確認中をリセット
public function reset_checking(){
    // 確認中の写真を取得
    $checking_points = Get_point::where('checked', 1)->get();
    // チェック中フラグをリセット
    foreach ($checking_points as $point) {
        $point->checked = 0; // 未チェック
        $point->save(); // データベースに保存
    }
    // スタッフメイン画面へ戻る
    return redirect()->route('staff_main');
}

// NGをリセット
public function reset_ng(){
    // NGの写真を取得
    $ng_points = Get_point::where('checked', 3)->get();
    // チェック中フラグをリセット
    foreach ($ng_points as $point) {
        $point->checked = 0; // 未チェック
        $point->save(); // データベースに保存
    }
    // スタッフメイン画面へ戻る
    return redirect()->route('staff_main');
}

// 減点処理
public function input_penalty(){
    // 全チーム情報から参加者のみを取得
    $users = User::with('category')->where('role', 0)->get();
    // チーム情報を渡す
    return view('input_penalty', compact('users'));
}

// 減点入力の格納
public function change_penalty(Request $request){
    \Log::debug($request);
    $penalties = $request->input('penalties');
    foreach($penalties as $team_no => $penalty){
        $user = User::where('team_no', $team_no)->first();
        $user->penalty = $penalty;
        $user->save();
    }

    return redirect()->route('staff_main');
}


// チーム情報一覧＆編集
public function team_index(){
    // 参加者のみの情報を取得 通常状態とロック状態の両方
    $users = User::with('category')->whereIn('role', [ 0 , 3 ])->get();
    // チームのポイント状態を生成
    $state = [];
    foreach ($users as $user) {
        // このチームの取得ポイント情報をgetテーブルから拾う
        // このチームでまだ未確認になっているポイント数を取得
        $uncheck_point = Get_point::where('team_no', $user->team_no)
            ->where('checked', 0)->where('point_no', '!=', 0)
            ->count();
        $state[$user->team_no] = "未確認数：$uncheck_point";
    }
    
    // チーム情報を渡す
    return view('team_index', compact('users' , 'state'));
}

// チーム情報編集画面へ
public function team_edit($id){
    // チーム情報を取得
    $user = User::find($id);
    // カテゴリー情報を取得
    $categories = Category::all();
    // チーム情報を渡す
    return view('team_edit', compact('user', 'categories'));
}

// チーム情報編集のDBへの登録
public function team_update(Request $request){
    // チーム情報を取得
    $user = User::find($request->id);
    // チーム情報を更新
    $user->name = $request->name;
    $user->email = $request->email;
    $user->category_id = $request->category_id;
    $user->member_num = $request->member_num;
    $user->role = 0;
    // データベースに保存
    $user->save();
    // チーム情報一覧へリダイレクト
    return redirect()->route('team_index');
}

// チームの通過ポイント編集＆減点入力
public function team_point($id){
    $user = User::find($id);
    // すでに他のスタッフが編集中の場合は、メッセージを返す
    if($user->role == 3){
        return redirect()->back()->with('message' , '他のスタッフが編集中です。');
    }
    // Getテーブル情報を渡す
    $get_points = Get_point::where('team_no', $user->team_no)->with('setPoint')->orderByRaw('CASE WHEN point_no = 0 THEN 1 ELSE 0 END, point_no ASC')->get();
         // set_pointsテーブルからpoint_noをキー、point_nameを値とする配列を作成
    $set_point_list = Set_point::pluck('point_name', 'point_no')->toArray();
    // 得点、減点の計算
    $penalty = $user->penalty;
    // checkedが2または5のレコードだけ抽出し、setPointが存在するもののscoreを合計
    $score = $get_points
    ->filter(function($point) {
        return in_array($point->checked, [2, 5]) && $point->setPoint;
    })
    ->sum(function($point) {
        return $point->setPoint->score;
    });
    // このチームをlockするため、userのroleを3に変更
    if($user){
        $user->role = 3;
        $user->save();
    }else{
        return redirect()->back()->with('message' , 'システムエラーですteam_point');
    }

    return view('team_point', compact('user', 'get_points' , 'set_point_list' , 'score' , 'penalty'));
}

// チームのロックを解除してチーム一覧へ戻る
public function team_point_unlock($user_id){
    // チームのロックを解除してチーム一覧へ戻る
    $user = User::find($user_id);
    if($user){
        $user->role = 0;
        $user->save();
    }else{
        return redirect()->back()->with('message' , 'システムエラーですteam_point_unlock');
    }
    return redirect()->route('team_index' );

}

// 減点の入力
public function team_penalty_input($user_id , Request $request){
//    $user_idに対象のユーザーのid
$user= User::find($user_id);
// 入力された減点
$penalty = $request->input('penalty');
if($user){
    $user->penalty =$penalty;
    $user->save(); // データベースに保存}
    // team_pointへ戻る前にLockを一旦解除
    $user->role = 0; // ロック解除
    $user->save(); // データベースに保存
    return redirect()->route('team_point' , [ 'id' => $user_id ]);
    }
    // エラーで返す
    return redirect()->back()->with('message' , 'システムエラーですteam_penalty_input');
}



// 手入力の削除
public function team_point_delete($get_id , $user_id){
    // 手入力の削除
    // $get_id で指定されたGetテーブルのレコードを無効化
    // 実際にはteam_no を０に変更する
    $get_point = Get_point::find($get_id);
    if ($get_point) {
        $get_point->team_no = 0; // 無効化
        $get_point->save(); // データベースに保存
    }
    // ポイント一覧に戻るときに、user_idを渡す
    $user = User::find($user_id);
    if($user){
        $user->role = 0; // ロック解除
        $user->save(); // データベースに保存
        return redirect()->route( 'team_point' , [ 'id' => $user_id ]);
    }
    // エラーで返す
    return redirect()->back()->with('message' , 'システムエラーですteam_point_delete');
}

// ポイント一覧から写真を選んで状態を変化させる
public function team_point_photo($get_id , $user_id){
    // $user_id で指定されたユーザの情報を取得
    $user = User::find($user_id);
    if($user){
        $team_no = $user->team_no;
        $team_name = $user->name;
        $user_id = $user->id;
    }
    else{
        return redirect()->back()->with('message' , 'システムエラーですteam_point_photo');
    }
    // $get_id で指定されたGetテーブルのレコードを取得
    $get_point = Get_point::with('setPoint')->find($get_id);
    if($get_point){
        $point_no = $get_point->point_no;
        $point_name = $get_point->setPoint ? $get_point->setPoint->point_name : '不明';
        $get_photo_url = $get_point->photo_filename;
        $checked = $get_point->checked;
        $set_photo_url = "https://rogain.s3.amazonaws.com/set_" . $point_no . ".JPG";
    }
    else{
        return redirect()->back()->with('message' , 'システムエラーですteam_point_photo');
    }

    return view('team_point_change', compact('user_id' , 'get_id' , 'checked' , 'team_no' , 'team_name' , 'point_no' , 'point_name' , 'get_photo_url' , 'set_photo_url'));
    }

    // ポイント状態の変更
public function team_point_change(Request $request){
// user-id get_id flag がパラメータ
// flag = 0 戻る　1 未確認　2 OK 3 NG
$user_id = $request->input('user_id');
$get_id = $request->input('get_id');
$flag = $request->input('flag');

$get_point = Get_point::find($get_id);
if ($get_point) {
    // チェック結果を更新
    if ($flag == 1) {
        $get_point->checked = 0; // 未確認
    } elseif ($flag == 2) {
        $get_point->checked = 2; // OK
    } elseif ($flag == 3) {
        $get_point->checked = 3; // NG
    }
    // データベースに保存
    $get_point->save();
} else {
    return redirect()->back()->with('message', 'エラーが発生しましたteam_point_change');
}
// team_pointへ戻る前にLockを一旦解除
$user = User::find($user_id);
if ($user) {
    $user->role = 0; // ロック解除
    $user->save(); // データベースに保存
} else {
    return redirect()->back()->with('message', 'システムエラーですteam_point_change');
}
return redirect()->route('team_point' , [ 'id' => $user_id ]);

}


// ポイント手入力
public function team_point_input($id , Request $request){
    // ルートパラメーidはUser_id  $request->input('point_no')はポイント番号
    $user = User::find($id);
    if(!$user){
        return redirect()->back()->with('message', 'システムエラーですteam_point_input');
    }
    // チーム番号を取得
    $team_no = $user->team_no;
    // 入力されたポイント番号を取得
    $point_no = $request->input('point_no');
    // すでに登録済みか調べる
    $exits = Get_point::where('team_no', $team_no)->where('point_no', $point_no)->exists();
    
    // あたらしくGet_pointテーブルにレコードを追加
    if ($exits){
        // team_pointへ戻る前にLockを一旦解除
        $user->role = 0; // ロック解除
        $user->save(); // データベースに保存// ポイント番号入力画面で戻る
        return redirect()->back()->with('message', 'すでに登録済みのポイントです');
    }else{
        Get_point::create([
        'team_no' => $team_no,
        'point_no' => $point_no,
        'checked' => 5,
        'photo_filename' => null
        ]);
    }
    // team_pointへ戻る前にLockを一旦解除
    $user->role = 0; // ロック解除
    $user->save(); // データベースに保存// ポイント番号入力画面で戻る
    return redirect()->route( 'team_point' , [ 'id' => $id ]);
}


// 取得写真一覧画面の作成
public function all_images($flag)
{
    // 現在ログインしているuserのチーム番号を取得
    $user = Auth::user();
    $team_no = $user->team_no;
    
    // get_pointテーブルから特定のteam_noのレコードをすべて取得
    $get_points = Get_point::where('team_no', $team_no)->with('setPoint')->get();

    // 一覧画面へ引き渡し
    return view('all_images', compact('flag' , 'get_points' , 'user'));

}

// 写真一覧からポイント番号画面の呼び出し
public function all_images_exchange(Request $request)
{
// パラメータを受け取る
$set_point_no = request('set_point_no');
$get_point_id = request('get_point_id');

$user = Auth::user();
// スタッフ編集中の場合はflag=4 で戻る
if ( $user->role == 3){
    return redirect()->route('all_images' , ['flag' => 4] );
}

// 設定ポイントのリストを渡す準備
$set_points = Set_point::all();
// 現在のポイント番号を渡す
// 取得写真のurlを渡す

$get_point = Get_point::find($get_point_id);
$get_photo_filename = $get_point ? $get_point->photo_filename : null ;

// ポイント番号変更画面を呼び出す
return view('all_images_exchange', compact('set_point_no' , 'set_points', 'user' , 'get_point_id' , 'get_photo_filename' ));

}

// 写真一覧からポイント番号変更画面から戻ってGetテーブルの変更
public function all_images_change_get(Request $request)
{
    // パラメータの受け取り
    $set_point_no = request('set_point_no');
    $get_point_id = request('get_point_id');
    $user = Auth::user();
    $team_no = $user->team_no;

    // ダブっていないか確認
    $get_point_before = Get_point::where('team_no', $team_no)->where('point_no', $set_point_no)->first();

    // ダブっている場合はflag=2 で一覧へ戻る
    if( $get_point_before ){
        return redirect()->route('all_images' , ['flag' => 2] );
    }

    // なんらかの理由でset_point_noがない場合はシステムエラー
    $set_point = Set_point::where('point_no', $set_point_no)->first();
    if (!$set_point) {
        return redirect()->route('all_images' , ['flag' => 5] );
    }
    
    // ダブっていない場合はGetテーブルを書き換える
    $get_point = Get_point::find($get_point_id);
    if ($get_point) {
        // S3のファイル名を変更
        $oldfilename = basename($get_point->photo_filename);
        // 拡張子の取得
        $ext = pathinfo($oldfilename, PATHINFO_EXTENSION);
        // 新しいファイル名を生成
        $newfilename = "get_" . $set_point_no . "_" . $get_point->team_no . "." . $ext;
        // S3のファイル名を変更
        Storage::disk('s3')->copy($oldfilename, $newfilename);
        // 古いファイルを削除
        Storage::disk('s3')->delete($oldfilename);
        // 新しいURLを取得
        $newurl = Storage::disk('s3')->url($newfilename);

        // 前の取得写真のGetテーブルを書き換え
        $get_point->point_no = $set_point_no ;
        $get_point->photo_filename = $newurl;
        $get_point->checked = 0; // 確認待ちに変更
        $get_point->save(); // データベースに保存

    // flg=1で写真一覧へ戻る
    return redirect()->route('all_images' , ['flag' => 1] );
    }
    // getテー物に対象¥対象レコードがなかった-エラー
    return redirect()->route('all_images' , ['flag' => 5] );
}


// 写真一覧から写真の登録画面の呼び出し-手入力対応
public function all_images_photo(Request $request)
{
    $user = Auth::user();
    // スタッフ編集中の場合はflag=4 で戻る
    if ( $user->role == 3){
        return redirect()->route('all_images' , ['flag' => 4] );
    }
    
    // パラメータの受け取り
    $set_point_no = request('set_point_no');
    $get_point_id = request('get_point_id');

    // 受け渡し準備
    // 設定場所名
    $set_point = Set_point::where('point_no', $set_point_no)->first();
    $set_point_name = $set_point->point_name;
    // Setファイル名
    $key = "set_" . $set_point_no . ".JPG";
    $url = Storage::disk('s3')->url($key);

    
    // 手入力対応の写真登録画面を呼び出す
    return view( 'all_images_photo_get' , compact('set_point_no' ,'set_point_name' ,  'user' , 'get_point_id' ,'url' ));

}

// 写真一覧から写真の登録画面から戻って写真の登録、Getテーブルの変更
public function all_images_change_photo(Request $request)
{
// ログインユーザーとチーム番号の取得
    $user = Auth::user();
    $team_no = $user->team_no;
    $role = $user->role;
    
    if($role == 3){
        // チーム番号が3の場合は、スタッフ編集中のためflag=4で戻す
        return redirect()->route('all_images',['flag' => 4] );
    }

    // 入力パラメータの取得
    // 設定ポイント番号
    $set_point_no = $request->input('set_point_no');
    // Getテーブルのid
    $get_point_id = $request->input('get_point_id');
    // Base64データを取得
    $imageData = $request->input('image');
    // データURLからBase64部分を抽出
    list($type, $data) = explode(';', $imageData);
    list(, $data) = explode(',', $data);
    // Base64デコードしバイナリデータへ変換
    $data = base64_decode($data);
    
    // ファイル名を生成
    $filename = "get_" . $set_point_no . "_" . $team_no  . ".jpg";
    
    // S3へアップロード
    // 画像を一旦tempフォルダへ保存
    $tempFilePath = sys_get_temp_dir() . '/' . $filename;
    file_put_contents($tempFilePath, $data);
    // S3にアップロード
    $path = Storage::disk('s3')->putFileAs('/', new File($tempFilePath) ,$filename);
    // S3のファイルパスを返す
    $url = Storage::disk('s3')->url($path );
    // 一時ファイルを削除
    unlink($tempFilePath);

    // Get_point のテーブルの修正
    // get_point_id のレコードが存在する場合のみ、filenameを書き換える
    $get_point = Get_point::find($get_point_id);
    if($get_point){
        $get_point->photo_filename = $url;
        $get_point->save(); // データベースに保存    
        }
    //  flag=3で写真一覧へ戻る
    return redirect()->route('all_images' , ['flag' => 3] );   

}



/**
 * 
 *  参加者メイン画面へのコントローラ
 * 
 * 
 */


public function get_point($flag ,Request $request)
{

// ルートパラメータに$flagをいれる メッセージ表示の制御
// ０.メッセージなし（ログイン直後、戻る、ほか）
// １．「登録しました」
// ２．「変更しました」
// ３．「現在、スタッフが編集中」る

// これを受け、get_point コントローラは以下の動作を行う
// set_pointの選択肢を渡す準備
// 次のパラメータをcompactでView:get_point_2 へ渡す
// flag set_points[] user 

// セッションからデータを取り出し
// $set_point_no = session('set_point_no');
// $get_point_id = session('get_point_id');

// get_photo_filenameをテーブルから引き出す
// $get_point = Get_point::find($get_point_id);
// $get_photo_filename = $get_point ? $get_point->photo_filename : null ;

// 設定ポイントのリストを渡す準備
$set_points = Set_point::all();

// ログインユーザーを渡す
$user = Auth::user();

// 参加者メイン画面を呼び出す
// 本番用改良画面を呼び出す
return view('get_point_2', compact('flag' , 'set_points', 'user' ));
}

/**
 * 
 *  UPLOADされた写真をAWS-S3へ保存し、get_pointテーブルを追加し仮登録する
 *  point 設定で選択されているポイント番号
 *  imaage アップロードされた写真
 * 　この時点では、checked=4 設定ポイント未定にしておくー＞一覧表示でエラーになるので
 */
public function upload_image(Request $request)
{
    // UPLOADされたファイルが画像であることにチェック
    $request->validate([
        'image' => 'required|mimes:jpg,jpeg,png,gif,heic,heif|max:20240000',
    ], [
        'image.required' => '画像ファイルは必須です。',
        'image.mimes' => '画像ファイルはjpg、jpeg、png、gif、heic、heif形式でなければなりません。',
        'image.max' => '画像ファイルのサイズは20MB以下でなければなりません。',
    ]);

    // アップロードされたファイルの容量を取得
    // $filesize = $request->file("image")->getSize();

    if ($request->hasFile('image')) {
        try{
        // アップロードされたファイルを取得
        $file = $request->file('image');
        // アップロードされた元のファイルの拡張子を取得
        $originalExt = $file->getClientOriginalExtension();

        // 保存時のファイル名を生成 拡張子を除く
        $team_no = Auth::user()->team_no;
        $randomString = Str::random(5); 
        $filename = "get_" . $randomString . "_" . $team_no ;
    
        // UPLOAD画像読み込み
        $manager = new ImageManager(new Driver());
        $image = $manager->read($file);


        // ここからトライアル    
        // ファイル形式をJPGに変更
        if($originalExt == 'heic'){
            $image->encodeByExtension('jpg');
            $filename = $filename . ".jpg";
        }else{
            $filename = $filename . "." . $originalExt;
        }
    

        // 写真の横幅を調整
        $width = $image->width();
        if ($width > 600) {
            $image->scaleDown(width: 600);
        }
        // 一時ファイルに保存
        $tempFilePath = sys_get_temp_dir() . '/' . $filename;
        $image->save($tempFilePath);
    }catch(\Exception $e){
        return redirect()->route('get_point',['flag' => 0] )->withErrors(['image' => '画像のアップロードに失敗しました。']);

    }

        // S3にアップロード
        $path = Storage::disk('s3')->putFileAs('/', new File($tempFilePath) ,$filename);
        // S3のファイルパスを返す
        $url = Storage::disk('s3')->url($path );
        // 一時ファイルを削除
        unlink($tempFilePath);

        // Get_point のテーブルに追記
        $get_point = Get_point::create([
            'team_no' => $team_no,
            'point_no' => 0,
            'checked' => 4,
            'photo_filename' => $url,
        ]);
        // 追加されたレコードのidを取得
        $get_point_id = $get_point->id;
        
        //アップロードから戻る flag=0 写真は登録されていない
        session(['get_point_id' => $get_point_id]);
        session(['set_point_no' => $request->input('set_point_no')]);
        return redirect()->route('get_point',['flag' => 0] );
    }

    // return back()->withErrors(['image' => '画像のアップロードに失敗しました']);
    return redirect()->route('get_point',['flag' => 0] )->withErrors(['image' => '画像のアップロードに失敗しました。']);
}

public function bug($id , Request $request)
{
   
    $user_id = $request->input('user_id');
    $get_id = $request->input('get_id');
    $flag = $request->input('flag');

    return view('debug', compact('id' , 'user_id' , 'get_id' , 'flag'));
}


// 本番用の取得写真登録
// 取得写真画面から圧縮したJPEG画像と設定ポイント番号が送られてくる　
// ログインユーザーからチーム番号を割り出し、Lockされていれば何もせず戻る
// Getテーブルを検索
// ダブっていなければ、そのまま本登録
// ダブっていれば、ユーザーに変更するか問い合わせる画面へつなぐ

public function confirm_get_point_2(Request $request)
{
    // ログインユーザーとチーム番号の取得
    $user = Auth::user();
    $team_no = $user->team_no;
    $role = $user->role;
    
    if($role == 3){
        // チーム番号が3の場合は、スタッフ編集中のためflag=3で戻す
        return redirect()->route('get_point',['flag' => 3] );
    }

    // 入力パラメータの取得
    // 設定ポイント番号
    $set_point_no = $request->input('set_point_no');
    // Base64データを取得
    $imageData = $request->input('image');
    // データURLからBase64部分を抽出
    list($type, $data) = explode(';', $imageData);
    list(, $data) = explode(',', $data);
    // Base64デコードしバイナリデータへ変換
    $data = base64_decode($data);
    
    // 同じチーム番号、設定ポイントで本登録されたレコードがないか検索
    $get_point_before = Get_point::where('team_no', $team_no)->where('point_no', $set_point_no)->first();
    // ダブっていない場合は、
    // 本登録する。ファイル名は get_setpoinnto_temano.jpg S3にアップロード
    // Getテーブルにチェック0未確認で追加
    // ダブっている場合は、
    // 仮登録する。ファイル名は　get_ramdom_temano.jpg S3にアップロード
    // Getテーブルにチェック4仮登録で追加   

  // ファイル名を生成
    if($get_point_before){
        // ダブっている場合 仮登録
        $randomString = Str::random(5);
        $filename = "get_" . $randomString . "_" . $team_no  . ".jpg";
    }else{
        // ダブっていない場合　本登録
        $filename = "get_" . $set_point_no . "_" . $team_no  . ".jpg";
    }

    // S3へアップロード
    // 画像を一旦tempフォルダへ保存
    $tempFilePath = sys_get_temp_dir() . '/' . $filename;
    file_put_contents($tempFilePath, $data);
    // S3にアップロード
    $path = Storage::disk('s3')->putFileAs('/', new File($tempFilePath) ,$filename);
    // S3のファイルパスを返す
    $url = Storage::disk('s3')->url($path );
    // 一時ファイルを削除
    unlink($tempFilePath);

    // Get_point のテーブルに追記
    if($get_point_before){
        // ダブっている場合 仮登録
        $get_point = Get_point::create([
            'team_no' => $team_no,
            'point_no' => 0,
            'checked' => 4,
            'photo_filename' => $url,
        ]);
    }else{
        // ダブっていない場合　本登録
        $get_point = Get_point::create([
            'team_no' => $team_no,
            'point_no' => $set_point_no,
            'checked' => 0,
            'photo_filename' => $url,
        ]); 
    }
    
    // 次の画面へ
    if($get_point_before){
        // ダブっている場合 変更するか確認画面へ
        // 受け渡しパラメータをセット $get_point_id ,$get_point_before_id
        $user = Auth::user(); 
        $set_point = Set_point::where('point_no' , $set_point_no)->first();
        $set_photo_filename = Storage::disk('s3')->url("set_" . $set_point_no . ".JPG");
        return view('confirm_get_point_2' , compact( 'user','get_point' , 'get_point_before' , 'set_point', 'set_photo_filename' ));
    }else{
        // ダブっていない場合　取得写真登録画面へ戻る
        // flag=1で戻して、戻ったところで「登録しました」のメッセージを表示
        $flag = 1;
        return redirect()->route('get_point',['flag' => $flag] )->with('message', '登録しました');
    }
}

// 取得写真がダブっていて、前のGetを変更する場合
public function exchange_get(Request $request)
{
    // パラメータとして以下を受け取る
    // set_point_no：設定ポイント番号
    // get_point_id：新しく登録しようとしているGetテーブルのid
    // get_point_before_id：前に登録したGetテーブルのid
    // 前に登録したGetテーブルのレコードを仮登録に変更
    // S3のファイル名を仮登録名に変更
    // 新しく登録しようとしているGetテーブルのレコードを本登録に変更
    // S3のファイル名を本登録名に変更
    // ここにくる前は、新しく登録しようとしている取得写真は仮登録されている

    $set_point_no = $request->input('set_point_no');
    $get_point_id = $request->input('get_point_id');    
    $get_point_before_id = $request->input('get_point_before_id');

    $get_point_before = Get_point::find($get_point_before_id);
    $get_point = Get_point::find($get_point_id);
    
    // 前の写真を仮登録へ
    // S3のファイル名を書き換え
    // ファイル名の取得
    if($get_point_before){
        $oldfilename = basename($get_point_before->photo_filename);
        // 拡張子の取得
        $ext = pathinfo($oldfilename, PATHINFO_EXTENSION);
        // 新しいファイル名を生成
        $randomString = Str::random(5);
        $newfilename = "get_" . $randomString . "_" . $get_point_before->team_no . "." . $ext;
        // S3のファイル名を変更
        
        Storage::disk('s3')->copy($oldfilename, $newfilename);
        // 古いファイルを削除
        Storage::disk('s3')->delete($oldfilename);
        // 新しいURLを取得
        $newurl = Storage::disk('s3')->url($newfilename);

        // 前の取得写真のGetテーブルを仮登録に書き換え
        $get_point_before->point_no = 0 ;
        $get_point_before->photo_filename = $newurl;
        $get_point_before->checked = 4; // 仮登録
        $get_point_before->save(); // データベースに保存
    }
    
    // 新しい写真を本登録へ
    if($get_point){
        // S3のファイル名を書き換え
        // ファイル名の取得
        $oldfilename = basename($get_point->photo_filename);
        // 拡張子の取得
        $ext = pathinfo($oldfilename, PATHINFO_EXTENSION);
        // 新しいファイル名を生成
        $newfilename = "get_" . $set_point_no . "_" . $get_point->team_no . "." . $ext;
        // S3のファイル名を変更
        Storage::disk('s3')->copy($oldfilename, $newfilename);
        // 古いファイルを削除
        Storage::disk('s3')->delete($oldfilename);
        // 新しいURLを取得
        $newurl = Storage::disk('s3')->url($newfilename);

        // あたらしいGetテーブルを書き換え
        $get_point->point_no = $set_point_no ;
        $get_point->photo_filename = $newurl;
        $get_point->checked = 0; // 本登録
        $get_point->save(); // データベースに保存
    }
    // 取得写真登録画面へ戻る
    // flag=2で戻って、戻り先で「変更されました」を表示
    return redirect()->route( 'get_point' , [ 'flag' => 2] );
}

public function confirm_get_point(Request $request)
{
// 取得写真と設定ポイントを結びつける
// 以下のパラメータを受け取る
// flag:0最初にここに入った　1確認画面View:confirm_get_pointから変更ありでかえってきた
// set_point_no:セットする設定ポイント番号
// get_point_id:ロックオンされているget_pointのid
// 中で現在ログオン中のuserからteam_noを取得
// 以下の動作をする
// flag=0 初めて入ってきた
// 　team_no set_point_no がget_pointテーブルに登録されいるか判定
// 　　登録されていない場合(重複していない場合)
// 　　　新規登録として、get_pointテーブルの書き換え、awsファイル名の変更
// 　　　flag=0 でconfirm_get_point確認画面へ「変更しました」ー＞get_point画面へ（次の登録へ）
// 　　登録されている場合（重複している場合）
// 　　　flag=1でconfirm_get_point確認画面へ「このまま変更」ー＞flag1で戻ってくる
//                                       「戻る」ー＞get_point画面へ（設定ポイント番号選びなおし）
// flag=1 ダブっているけどこのまま登録
// 　　前のget_pointのid レコードを仮登録へ書き換え、AWSファイル名変更
// 　　ロックオンされているレコードを本登録。AWSファイル名変更
// 　　flag=2でconfirm_get_point確認画面へ「戻る」ー＞get_point画面へ

// ログインユーザーとチーム番号の取得
$user = Auth::user();
$team_no = $user->team_no;

// 入力パラメータの取得
$flag = $request->input('flag');
$set_point_no = $request->input('set_point_no');
$get_point_id = $request->input('get_point_id');
// 同じチーム番号、設定ポイントで本登録されたレコードがないか検索
$get_point_before = Get_point::where('team_no', $team_no)->where('point_no', $set_point_no)->first();
// ロックオンされているレコードを取得
$get_point = Get_point::where('id' , $get_point_id)->first();


if($get_point_before){
    // ダブっている場合
    if ($flag == 0){
    // 初めての登録 flag=0 の場合は
    // ダブりの確認画面confirm_get_pointをflag=0で呼び出す
    // 受け渡しパラメータをセット
    $set_point = Set_point::where('point_no' , $set_point_no)->first();
    // 設定ポイントのファイル名を生成
    $key="set_" . $set_point_no . ".JPG";
    $set_photo_filename = Storage::disk('s3')->url($key);

    $set_point_name = $set_point->point_name;
    $get_photo_filename = $get_point->photo_filename ;
    $before_photo_filename = $get_point_before->photo_filename ;
    $flag = 0 ;
    $user = Auth::user();

    return view('confirm_get_point' , compact( 'flag' , 'user' , 'set_point_no' , 'set_point_name' ,  'set_photo_filename', 'get_point_id' , 'get_photo_filename' ,'before_photo_filename'));

    }else{
    // ダブり確認画面からそのまま変更flag=1で戻ってきた場合
    // 前の本登録を仮登録へ変更
    if( $get_point_before){
        // 拡張子の取得
        $ext = pathinfo($get_point_before->photo_filename, PATHINFO_EXTENSION);
        // ファイル名の取得
        $oldfilename = basename($get_point_before->photo_filename);
        // 新しいファイル名の生成
        $randomString = Str::random(5); 
        $filename= "get_" . $randomString . "_" . $team_no . "." . $ext;
        // 写真ファイル名の変更
        // ファイルのコピー
        Storage::disk('s3')->copy($oldfilename, $filename);
        // 元のファイルの削除
        Storage::disk('s3')->delete($oldfilename);
        // 変更後のファイルのURLを取得
        $newurl = Storage::disk('s3')->url($filename);
        // get_pointテーブルのレコードの変更
        $get_point_before->point_no = 0;
        $get_point_before->photo_filename = $newurl;
        $get_point_before->checked = 4;
        $get_point_before->save();
    }
    // ロックオンのレコードを本登録へ
    if ( $get_point){
        // 拡張子の取得
        $ext = pathinfo($get_point->photo_filename, PATHINFO_EXTENSION);
        // ファイル名の取得
        $oldfilename = basename($get_point->photo_filename);
        // 新しいファイル名の生成
        $filename= "get_" . $set_point_no . "_" . $team_no . "." . $ext;
        // 写真ファイル名の変更
        // ファイルのコピー
        Storage::disk('s3')->copy( $oldfilename, $filename);
        // 元のファイルの削除
        Storage::disk('s3')->delete( $oldfilename );
        // 変更後のファイルのURLを取得
        $newurl = Storage::disk('s3')->url($filename);
        // get_pointテーブルのレコードの変更
        $get_point->point_no = $set_point_no;
        $get_point->photo_filename = $newurl;
        $get_point->checked = 0;
        $get_point->save();
    }

        // get_point画面へ戻る
        // set_point の　最初のレコードをセット
        $set_point = Set_point::first();
        session(['set_point_no' => $set_point->point_no]);
        session(['get_point_id' => 0]);
        
        return redirect()->route('get_point',['flag' => 1] );

    }
}else{
    // ダブっていない場合はそのまま本登録
    // 本登録ファイル名の生成
    // 拡張子の取得
    $ext = pathinfo($get_point->photo_filename, PATHINFO_EXTENSION);
    // ファイル名の抽出
    $oldfilename= basename($get_point->photo_filename);

    // \Log::debug('oldFilename:' . $oldfilename);
    // 新しいファイル名の生成
    $filename= "get_" . $set_point_no . "_" . $team_no . "." . $ext;
    // 写真ファイル名の変更
    // ファイルのコピー
    Storage::disk('s3')->copy($oldfilename, $filename);
    // 元のファイルの削除
    Storage::disk('s3')->delete($oldfilename);
    // 変更後のファイルのURLを取得
    $newurl = Storage::disk('s3')->url($filename);
    // get_pointテーブルのレコードの更新
    // データベースの更新
    $get_point->point_no = $set_point_no;
    $get_point->photo_filename = $newurl;
    $get_point->checked = 0;
    $get_point->save();     

    // 取得画面get_point画面へ戻る
    // 受け渡しパラメータをセッションにセット
    // set_point_noは最初のレコードをセット
    $set_point = Set_point::first();
    session(['set_point_no' => $set_point->point_no]);
    session(['get_point_id' => 0]);
    return redirect()->route('get_point',['flag' => 1] );
}
}

/**
 * 
 * Blade の　Confirm_get_pointからRequestを受け取り、Controllerのget_pointへ受け渡す
 * flag は　ルートパラメータで渡す
 * ただし、set_point_no が０の場合は、set_pointの第一レコードの番号に変える
 */
public function storeSessionData(Request $request)
{
    // フォームから送信されたデータをセッションに保存
    // set_point_no が　０の場合は第一レコードへ変換
    if ($request->input('set_point_no') == 0){
        $set_point = Set_point::first();
        $set_point_no = $set_point->point_no;
    }else{
        $set_point_no = $request->input('set_point_no');
    }
    session(['set_point_no' => $set_point_no]);
    session(['get_point_id' => $request->input('get_point_id')]);
    $flag = $request->input('flag');
    // get_pointルートにリダイレクト
    return redirect()->route('get_point' , ['flag' => $flag]);
}

// 成績速報画面
public function result()
{
    // カテゴリー一覧を準備
    $categories = Category::all();
    // ログインユーザーを準備
    $user = Auth::user();

    return view('result' , compact('categories' , 'user'));


}

// カテゴリー別の成績データを作る
public function getResults(Request $request)
{
    // クエリパラメータからカテゴリIDを取得
    $categoryId = $request->query('category_id');

    // カテゴリIDに基づいて該当するチームデータを取得
    $results = User::where('category_id', $categoryId)->get();
    
    // 結果を入れる配列を準備
    $resultArray = [];
    // 該当チームすべてに対し
    foreach ($results as $result) {
        
        // 減点を取得
        $penalty = $result->penalty;
        // 巡ったポイントのうち、OKまたは手入力のポイントを取得
        $get_points = Get_point::where('team_no', $result->team_no)->whereIn('checked', [2,5])->get();
        // 未確認待ちのポイント数をカウント
        $checking_num = Get_point::where('team_no', $result->team_no)->where('checked', 0)->count();
        // NGのポイント数をカウント
        $ng_num = Get_point::where('team_no', $result->team_no)->where('checked', 3)->count();
        // ポイント番号不明をカウント
        $unknown_num = Get_point::where('team_no', $result->team_no)->where('checked', 4)->count();
        // 表示用文字列に変換 と同時に総合得点を計算
        $result_str = "";
        $total_score = 0;

        foreach ($get_points as $get_point) {
            if($get_point->setPoint){
                $result_str .= $get_point->point_no . ":" . $get_point->setPoint->point_name . "ー";
                $total_score += $get_point->setPoint->score;
            }else{
                $result_str .= $get_point->point_no . ":不明" . "ー";
            }
            
        }
        
        // 減点を加える
        $total_score -= $penalty;
                
        $resultArray[] = [
            'team_no' => $result->team_no,
            'team_name' => $result->name,
            'member_num' => $result->member_num,
            'checking_num' => $checking_num,
            'ng_num' => $ng_num,
            'unknown_num' => $unknown_num,
            'penalty' => $penalty,
            'result_str' => $result_str,
            'total_score' => $total_score,
        ];
    }
    
    // 配列を得点の順番に並べ替える
    usort($resultArray, function($a, $b) {
        return $b['total_score'] <=> $a['total_score'];
    });
    
    return response()->json(['results' => $resultArray]);
}


/**
 * 
 *  スタッフメイン画面
 * 
 */
public function staff_main(){
    // 未確認写真の数
    $unchecked_count = Get_point::where('checked', 0)->count();
    // 確認中写真の数
    $checking_count = Get_point::where('checked', 1)->count();
    // OK写真の数
    $ok_count = Get_point::where('checked', 2)->count();
    // NG写真の数
    $ng_count = Get_point::where('checked', 3)->count();
    
    $total_count = $unchecked_count + $checking_count + $ok_count + $ng_count;
    
    $data = [
        'unchecked_count' => $unchecked_count,
        'checking_count' => $checking_count,
        'total_count' => $total_count,
    ];

    return view( 'staff_main' ,$data );
                                
}

// ポイント履歴一覧
public function point_history()
{
    // ポイント履歴を取得
    $set_ponits = Set_point::all();
    // 結果を入れる配列
    $results = [];
    
    foreach ($set_ponits as $set_point) {
        // ポイント番号
        $point_no = $set_point->point_no;
        // ポイント名
        $point_name = $set_point->point_name;
        // そのポイントを通過したチーム番号OKと手入力を取得し１列の文字列にする
        $get_points = Get_point::where('point_no', $point_no)->whereIn('checked', [2,5])->get();
        // ポイント毎の通過チーム番号を文字列に
        $result_str = "";
        foreach ($get_points as $get_point) {
            $team_no = $get_point->team_no;
            $user = User::where('team_no', $team_no)->first();
            $team_name = $user ? $user->name :  '不明' ;
            $result_str .= $team_no . ":" . $team_name . "ー";
        }
        $results[] = [
            'point_no' => $point_no,
            'point_name' => $point_name,
            'point_history' => $result_str,
        ];
    }

    return view('point_history', compact('results'));
}

// 取得写真のダウンロード
public function download_get_photo( Request $request )
{
    // ファイル名を取得
    $filename = $request->input('filename');
    
    // S3のURLからファイルパスを抽出
    if (filter_var($filename, FILTER_VALIDATE_URL)) {
        $parsedUrl = parse_url($filename);
        $filename = ltrim($parsedUrl['path'], '/');
    }

    // ファイル名がnullや拡張子がない場合をはじく
    if (empty($filename) || !pathinfo($filename, PATHINFO_EXTENSION)) {
        // ファイル名が空、または拡張子がない場合は404エラー
        return redirect()->back()->with('message', '写真ファイルがありません');
    }

    // S3からファイルを取得
    $disk = Storage::disk('s3');

    try{
        if (!$disk->exists($filename)) {
            return redirect()->back()->with('message', '写真ファイルがありません');
        }
        $file = $disk->get($filename);
    }catch(\Exception $e){
        // エラーが発生した場合はログに記録し、エラーメッセージを表示
        return redirect()->back()->with('message', '写真ファイルがありません');
    }

    // ファイルをダウンロード
    return response($file, 200)
        ->header('Content-Type', $disk->mimeType($filename))
        ->header('Content-Disposition', 'attachment; filename="' . basename($filename) . '"');
    
}

// CSVファイル圧縮テスト画面 ファイル選択
public function canvas_test()
{
    return view('test_canvas');
}

// 画像ファイルの受け取り
public function canvas_upload_test(Request $request)
{
    try {
        // Base64データを取得
        $imageData = $request->input('image');

        // 設定番号を取得
        $selected_point_no = $request->input('set_point_no');

        // データURLからBase64部分を抽出
        list($type, $data) = explode(';', $imageData);
        list(, $data) = explode(',', $data);

        // Base64デコード
        $data = base64_decode($data);

        

        // 画像情報を取得（メモリ上で処理）
        $imageInfo = getimagesizefromstring($data);
        $width = $imageInfo[0]; // 横幅
        $height = $imageInfo[1]; // 高さ
        $filesize = strlen($data); // データサイズ（バイト単位）
       
        // Base64形式の画像データを生成
        $base64Image = 'data:' . $imageInfo['mime'] . ';base64,' . base64_encode($data);
        
        // ファイル名を生成して保存
        // $fileName = 'canvas_image_' . time() . '.jpg';
        // $filePath = public_path('uploads/' . $fileName);

        // // ディレクトリが存在しない場合は作成
        // if (!file_exists(public_path('uploads'))) {
        //     mkdir(public_path('uploads'), 0777, true);
        // }

        // // ファイルを保存
        // file_put_contents($filePath, $data);

        // 成功レスポンスを返す
        $result = "成功しました";

        return view('test_canvas_2', compact('result','width','height','filesize','base64Image', 'selected_point_no'));
        
    } catch (\Exception $e) {
        // エラーをログに記録
        \Log::error($e->getMessage());

        // エラーレスポンスを返す
        $result = "エラーが発生しました。";
        return view('test_canvas_2', compact('result'));
        
    }

}

}
