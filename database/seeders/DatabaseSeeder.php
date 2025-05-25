<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\Nico\NicoSeeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            NicoSeeder::class,
        ]);
    }
}
