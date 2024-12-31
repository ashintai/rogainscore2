<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    /**
     * ユーザーとのリレーションシップを定義
     */
    public function users()
    {
        return $this->hasMany(User::class , 'category_id' , 'id');
    }
}
