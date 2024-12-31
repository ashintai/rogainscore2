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
        Schema::create('set_points', function (Blueprint $table) {
            $table->id();
            $table->integer('point_no')->comment('ポイント番号');
            $table->string('point_name',255)->comment('ポイント名');
            $table->integer('score')->comment('得点');
            $table->integer('sns_score')->comment('SNS得点');
            $table->string('photo_filename',255)->nullable()->comment('設定写真ファイル名');
            $table->string('gps_lati',255)->nullable()->comment('位置緯度');
            $table->string('gps_long',255)->nullable()->comment('位置経度');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('set_points');
    }
};
