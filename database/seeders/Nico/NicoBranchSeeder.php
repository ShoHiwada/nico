<?php

namespace Database\Seeders\Nico;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NicoBranchSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('branches')->insert([
            ['id' => 1, 'name' => '福岡支店', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => '佐賀支店', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => '長崎支店', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}

