<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    // ─── Index (GET /api/posts/{id}/comments) ──────────────────────────────

    public function test_anyone_can_list_comments_for_a_post(): void
    {
        $post = Post::factory()->for(User::factory())->create();
        Comment::factory()->for($post)->for(User::factory())->count(3)->create();

        $response = $this->getJson("/api/posts/{$post->id}/comments");

        $response->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'data' => [['id', 'comment', 'post_id', 'user_id', 'user']],
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    public function test_index_returns_empty_data_for_post_with_no_comments(): void
    {
        $post = Post::factory()->for(User::factory())->create();

        $this->getJson("/api/posts/{$post->id}/comments")
            ->assertOk()
            ->assertJson(['data' => []]);
    }

    public function test_index_returns_comments_in_descending_order(): void
    {
        $post = Post::factory()->for(User::factory())->create();
        /** @var User $user */
        $user = User::factory()->create();

        $old = Comment::factory()->for($post)->for($user)->create(['created_at' => now()->subHour()]);
        $new = Comment::factory()->for($post)->for($user)->create(['created_at' => now()]);

        $data = $this->getJson("/api/posts/{$post->id}/comments")->json('data');

        $this->assertEquals($new->id, $data[0]['id']);
        $this->assertEquals($old->id, $data[1]['id']);
    }

    // ─── Store (POST /api/posts/{id}/comments) ─────────────────────────────

    public function test_guest_can_post_a_comment(): void
    {
        $post = Post::factory()->for(User::factory())->create();

        $response = $this->postJson("/api/posts/{$post->id}/comments", [
            'comment' => 'Great article!',
        ]);

        $response->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonPath('data.comment', 'Great article!');

        $this->assertDatabaseHas('comments', [
            'comment' => 'Great article!',
            'post_id' => $post->id,
        ]);
    }

    public function test_authenticated_user_comment_stores_their_user_id(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $post = Post::factory()->for(User::factory())->create();

        $response = $this->actingAs($user)->postJson("/api/posts/{$post->id}/comments", [
            'comment' => 'Authenticated comment',
        ]);

        $response->assertOk()->assertJsonPath('data.user_id', $user->id);

        $this->assertDatabaseHas('comments', [
            'comment' => 'Authenticated comment',
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);
    }

    public function test_store_fails_when_comment_is_missing(): void
    {
        $post = Post::factory()->for(User::factory())->create();

        $this->postJson("/api/posts/{$post->id}/comments", [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['comment']);
    }

    public function test_store_fails_when_comment_exceeds_max_length(): void
    {
        $post = Post::factory()->for(User::factory())->create();

        $this->postJson("/api/posts/{$post->id}/comments", [
            'comment' => str_repeat('x', 1001), // 1001 chars – over the 1000-char limit
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['comment']);
    }

    // ─── Destroy (DELETE /api/comments/{id}) ───────────────────────────────

    public function test_comment_author_can_delete_their_comment(): void
    {
        /** @var User $user */
        $user    = User::factory()->create();
        $post    = Post::factory()->for(User::factory())->create();
        $comment = Comment::factory()->for($post)->for($user)->create();

        $response = $this->actingAs($user)->deleteJson("/api/comments/{$comment->id}");

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    public function test_post_owner_can_delete_a_comment_on_their_post(): void
    {
        /** @var User $postOwner */
        $postOwner     = User::factory()->create();
        /** @var User $commentAuthor */
        $commentAuthor = User::factory()->create();
        $post          = Post::factory()->for($postOwner)->create();
        $comment       = Comment::factory()->for($post)->for($commentAuthor)->create();

        $response = $this->actingAs($postOwner)->deleteJson("/api/comments/{$comment->id}");

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    public function test_unrelated_user_cannot_delete_a_comment(): void
    {
        /** @var User $postOwner */
        $postOwner     = User::factory()->create();
        /** @var User $commentAuthor */
        $commentAuthor = User::factory()->create();
        /** @var User $stranger */
        $stranger      = User::factory()->create();
        $post          = Post::factory()->for($postOwner)->create();
        $comment       = Comment::factory()->for($post)->for($commentAuthor)->create();

        $this->actingAs($stranger)->deleteJson("/api/comments/{$comment->id}")
            ->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_delete_a_comment(): void
    {
        $comment = Comment::factory()
            ->for(Post::factory()->for(User::factory()))
            ->for(User::factory())
            ->create();

        $this->deleteJson("/api/comments/{$comment->id}")->assertStatus(401);
    }

    public function test_destroy_returns_404_for_non_existent_comment(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $this->actingAs($user)->deleteJson('/api/comments/99999')
            ->assertStatus(404)
            ->assertJson(['success' => false]);
    }
}
