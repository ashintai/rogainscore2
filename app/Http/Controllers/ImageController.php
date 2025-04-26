<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Get_point;


class ImageController extends Controller
{
    // 画像のURLを生成するコントローラ
    // S3に保存されている画像のURLを生成するコントローラです。
    public function getImageUrl(Request $request)
    {
        $key = $request->input('key');
        $url = Storage::disk('s3')->url($key);

        return response()->json(['url' => $url]);
    }

    // 通過したポイントの写真をUPする
    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,gif',
        ]);

        if ($request->hasFile('image')) {
            // アップロードされたファイルを取得            
            $file = $request->file('image');
            // アップロードされた元のファイルの拡張子を取得
            $originalExt = $file->getClientOriginalExtension();
            // ファイル名を生成だよよ
            $teamno = Auth::user()->team_no;
            $point_no = $request->input('point');
            $filename = "get_" . $point_no . "_" . $teamno . "." . $originalExt;
        
            $path = Storage::disk('s3')->putFileAs('/', $file ,$filename);
            $url = Storage::disk('s3')->url($path );

            
            \Log::info('Uploaded file path: ' . $path);
            \Log::info('Generated URL: ' . $url); 
            \Log::info('Filename: ' . $filename);
            
            // Get_point のテーブルに追記
            Get_point::create([
                'team_no' => $teamno,
                'point_no' => $point_no,
                'filename' => $filename,
                'photo_filename' => $url,
                
            ]);
            
            
            return back()->with('success', '画像が正常にアップロードされました')->with('image_url', $url)->with('pointno' , $point_no);
        }

        return back()->withErrors(['image' => '画像のアップロードに失敗しました']);
    }
}

