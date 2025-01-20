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
        
        \Log::debug('LoginOK');
        
        $user = Auth::user();

        // 役割が１：管理者の場合,ポイント設定画面へ直リンク
        if(Auth::check() && $user->role == 1){
            return view( 'set_point');
        }
        
        // 役割が２：スタッフの場合、チェック画面へ直リンク
        if(Auth::check() && $user->role == 2){
            return redirect()->route('staff_main');
        }
    
        // 役割が０（それ以外）、参加者メイン画面のコントローラーへ
        // flag=0で渡して、ログイン直後であることを伝える
        // set_point_no は　最初レコードの番号を渡す
        $set_point = Set_point::first();
        session(['set_point_no' => $set_point->point_no]);
        // $get_point_id はログイン直後はなし
        session(['get_point_id' => 0]);
        // ログイン直後はflag=0 前で写真登録なしで入る
        return redirect()->route('get_point',['flag' => 0]);
    }

    // public function upload_set()
    // {
    //     return view('upload_set');
    // }

    // public function import_set(Request $request)
    // {
    //     $request->validate([
    //         'files.*' => 'required|file|mimes:jpg,jpeg,png.gif',
    //     ]);

    //     if($request->hasFile('files')){
    //         foreach ($request->file('files') as $file) {
    //             $originalName = $file->getClientOriginalName();
    //             $path = $file->storeAs('uploads' , $originalName, 'public');
    //         }
    //     }
    //     $files = Storage::disk('public')->files('uploads');
    //     return view('verify_set', compact('files'));
    // }
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
    
    return view('set_point');
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
return view('set_point');
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
return view('set_point');
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
return view('set_point');
}



/**
 * 写真判定画面
 * 
 */
