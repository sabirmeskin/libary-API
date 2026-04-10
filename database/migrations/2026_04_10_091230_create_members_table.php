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
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('membership_no', 30)->unique();
            $table->string('name')->index();
            $table->string('email')->unique();
            $table->string('phone', 30)->nullable()->index();
            $table->string('address')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->date('joined_at')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
