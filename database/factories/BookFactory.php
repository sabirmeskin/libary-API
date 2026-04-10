<?php

namespace Database\Factories;

use App\Models\Author;
use App\Models\Book;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Book>
 */
class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $authorIds = null;

        $authorIds ??= Author::query()->pluck('id')->all();

        $total = fake()->numberBetween(2, 30);

        return [
            'author_id' => fake()->randomElement($authorIds),
            'isbn' => fake()->unique()->isbn13(),
            'title' => fake()->sentence(rand(2, 6)),
            'subtitle' => fake()->optional()->sentence(rand(2, 5)),
            'summary' => fake()->optional()->paragraphs(2, true),
            'published_year' => fake()->numberBetween(1950, (int) now()->format('Y')),
            'total_copies' => $total,
            'available_copies' => fake()->numberBetween(1, $total),
            'shelf_location' => sprintf('%s-%03d', fake()->randomLetter(), fake()->numberBetween(1, 400)),
        ];
    }
}
