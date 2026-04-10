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
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->dateTime('borrowed_at')->index();
            $table->dateTime('due_at')->index();
            $table->dateTime('returned_at')->nullable()->index();
            $table->enum('status', ['borrowed', 'returned', 'overdue', 'lost'])->default('borrowed')->index();
            $table->decimal('fine_amount', 10, 2)->default(0);
            $table->timestamps();

            $table->index(['member_id', 'status']);
            $table->index(['book_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
