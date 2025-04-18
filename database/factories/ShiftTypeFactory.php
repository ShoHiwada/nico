<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ShiftTypeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['日勤', '夜勤', '早番', '遅番']),
            'start_time' => $this->faker->time('H:i', 'now'),
            'end_time' => $this->faker->time('H:i', '+8 hours'),
        ];
    }
}
