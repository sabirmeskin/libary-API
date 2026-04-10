<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'authors.view', 'authors.create', 'authors.update', 'authors.delete',
            'categories.view', 'categories.create', 'categories.update', 'categories.delete',
            'books.view', 'books.create', 'books.update', 'books.delete',
            'members.view', 'members.create', 'members.update', 'members.delete',
            'loans.view', 'loans.create', 'loans.update', 'loans.delete',
            'search.global',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $admin = Role::findOrCreate('admin', 'web');
        $librarian = Role::findOrCreate('librarian', 'web');
        $member = Role::findOrCreate('member', 'web');

        $admin->syncPermissions($permissions);

        $librarian->syncPermissions([
            'authors.view', 'authors.create', 'authors.update',
            'categories.view', 'categories.create', 'categories.update',
            'books.view', 'books.create', 'books.update',
            'members.view', 'members.create', 'members.update',
            'loans.view', 'loans.create', 'loans.update',
            'search.global',
        ]);

        $member->syncPermissions([
            'authors.view',
            'categories.view',
            'books.view',
            'members.view',
            'loans.view',
            'search.global',
        ]);

        $adminUser = User::query()->where('email', 'admin@library.local')->first();

        if ($adminUser) {
            $adminUser->syncRoles(['admin']);
        }
    }
}
