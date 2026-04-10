<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Loan;
use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Loan>
 */
class LoanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $bookIds = null;
        static $memberIds = null;

        $bookIds ??= Book::query()->pluck('id')->all();
        $memberIds ??= Member::query()->pluck('id')->all();

        $status = fake()->randomElement(['borrowed', 'returned', 'overdue', 'lost']);
        $borrowedAt = fake()->dateTimeBetween('-9 months', '-1 days');
        $dueAt = (clone $borrowedAt)->modify('+14 days');
        $returnedAt = $status === 'returned' ? (clone $borrowedAt)->modify('+'.fake()->numberBetween(1, 20).' days') : null;
        $fineAmount = $status === 'returned' || $status === 'overdue'
            ? fake()->randomFloat(2, 0, 50)
            : 0;

        return [
            'book_id' => fake()->randomElement($bookIds),
            'member_id' => fake()->randomElement($memberIds),
            'borrowed_at' => $borrowedAt,
            'due_at' => $dueAt,
            'returned_at' => $returnedAt,
            'status' => $status,
            'fine_amount' => $fineAmount,
        ];
    }
}
