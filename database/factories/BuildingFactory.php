<?php

namespace Database\Factories;

use App\Enums\Buildings\BuildingType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Building>
 */
class BuildingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company().' Building',
            'code' => fake()->unique()->bothify('BLD-###'),
            'description' => fake()->sentence(),
            'address' => fake()->address(),
            'building_type' => fake()->randomElement(BuildingType::cases()),
            'floors' => fake()->numberBetween(1, 20),
            'total_rooms' => fake()->numberBetween(10, 500),
            'construction_year' => fake()->numberBetween(1950, 2024),
            'is_active' => fake()->boolean(85),
            'building_supervisor_id' => User::factory(),
            'contact_info' => [
                'phone' => fake()->phoneNumber(),
                'email' => fake()->email(),
            ],
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
        ];
    }
}