// 未チェックの写真を取得
public function next_get_point()
{
    // 最初の未チェック写真を探す
    $next_point = Get_point::where('checked', 0)->first();
    // チェック中フラグを立てる
    if ($next_point) {
        // checkedカラムの内容を書き換える
        $next_point->checked = 1; // 確認中
        $next_point->save(); // データベースに保存
    
        // 設定写真のURLを生成
        $key = "set_" . $next_point->point_no . ".JPG";
        $set_photo_url = Storage::disk('s3')->url($key);
        $set_point = Set_point::where('point_no', $next_point->point_no)->first();
        $set_point_name = $set_point->point_name;
        
        // $set_photo_url = "https://rogain.s3.amazonaws.com/set_" . $next_point->point_no . ".JPG"; 
        // 取得写真のURL
        $get_photo_url = $next_point->photo_filename ;
        // チーム番号とチーム名,メンバー数を準備する
        $team_no = $next_point->team_no;
        $team_name = $next_point->user->name; //リレーションでUsrからチーム名を取得
        $member_num = $next_point->user->member_num;

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
    // 参加者のみの情報を取得
    $users = User::with('category')->where('role', 0)->get();
    // チーム情報を渡す
    return view('team_index', compact('users'));
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

// 取得写真一覧画面の作成
public function all_images()
{
    // 現在ログインしているuserのチーム番号を取得
    $user = Auth::user();
    $team_no = $user->team_no;

    // get_pointテーブルから特定のteam_noのレコードをすべて取得
    $get_points = Get_point::where('team_no', $team_no)->with('setPoint')->get();

    // 一覧画面へ引き渡し
    return view('all_images', compact('get_points' , 'user'));

}

// ポイント番号変更
// public function edit_point($id)
// {
//     // ユーザー情報
//     $user = Auth::user();
//     // 設定ポイント情報
//     $set_points = Set_point::all();
//     // id からレコードを取得
//     $get_point = Get_point::find($id);
    
//     if($get_point){
//         // ポイント番号変更画面へ渡す
//         return view('edit_point', compact('get_point', 'set_points', 'user'));
//     }else{
//         // get_pointsテーブルのidが不正 本来はありえない
//     }
    
// }

// ポイント番号変更の処理
// public function change_point(Request $request)
// {
//     $user = Auth::user();
//     $change_point_no = $request->input('point');
//     $get_point_id = $request->input('get_point_id');

//     // ポイント番号の変更手順
//     // １．変更後のポイント番号がすでに登録されているか調べる（get_pointテーブル）
//     // ２．まだ登録されていない場合は
//     // 　　写真ファイル名を生成し、aws-s3の該当ファイルのファイル名を変更する。
//     // 　　（コピーした後、前の写真ファイルを削除）
//     // 　　get_pointテーブルの該当レコードのpoint_no,checked=0,photo_filenameを変更
//     // 　　changed_verify.blade.phpに変更後のポイント番号、ポイント名、取得ファイル名、正解ファイル名、flag=0をわたす
//     // ３．すでに登録されている場合は
//     // 　　前に登録したaws-s3の該当ファイルのファイル名を乱数で変更する。
//     // 　　get_pointテーブルの前登録レコードのponit_no=0,cheked=4,photo_filenameを変更
//     // 　　新規レコードを作成し、team_no,point_no,checked=0,photo_filenameを作成
//     // 　　changed_verify.blade.phpに変更後のポイント番号、ポイント名、取得ファイル名、正解ファイル名、ダブったファイル名、flag=1をわたす
//     // ４．chaged_verify.blade.phpではbefore_photo_filenameがあるかないかでダブりを検出
//     //     変更後のポイント番号、ポイント名、取得ファイル、正解ファイルを表示
//     //    ダブった場合は、(前に登録あり)
//     //     変更後のポイント番号、ポイント名、取得ファイル、正解ファイル、ダブったファイルを表示
//     //     ダブったことをウオーニングする

//     // 変更するレコードを取得
//     $change_get_point = Get_point::find($get_point_id)->first();
//     // 正解のポイント名を取得
//     $set_point = Set_point::where('point_no', $change_point_no)->first();
//     $set_point_name = $set_point->point_name;
//     // 正解ファイル名を生成
//     $set_photo_filename = "https://rogain.s3.amazonaws.com/set_" . $change_point_no . ".JPG";

//     // 変更するポイント番号がすでに登録されているか調べる
//     $team_no = $user->team_no;
//     $before_get_point = Get_point::where('team_no' , $team_no )->where('point_no', $change_point_no)->first();

//     if($before_get_point){
//         // 前に登録されたポイントがある場合
//         // 元の取得ファイル名
//         $before_photo_filename = $before_get_point->photo_filename;
//         // 拡張子の取得
//         $ext = pathinfo($before_photo_filename, PATHINFO_EXTENSION);
//         // 新しいファイル名-退避用
//         $change_photo_filename = "get_" . Str::random(10) . "_" . $team_no  . "." . $ext;
//         // 新しいファイル名のURL
//         $change_photo_filename = "https://rogain.s3.amazonaws.com/" . $change_photo_filename;
//         // ファイルのコピー
//         Storage::disk('s3')->copy($before_photo_filename, $change_photo_filename);
//         // 元のファイルの削除
//         Storage::disk('s3')->delete($before_photo_filename);
//         // get_pointテーブルのレコードの変更
//         // データベースの更新
//         $before_get_point->point_no = 0;
//         $before_get_point->photo_filename = $change_photo_filename;
//         $before_get_point->checked = 4;
//         $before_get_point->save();

//         // 変更後の取得ファイル名を生成
//         //    元の取得ファイル名
//         $before_photo_filename = $change_get_point->photo_filename;
//         //    拡張子の取得
//         $ext = pathinfo($before_photo_filename, PATHINFO_EXTENSION);
//         //    変更後のファイル名生成
//         $change_photo_filename = "get_" . $change_point_no . "_" . $team_no . "." . $ext;
//         //    変更後のファイル名のURL
//         $change_photo_filename = "https://rogain.s3.amazonaws.com/" . $change_photo_filename;
//         //    ファイルのコピー
//         Storage::disk('s3')->copy($before_photo_filename, $change_photo_filename);
//         //    元のファイルの削除
//         Storage::disk('s3')->delete($before_photo_filename);
                
//         // レコードの修正
//         $change_get_point->point_no = $change_point_no;
//         $change_get_point->checked = 0;
//         $change_get_point->photo_filename = $change_photo_filename;
//         $change_get_point->save();

//         return view('changed_verify', compact('change_point_no', 'set_point_name' , 'change_photo_filename', 'set_photo_filename', 'before_photo_filename' , 'user'));
        
//     }else{
//         // 前の登録がない場合
//         // 元の取得ファイル名
//         $before_filename = $change_get_point->photo_filename;
//         // 拡張子の取得
//         $ext = pathinfo($before_filename, PATHINFO_EXTENSION);
//         // 新しいファイル名
//         $change_filename = "get_" . $change_point_no . "_" . $team_no . "." . $ext;
//         // 新しいファイル名のURL
//         $change_photo_filename = "https://rogain.s3.amazonaws.com/" . $change_filename;
//         // ファイルのコピー
//         Storage::disk('s3')->copy($before_filename, $change_filename);
//         // 元のファイルの削除
//         Storage::disk('s3')->delete($before_filename);
//         // get_pointテーブルのレコードの変更
//         // データベースの更新
//         $change_get_point->point_no = $change_point_no;
//         $change_get_point->photo_filename = $change_filename;
//         $change_get_point->checked = 0;
//         $change_get_point->save();     
        
//         $before_photo_filename = null;

//         return view('changed_verify', compact('change_point_no', 'set_point_name' , 'change_photo_filename', 'set_photo_filename' , 'before_photo_filename' , 'user' ));
//     }
// }


/**
 * 
 *  参加者メイン画面へのコントローラ
 * 
 * 
 */


public function get_point($flag ,Request $request)
{

// ルートパラメータに$flagをいれる
// ０.ログイン直後
// １．取得写真のアップロード後
// ２．写真一覧から変更要請
// ３．新規登録して戻る
// ４．だぶりあったが変更せず戻る
// ５．だぶって変更して戻る

// セッションに以下２つを入れる
// set_point_no ここに入ったときに最初に表示する設定ポイント番号　何もなければ１
// get_point_id 現在アップロード（ロックオンされている）get_point の　id 何もなければ　０

// これを受け、get_point コントローラは以下の動作を行う
// set_pointの選択肢を渡す準備
// アップされているget_point の写真フィル名（AWS）
// 次のパラメータをcompactでView:get_point へ渡す
// flag set_point_no set_points[] get_point_id get_photo_filename user 

// 前提として、アップロードされた写真はすぐにget_pointテーブルに仮名で登録されている
// ユーザーが指定した設定ポイント番号がすでに登録済みの場合や
// 写真一覧から変更要請があった場合も、対象のget_point_id が渡される
// ログイン直後はget_point_id は０。その場合は　flag=0


// セッションからデータを取り出し
$set_point_no = session('set_point_no');
$get_point_id = session('get_point_id');

// get_photo_filenameをテーブルから引き出す
$get_point = Get_point::find($get_point_id);
$get_photo_filename = $get_point ? $get_point->photo_filename : null ;

// 設定ポイントのリストを渡す準備
$set_points = Set_point::all();

// ログインユーザーを渡す
$user = Auth::user();

// 参加者メイン画面を呼び出す
return view('get_point', compact('flag' , 'set_point_no' ,'set_points', 'get_point_id' , 'get_photo_filename' , 'user' ));
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
    ]);
    // アップロードされたファイルの容量を取得
    // $filesize = $request->file("image")->getSize();

    if ($request->hasFile('image')) {
        // アップロードされたファイルを取得
        $file = $request->file('image');
        // アップロードされた元のファイルの拡張子を取得
        $originalExt = $file->getClientOriginalExtension();

        // 保存時のファイル名を生成 拡張子を除く
        $team_no = Auth::user()->team_no;
        $randomString = Str::random(5); 
        $filename = "get_" . $randomString . "_" . $team_no ;

        // HEIC形式の場合、JPGに変換
        // if ($originalExt === 'heic') {
        //     $imagick = new Imagick();
        //     $imagick->readImage($file->getPathname());
        //     $imagick->setImageFormat('jpg');
            
        //     // 変換後のファイル名を生成 .jpg
        //     $filename = "{$filename}.jpg";

        //     // 変換後の画像を一時ファイルに保存
        //     $tempFilePath = sys_get_temp_dir() . '/' . $filename;
        //     $imagick->writeImage($tempFilePath);
        // }else{
        //     // HEIC以外の場合
        //     // 保存時のファイル名を生成
        //     $filename=$filename .  "." . $originalExt;
        //      // 変換後の画像を一時ファイルに保存
        //     $tempDir = sys_get_temp_dir();
        //     $tempFilePath = $tempDir . '/' . $filename;
        //     $file->move($tempDir, $filename);
        // }
        // // 一時保存されたファイルの容量
        // $filesize = filesize($tempFilePath);
        // // 容量が１MBを超えた場合は圧縮する
        // if ($filesize > 1024 * 1024) {
        //     $imagick = new Imagick();
        //     $imagick->readImage($tempFilePath);
        //     // $imagick->setImageCompressionQuality(80);
        //     $newWidth = 600; // 幅を600pxに設定
        //     $imagick->resizeImage($newWidth, 0, \Imagick::FILTER_LANCZOS, 1);
        //     $imagick->writeImage($tempFilePath);
        // }

    
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

public function bug()
{
    return view('bug');
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

// \Log::debug($request->all());
// \Log::debug($get_point_before);
// \Log::debug($get_point_id);
// \Log::debug($get_point);
// \Log::debug($set_point_no);


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

        // \Log::debug('set_photo_filename:' . $set_photo_filename);

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
        // 巡ったポイントのうち、OKがでているポイントを取得
        $get_points = Get_point::where('team_no', $result->team_no)->where('checked', 2)->get();
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
            $result_str .= $get_point->point_no . ":" . $get_point->setPoint->point_name . "ー";
            $total_score += $get_point->setPoint->score;
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

    // 結果をJSON形式で返す
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

    // S3からファイルを取得
    $disk = Storage::disk('s3');
    if (!$disk->exists($filename)) {
        abort(404, 'File not found');
    }
    $file = $disk->get($filename);

    // ファイルをダウンロード
    return response($file, 200)
        ->header('Content-Type', $disk->mimeType($filename))
        ->header('Content-Disposition', 'attachment; filename="' . basename($filename) . '"');

    // return response()->download($filename);

    // \Log::debug($filename);
    
    // // S3のURLからファイルパスを抽出
    // if (filter_var($filename, FILTER_VALIDATE_URL)) {
    //     $parsedUrl = parse_url($filename);
    //     $filename = ltrim($parsedUrl['path'], '/');
    // }

    // \Log::debug($filename);

    // // S3からファイルを取得
    // $disk = Storage::disk('s3');
    // $file = $disk->get($filename);

    // // ファイル名を取得
    // $name = basename($filename);

    // // ファイルをダウンロードさせるレスポンスを返す
    // return response($file, 200)
    //     ->header('Content-Type', $disk->mimeType($filename))
    //     ->header('Content-Disposition', 'attachment; filename="' . $name . '"');
}

}