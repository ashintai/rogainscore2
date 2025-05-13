<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ImageController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });


// Route::get('/upload_set',[HomeController::class, 'upload_set'])->name('upload_set');
// Route::post('/import_set',[HomeController::class, 'import_set'])->name('import_set');
// Route::get('/edit-point/{id}', [HomeController::class, 'edit_point'])->name('edit_point');
// Route::post('/change_point' , [HomeController::class, 'change_point'])->name('change_point');
// Route::get('/penalty_gen' , [HomeController::class , 'penalty_gen'])->name('penalty_gen');
Auth::routes();

// ログイン後のページ
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/home', [HomeController::class, 'index'])->name('home2');
Route::get('/index', [HomeController::class, 'index'])->name('index');
// 参加者のログインをスタートまで待機
Route::post('/login_wait', [HomeController::class, 'login_wait'])->name('login_wait');
// Userテーブルの消去
Route::post('/clear_user', [HomeController::class, 'clear_user'])->name('clear_user');
// 取得写真データの消去とAWS-S3のget写真消去
Route::post('/clear_get', [HomeController::class, 'clear_get'])->name('clear_get');
// 非同期でS3no画像URLを取得するためのルートを追加
Route::get('/image-url', [ImageController::class, 'getImageUrl'])->name('image.url');

// 管理者ー設定ポイントDBへの設定
Route::get('/set_point',[HomeController::class, 'set_point'])->name('set_point');
// 管理者ー設定ポイントDBへの設定
Route::post('/pointdata_set',[HomeController::class, 'pointdata_set'])->name('pointdata_set');
// 管理者ーカテゴリーDBへの設定
Route::post('/category_set' , [HomeController::class, 'category_set'])->name('category_set');
// 管理者ーチームDBへの設定
Route::post('/team_set' , [HomeController::class, 'team_set'])->name('team_set');
// 管理者ー取得ダミーデータの設定
Route::post('/dummy_get', [HomeController::class, 'dummy_get'])->name('dummy_get'); 

// 参加者メイン画面
Route::get('/get_point/{flag}', [HomeController::class, 'get_point'])->name('get_point');
// 参加者ー通過ポイント写真のUPLOAD
Route::post('/upload_image', [HomeController::class, 'upload_image'])->name('upload_image');
// 参加者ー取得写真一覧へのリンク
Route::get('/all_images', [HomeController::class, 'all_images'])->name('all_images');
//参加者ーポイント番号変更のルート
Route::post('/confirm_get_point' , [HomeController::class , 'confirm_get_point'])->name('confirm_get_point');
// 参加者の取得写真がダブった場合の変更
Route::post('/exchange_get' , [HomeController::class , 'exchange_get'])->name('exchange_get');
// 参加者ーリクエストからセッションに変換して参加者メイン画面へもどす
Route::post('/store_session_data' , [HomeController::class , 'storeSessionData'])->name('store_session_data');
// 参加者＆スタッフー成績速報　
Route::get('/result' , [HomeController::class , 'result'])->name('result');
// 参加者＆スタッフー成績のデータ取得
Route::get('/get-results', [HomeController::class, 'getResults']);
// 参加者ー取得写真のダウンロード
Route::post('/download_get_photo', [HomeController::class, 'download_get_photo'])->name('download_get_photo');
// Route::get('/download_get_photo/{filename}', [HomeController::class, 'download_get_photo'])->where('filename' , '.*')->name('download_get_photo');
// Route::get('/download_get_photo', [HomeController::class, 'download_get_photo'])->name('download_get_photo');

// スタッフメイン画面
Route::get('/staff_main' , [HomeController::class , 'staff_main'])->name('staff_main');
// スタッフー次の未チェック写真の引き取り
Route::get( '/next_get_point' , [HomeController::class, 'next_get_point'])->name('next_get_point');
// スタッフーチェック結果の登録
Route::post( '/change_checked' , [HomeController::class, 'change_checked'])->name('change_checked');
// スタッフー写真判定からindex経由でスタッフ画面へ戻る
Route::get('/checkpoint' , [HomeController::class , 'index'])->name('checkpoint');
// スタッフー減点入力
Route::get('/input_penalty' , [HomeController::class , 'input_penalty'])->name('input_penalty');
// スタッフー確認中のレコードを未確認へ戻す
Route::get('/reset_checking' , [HomeController::class , 'reset_checking'])->name('reset_checking');
// スタッフー減点のDBへの登録
Route::post('/change_penalty' , [HomeController::class , 'change_penalty'])->name('change_penalty');
// ＮＧリセット
Route::get('/reset_ng' , [HomeController::class , 'reset_ng'])->name('reset_ng');
// チーム情報一覧＆編集
Route::get('/team_index' , [HomeController::class , 'team_index'])->name('team_index');
// チーム情報編集画面
Route::get('/team_edit/{id}' , [HomeController::class , 'team_edit'])->name('team_edit');
// チーム情報編集のDBへの登録
Route::post('/team_update/{id}' , [HomeController::class , 'team_update'])->name('team_update');
// ポイント履歴一覧
Route::get('/point_history' , [HomeController::class , 'point_history'])->name('point_history');
// でバグ用
Route::get('/bug', [HomeController::class, 'bug'])->name('bug');

// Canvas画像圧縮テスト
Route::get('/canvas_test', [HomeController::class, 'canvas_test'])->name('canvas_test');
Route::post('/canvas_upload_test', [HomeController::class, 'canvas_upload_test'])->name('canvas_upload_test');