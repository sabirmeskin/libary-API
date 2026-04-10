<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('isbn', 20)->unique();
            $table->string('title')->index();
            $table->string('subtitle')->nullable();
            $table->text('summary')->nullable();
            $table->unsignedSmallInteger('published_year')->nullable()->index();
            $table->unsignedInteger('total_copies')->default(1);
            $table->unsignedInteger('available_copies')->default(1)->index();
            $table->string('shelf_location', 50)->nullable()->index();
            $table->timestamps();

            $table->index(['author_id', 'published_year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
