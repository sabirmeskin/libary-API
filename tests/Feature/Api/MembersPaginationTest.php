<?php

namespace Tests\Feature\Api;

use App\Models\Member;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MembersPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_members_pagination_with_active_filter_works(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        Member::factory()->count(30)->create(['is_active' => true]);
        Member::factory()->count(15)->create(['is_active' => false]);

        Sanctum::actingAs($admin, ['*']);

        $response = $this->getJson('/api/v1/members?per_page=20&is_active=true&page=2');

        $response
            ->assertOk()
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonPath('meta.per_page', 20)
            ->assertJsonPath('meta.total', 30);

        $data = $response->json('data');

        $this->assertCount(10, $data);

        foreach ($data as $member) {
            $this->assertTrue((bool) $member['is_active']);
        }
    }
}
