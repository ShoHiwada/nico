<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shift_requests', function (Blueprint $table) {
            $table->string('month')->after('user_id'); // 例: "2025-05" みたいな文字列想定
        });
    }

    public function down(): void
    {
        Schema::table('shift_requests', function (Blueprint $table) {
            $table->dropColumn('month');
        });
    }
};
