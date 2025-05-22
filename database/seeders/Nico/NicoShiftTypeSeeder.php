<?php

namespace Database\Seeders\Nico;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NicoShiftTypeSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('shift_types')->insert([
            [
                'id' => 1,
                'name' => '社員1',
                'category' => 'day',
                'start_time' => '08:30:00',
                'end_time' => '17:30:00',
                'note' => '時間外集計なし',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => '社員2',
                'category' => 'day',
                'start_time' => '09:30:00',
                'end_time' => '18:30:00',
                'note' => '時間外集計なし',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => '社員3',
                'category' => 'day',
                'start_time' => '09:00:00',
                'end_time' => '18:00:00',
                'note' => '時間外集計なし',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'name' => '社員4',
                'category' => 'day',
                'start_time' => '10:00:00',
                'end_time' => '19:00:00',
                'note' => '時間外集計なし',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'name' => 'パート2',
                'category' => 'day',
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'note' => '集計なし',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 6,
                'name' => 'パート3',
                'category' => 'day',
                'start_time' => '09:30:00',
                'end_time' => '16:00:00',
                'note' => '集計なし',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 7,
                'name' => '日勤',
                'category' => 'day',
                'start_time' => '09:30:00',
                'end_time' => '18:30:00',
                'note' => '時間外集計なし',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 8,
                'name' => '夜勤①（18:00-8:00）',
                'category' => 'night',
                'start_time' => '18:00:00',
                'end_time' => '08:00:00',
                'note' => '時間外集計なし',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 9,
                'name' => '夜勤②（22:00-8:00）',
                'category' => 'night',
                'start_time' => '22:00:00',
                'end_time' => '08:00:00',
                'note' => '時間外集計なし',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 10,
                'name' => '夜勤',
                'category' => 'night',
                'start_time' => null,
                'end_time' => null,
                'note' => '時間外集計なし',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 11,
                'name' => '晴雨庵',
                'category' => 'day',
                'start_time' => null,
                'end_time' => null,
                'note' => '集計なし',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 12,
                'name' => '特遅',
                'category' => 'day',
                'start_time' => null,
                'end_time' => null,
                'note' => '時間外集計なし',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 13,
                'name' => 'フレックス社員',
                'category' => 'day',
                'start_time' => null,
                'end_time' => null,
                'note' => 'フレックス枠',
                'created_at' => now(),
                'updated_at' => now(),
            ],            
        ]);
    }
}