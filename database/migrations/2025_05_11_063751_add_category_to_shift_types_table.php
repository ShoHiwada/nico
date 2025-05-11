<?php

// database/migrations/xxxx_xx_xx_xxxxxx_add_category_to_shift_types_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('shift_types', function (Blueprint $table) {
            $table->string('category')->default('day')->after('name'); // day or night
        });
    }

    public function down(): void
    {
        Schema::table('shift_types', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
};

