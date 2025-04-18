<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Shift;
use App\Models\ShiftType;

class ShiftsTableSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $shiftTypes = ShiftType::pluck('id')->all();

        foreach ($users as $user) {
            $shiftCount = rand(30, 100);
            for ($i = 0; $i < $shiftCount; $i++) {
                Shift::create([
                    'user_id' => $user->id,
                    'date' => now()->addDays(rand(0, 60))->toDateString(),
                    'shift_type_id' => $shiftTypes[array_rand($shiftTypes)],
                    'status' => 'confirmed',
                ]);
            }
        }

        // ✅ 固定ユーザーに適当なシフトを追加
        $fixedEmails = [
            'admin@example.com',
            'tanaka@example.com',
            'kikuchi@example.com',
            'horinouchi@example.com',
        ];

        foreach ($fixedEmails as $email) {
            $user = User::where('email', $email)->first();
            if ($user) {
                for ($i = 0; $i < 20; $i++) {
                    Shift::create([
                        'user_id' => $user->id,
                        'date' => now()->addDays(rand(0, 30))->toDateString(),
                        'shift_type_id' => $shiftTypes[array_rand($shiftTypes)],
                        'status' => 'confirmed',
                    ]);
                }
            }
        }
    }
}
