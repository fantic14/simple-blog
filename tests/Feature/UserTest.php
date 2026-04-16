<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_anyone_can_fetch_a_user_by_id(): void
    {
        $user = User::factory()->create(['name' => 'Alice']);

        $response = $this->getJson("/api/users/{$user->id}");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data'    => ['id' => $user->id, 'name' => 'Alice'],
            ]);
    }

    public function test_response_does_not_expose_password(): void
    {
        $user = User::factory()->create();

        $data = $this->getJson("/api/users/{$user->id}")->json('data');

        $this->assertArrayNotHasKey('password', $data);
        $this->assertArrayNotHasKey('remember_token', $data);
    }

    public function test_show_returns_404_for_non_existent_user(): void
    {
        $this->getJson('/api/users/99999')
            ->assertStatus(404)
            ->assertJson(['success' => false, 'message' => 'User not found']);
    }
}
