<?php

namespace Database\Seeders\Nico;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

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
        ]);
    }
}
