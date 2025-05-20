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
        Schema::table('shift_nights', function (Blueprint $table) {
            $table->unsignedBigInteger('shift_type_id')->nullable()->after('building_id');
            // 外部キー制約を使うなら以下も追加
            // $table->foreign('shift_type_id')->references('id')->on('shift_types')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shift_nights', function (Blueprint $table) {
            $table->dropColumn('shift_type_id');
        });
    }
};
