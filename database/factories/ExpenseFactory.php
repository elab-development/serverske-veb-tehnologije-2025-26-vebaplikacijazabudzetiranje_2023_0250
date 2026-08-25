<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'description' => fake()->sentence(3),
            'amount' => fake()->randomFloat(2, 10, 5000),
            'group_id' => \App\Models\Group::factory(),
            'paid_by' => \App\Models\User::factory(),
        ];
    }
}