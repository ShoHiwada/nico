<?php

namespace Database\Seeders\Nico;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NicoBuildingSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('buildings')->insert([
            ['branch_id' => 1, 'name' => 'アーバンスカイ', 'created_at' => now(), 'updated_at' => now()],
            ['branch_id' => 1, 'name' => 'パウゼ福大前', 'created_at' => now(), 'updated_at' => now()],
            ['branch_id' => 1, 'name' => 'CSハイツ', 'created_at' => now(), 'updated_at' => now()],
            ['branch_id' => 1, 'name' => 'ローレル片江', 'created_at' => now(), 'updated_at' => now()],
            ['branch_id' => 1, 'name' => 'マルワコーポ福大前', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
