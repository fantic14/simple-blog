<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    // ─── Index ────────────────────────────────────────────────────────────────

    public function test_anyone_can_list_posts(): void
    {
        Post::factory()->for(User::factory())->count(3)->create();

        $response = $this->getJson('/api/posts');

        $response->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'data' => [['id', 'title', 'content', 'user_id', 'user']],
            ]);
    }

    public function test_index_returns_posts_in_descending_order(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $old  = Post::factory()->for($user)->create(['created_at' => now()->subDay()]);
        $new  = Post::factory()->for($user)->create(['created_at' => now()]);

        $data = $this->getJson('/api/posts')->json('data');

        $this->assertEquals($new->id, $data[0]['id']);
        $this->assertEquals($old->id, $data[1]['id']);
    }

    public function test_index_returns_empty_data_when_no_posts_exist(): void
    {
        $response = $this->getJson('/api/posts');

        $response->assertOk()->assertJson(['data' => []]);
    }

    // ─── Show ─────────────────────────────────────────────────────────────────

    public function test_anyone_can_view_a_single_post(): void
    {
        $post = Post::factory()->for(User::factory())->create();

        $response = $this->getJson("/api/posts/{$post->id}");

        $response->assertOk()
            ->assertJson(['success' => true, 'data' => ['id' => $post->id]]);
    }

    public function test_show_returns_404_for_non_existent_post(): void
    {
        $this->getJson('/api/posts/99999')
            ->assertStatus(404)
            ->assertJson(['success' => false]);
    }

    // ─── Store ────────────────────────────────────────────────────────────────

    public function test_authenticated_user_can_create_a_post(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/posts', [
            'title'   => 'Test Post Title',
            'content' => 'Some body content here.',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.title', 'Test Post Title')
            ->assertJsonPath('data.user_id', $user->id);

        $this->assertDatabaseHas('posts', [
            'title'   => 'Test Post Title',
            'user_id' => $user->id,
        ]);
    }

    public function test_unauthenticated_user_cannot_create_a_post(): void
    {
        $this->postJson('/api/posts', [
            'title'   => 'Ghost Post',
            'content' => 'No auth here.',
        ])->assertStatus(401);
    }

    public function test_store_fails_when_title_is_missing(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $this->actingAs($user)->postJson('/api/posts', ['content' => 'Content only'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    public function test_store_fails_when_title_exceeds_max_length(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $this->actingAs($user)->postJson('/api/posts', [
            'title'   => str_repeat('a', 61), // 61 chars – over the 60-char limit
            'content' => 'Valid content',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    public function test_store_fails_when_content_is_missing(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $this->actingAs($user)->postJson('/api/posts', ['title' => 'Title only'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['content']);
    }

    // ─── Update ───────────────────────────────────────────────────────────────

    public function test_owner_can_update_their_post(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();

        $response = $this->actingAs($user)->patchJson("/api/posts/{$post->id}", [
            'title'   => 'Updated Title',
            'content' => 'Updated content.',
        ]);

        $response->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonPath('data.title', 'Updated Title');

        $this->assertDatabaseHas('posts', ['id' => $post->id, 'title' => 'Updated Title']);
    }

    public function test_non_owner_cannot_update_a_post(): void
    {
        /** @var User $owner */
        $owner = User::factory()->create();
        /** @var User $other */
        $other = User::factory()->create();
        $post  = Post::factory()->for($owner)->create();

        $this->actingAs($other)->patchJson("/api/posts/{$post->id}", [
            'title' => 'Hacked Title',
        ])->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_update_a_post(): void
    {
        $post = Post::factory()->for(User::factory())->create();

        $this->patchJson("/api/posts/{$post->id}", ['title' => 'X'])
            ->assertStatus(401);
    }

    public function test_update_returns_404_for_non_existent_post(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $this->actingAs($user)->patchJson('/api/posts/99999', ['title' => 'X'])
            ->assertStatus(404)
            ->assertJson(['success' => false]);
    }

    // ─── Destroy ──────────────────────────────────────────────────────────────

    public function test_owner_can_delete_their_post(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();

        $response = $this->actingAs($user)->deleteJson("/api/posts/{$post->id}");

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_non_owner_cannot_delete_a_post(): void
    {
        /** @var User $owner */
        $owner = User::factory()->create();
        /** @var User $other */
        $other = User::factory()->create();
        $post  = Post::factory()->for($owner)->create();

        $this->actingAs($other)->deleteJson("/api/posts/{$post->id}")
            ->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_delete_a_post(): void
    {
        $post = Post::factory()->for(User::factory())->create();

        $this->deleteJson("/api/posts/{$post->id}")->assertStatus(401);
    }

    public function test_destroy_returns_404_for_non_existent_post(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $this->actingAs($user)->deleteJson('/api/posts/99999')
            ->assertStatus(404)
            ->assertJson(['success' => false]);
    }
}
