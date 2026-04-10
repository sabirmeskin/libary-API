<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
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
        $name = fake()->unique()->words(rand(1, 3), true);

        return [
            'name' => ucfirst($name),
            'slug' => str(fake()->slug(3).'-'.fake()->unique()->numberBetween(10, 99999))->lower()->toString(),
            'description' => fake()->optional()->sentence(),
        ];
    }
}
