<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('shifts_requests', function (Blueprint $table) {
            $table->dropForeign(['shift_type_id']);
            $table->dropColumn('shift_type_id');
    
            $table->json('week_patterns')->after('date')->nullable();
        });
    }
        
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shifts_requests', function (Blueprint $table) {
            //
        });
    }
};
