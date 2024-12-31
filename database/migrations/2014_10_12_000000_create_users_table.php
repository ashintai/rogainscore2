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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name',255)->comment('チーム名');
            $table->string('email',255)->unique()->comment('e-mail');
            $table->string('password')->comment('Password');
            $table->integer('role')->default('0')->comment('0参加者 1管理者 2スタッフ');
            $table->integer('team_no')->nullable()->comment('チーム番号');
            $table->integer('category_id')->nullable()->comment('カテゴリid');
            $table->integer('member_num')->nullable()->comment('メンバー数');
            $table->integer('penalty')->default('0')->comment('減点');
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken()->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
