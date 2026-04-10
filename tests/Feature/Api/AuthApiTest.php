<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_token_and_rbac_payload(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->create([
            'email' => 'admin@library.local',
            'password' => 'ChangeMe@12345',
        ]);
        $user->assignRole('admin');

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@library.local',
            'password' => 'ChangeMe@12345',
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'message',
                'token',
                'token_type',
                'user',
                'roles',
                'permissions',
            ]);
    }

    public function test_me_requires_authentication_and_uses_standard_error_shape(): void
    {
        $response = $this->getJson('/api/v1/auth/me');

        $response
            ->assertUnauthorized()
            ->assertJsonStructure([
                'message',
                'error' => ['type', 'code'],
                'path',
                'timestamp',
            ])
            ->assertJsonPath('error.code', 401);
    }
}
