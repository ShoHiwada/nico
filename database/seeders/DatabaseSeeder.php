<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Position;
use App\Models\Branch;
use App\Models\Department;
use App\Models\ShiftType;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // マスタ系を先に作成
        Position::factory()->count(3)->create();
        Branch::factory()->count(2)->create();
        Department::factory()->count(4)->create();
        ShiftType::factory()->count(3)->create();

        // 職員60人を作成（部署・支店・役職はランダムに自動で付与される）
        User::factory()->count(60)->create();

        User::create([
            'name' => '管理者テストユーザー',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'is_admin' => true,
            'position_id' => 1,
            'department_id' => 1,
            'branch_id' => 1,
        ]);
        
        User::create([
            'name' => '田中',
            'email' => 'tanaka@example.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
            'position_id' => 1,
            'department_id' => 1,
            'branch_id' => 1,
        ]);
        
        User::create([
            'name' => '菊池',
            'email' => 'kikuchi@example.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
            'position_id' => 1,
            'department_id' => 1,
            'branch_id' => 1,
        ]);
        
        User::create([
            'name' => '堀之内',
            'email' => 'horinouchi@example.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
            'position_id' => 1,
            'department_id' => 1,
            'branch_id' => 1,
        ]);

        // シフトタイプを作成
        $this->call([
            ShiftTypeSeeder::class,
        ]);

        // シフトデータは別Seederで大量に作成
        $this->call([
            ShiftsTableSeeder::class,
        ]);


    }
}
