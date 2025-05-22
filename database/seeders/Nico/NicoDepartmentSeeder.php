<?php

namespace Database\Seeders\Nico;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NicoDepartmentSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('departments')->insert([
            ['id' => 1, 'name' => 'GH', 'branch_id' => 1],
            ['id' => 2, 'name' => '訪問看護', 'branch_id' => 1],
            ['id' => 3, 'name' => 'Upto', 'branch_id' => 1],
            ['id' => 4, 'name' => '事務', 'branch_id' => 1],
            ['id' => 5, 'name' => 'グループホーム', 'branch_id' => 2],
            ['id' => 6, 'name' => 'グループホーム', 'branch_id' => 3],
        ]);
    }
}
