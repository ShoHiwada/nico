<?php

namespace Database\Seeders\Nico;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NicoPositionSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('positions')->insert([
            ['id' => 1, 'name' => '代表'],
            ['id' => 2, 'name' => '副代表'],
            ['id' => 3, 'name' => '管理者'],
            ['id' => 4, 'name' => '正社員'],
            ['id' => 5, 'name' => '契約社員'],
            ['id' => 6, 'name' => 'アルバイト'],
            ['id' => 7, 'name' => 'パート'],
        ]);
    }
}
