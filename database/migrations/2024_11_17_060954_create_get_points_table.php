<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('get_points', function (Blueprint $table) {
            $table->id();
            $table->integer('team_no')->comment('チーム番号');
            $table->integer('point_no')->comment('ポイント番号');
            $table->integer('checked')->default('0')->comment('0未確認 1確認中 2OK 3NG');
            $table->integer('sns_checked')->default('0')->comment('0未確認 1確認中 2OK 3NG');
            $table->string('photo_filename',255)->nullable()->comment('通過写真ファイル');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('get_points');
    }
};
