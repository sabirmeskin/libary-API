<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate([
            'email' => 'admin@library.local',
        ], [
            'name' => 'Library Admin',
            'password' => Hash::make('ChangeMe@12345'),
        ]);

        $this->call(RolePermissionSeeder::class);
        $this->call(LibrarySeeder::class);
    }
}
