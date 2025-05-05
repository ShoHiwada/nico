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
            if (!Schema::hasColumn('shifts_requests', 'week_patterns')) {
                $table->json('week_patterns')->after('date')->nullable();
            }
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
