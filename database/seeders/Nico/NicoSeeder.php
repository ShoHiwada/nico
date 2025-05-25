<?php

namespace Database\Seeders\Nico;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NicoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            NicoBranchSeeder::class,
            NicoDepartmentSeeder::class,
            NicoPositionSeeder::class,
            NicoShiftTypeSeeder::class,
            NicoUserSeeder::class,
            NicoBuildingSeeder::class,
            NicoFixedShiftSeeder::class,
        ]);

        // SQLファイルを読み込んで直接実行
        $path = database_path('seed_data/shifts_requests_202506.sql');
        if (file_exists($path)) {
            DB::unprepared(file_get_contents($path));
        }
    }
}
