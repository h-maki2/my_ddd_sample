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
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->uuid('user_id')->primary();
            $table->string('name', 50);
            $table->text('self_introduction_text')->nullable();
            $table->timestamps();
            // 外部キー制約
            $table->foreign('user_id')->references('id')->on('users')
                ->onDelete('cascade'); // 親レコード削除時に自動削除
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
