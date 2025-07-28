<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'color' => fake()->hexColor(),
            'icon' => fake()->randomElement(['heroicon-o-folder', 'heroicon-o-computer-desktop', 'heroicon-o-wrench-screwdriver']),
            'is_active' => fake()->boolean(85),
            'sort_order' => fake()->numberBetween(1, 100),
            'category_supervisor_id' => User::factory(),
        ];
    }
}
