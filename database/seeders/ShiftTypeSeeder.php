<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShiftTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['id' => 1, 'name' => '早番',         'category' => 'day',   'start_time' => '07:00', 'end_time' => '16:00'],
            ['id' => 2, 'name' => '日勤',         'category' => 'day',   'start_time' => '09:00', 'end_time' => '18:00'],
            ['id' => 3, 'name' => '遅番',         'category' => 'day',   'start_time' => '11:00', 'end_time' => '20:00'],
            ['id' => 4, 'name' => '夜勤（18-8）', 'category' => 'night', 'start_time' => '18:00', 'end_time' => '08:00'],
            ['id' => 5, 'name' => '夜勤（22-8）', 'category' => 'night', 'start_time' => '22:00', 'end_time' => '08:00'],
        ];

        if (app()->environment('local')) {
            // 開発環境では初期化してinsert
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('shift_types')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            DB::table('shift_types')->insert($types);
        } else {
            // 本番環境では上書き更新
            foreach ($types as $type) {
                DB::table('shift_types')->updateOrInsert(['id' => $type['id']], $type);
            }
        }
    }
}
