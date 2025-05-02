<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shifts_fixed', function (Blueprint $table) {
            if (!Schema::hasColumn('shifts_fixed', 'note')) {
                $table->string('note', 255)->nullable()->after('end_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('shifts_fixed', function (Blueprint $table) {
            $table->dropColumn('note');
        });
    }
};

