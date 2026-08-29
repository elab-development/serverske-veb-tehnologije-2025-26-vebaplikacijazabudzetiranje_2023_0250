<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'description' => fake()->sentence(3),
            'category' => fake()->randomElement(['Hrana', 'Piće', 'Prevoz', 'Smeštaj', 'Zabava', 'Ostalo']),
            'amount' => fake()->randomFloat(2, 10, 5000),
            'paid_at' => fake()->dateTimeBetween('-2 months', 'now')->format('Y-m-d'),
            'group_id' => \App\Models\Group::factory(),
            'paid_by' => \App\Models\User::factory(),
        ];
    }
}