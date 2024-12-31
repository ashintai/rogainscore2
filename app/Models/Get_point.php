<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Get_point extends Model
{
    use HasFactory;

    protected $table = 'get_points'; // テーブル名を指定

    protected $fillable = [
        'team_no',
        'point_no',
        'photo_filename',
        'checked',
        // 他のカラムを追加
    ];

    // Userテーブルとのリレーションを定義
    public function user()
    {
        return $this->belongsTo(User::class , 'team_no' , 'team_no');
    }

    // Get_pointテーブルとのリレーションを
    public function setPoint()
    {
        return $this->belongsTo(Set_point::class , 'point_no' , 'point_no');
    }


}
