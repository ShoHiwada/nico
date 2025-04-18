<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'is_admin' => false,

            // ↓↓↓ 追加：ランダムな支店・部署・役職を紐付け
            'position_id' => Position::inRandomOrder()->first()->id ?? 1,
            'department_id' => Department::inRandomOrder()->first()->id ?? 1,
            'branch_id' => Branch::inRandomOrder()->first()->id ?? 1,
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
