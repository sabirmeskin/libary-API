<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Loan;
use App\Models\Member;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LibrarySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::factory()->count(45)->create();

        Author::factory()->count(1200)->create();

        Book::factory()->count(6000)->create();

        Book::query()->chunkById(500, function ($books) use ($categories): void {
            foreach ($books as $book) {
                    $book->categories()->sync(
                        $categories->random(rand(1, 4))->pluck('id')->all()
                    );
            }
        });

        Member::factory()->count(4000)->create();
        Loan::factory()->count(10000)->create();

        Book::query()->chunkById(500, function ($books): void {
            foreach ($books as $book) {
                $activeLoansCount = $book->loans()->whereIn('status', ['borrowed', 'overdue'])->count();
                $available = max(0, $book->total_copies - $activeLoansCount);
                $book->update(['available_copies' => $available]);
            }
        });
    }
}
