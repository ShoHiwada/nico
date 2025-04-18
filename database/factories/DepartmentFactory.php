<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Branch;

class DepartmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'branch_id' => Branch::inRandomOrder()->first()->id ?? 1,
        ];
    }
}
