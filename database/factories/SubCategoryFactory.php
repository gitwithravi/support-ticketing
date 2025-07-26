<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SubCategory>
 */
class SubCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'color' => fake()->hexColor(),
            'icon' => fake()->randomElement(['heroicon-o-computer-desktop', 'heroicon-o-device-phone-mobile', 'heroicon-o-printer']),
            'is_active' => fake()->boolean(80),
            'sort_order' => fake()->numberBetween(1, 100),
        ];
    }
}