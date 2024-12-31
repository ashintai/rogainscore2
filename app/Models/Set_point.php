<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Set_point extends Model
{
    use HasFactory;

    // リレーションの定義
    public function getPoints()
    {
        return $this->hasMany(Get_point::class, 'point_no', 'point_no');
    }
}
