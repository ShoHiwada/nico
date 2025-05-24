<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('shifts_requests', function (Blueprint $table) {
            // 外部キー削除
            $table->dropForeign(['shift_type_id']);
    
            // カラム削除
            $table->dropColumn('shift_type_id');
        });
    }
    
    public function down()
    {
        Schema::table('shifts_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('shift_type_id')->nullable();
    
            // 必要であれば外部キー制約も再設定（例：shift_types テーブルへの参照）
            $table->foreign('shift_type_id')->references('id')->on('shift_types')->onDelete('cascade');
        });
    }
    
};
