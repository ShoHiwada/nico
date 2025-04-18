<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // （管理者操作ログ）
    public function up(): void
    {
        Schema::create('admin_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_user_id')->constrained('users')->onDelete('cascade');
            $table->string('action_type'); // 例: shift_confirm, notification_sent
            $table->string('target_type'); // 例: shift, user, schedule
            $table->unsignedBigInteger('target_id'); // 対象レコードID
            $table->text('description')->nullable(); // 操作内容の説明）
            $table->timestamp('created_at')->useCurrent(); // 操作日時
        });        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_logs');
    }
};
