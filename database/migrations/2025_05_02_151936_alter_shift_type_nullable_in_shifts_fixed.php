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
        Schema::table('shifts_fixed', function (Blueprint $table) {
            $table->unsignedBigInteger('shift_type_id')->nullable()->change();
        });
    }
    
    public function down()
    {
        Schema::table('shifts_fixed', function (Blueprint $table) {
            $table->unsignedBigInteger('shift_type_id')->nullable(false)->change();
        });
    }
    
};
