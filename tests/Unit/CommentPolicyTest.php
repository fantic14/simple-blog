<?php

namespace Tests\Unit;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Policies\CommentPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentPolicyTest extends TestCase
{
    use RefreshDatabase;

    private CommentPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new CommentPolicy();
    }

    public function test_comment_author_can_delete_their_comment(): void
    {
        $user    = User::factory()->create();
        $post    = Post::factory()->for(User::factory())->create();
        $comment = Comment::factory()->for($post)->for($user)->create();

        $this->assertTrue($this->policy->delete($user, $comment));
    }

    public function test_post_owner_can_delete_a_comment_on_their_post(): void
    {
        $postOwner     = User::factory()->create();
        $commentAuthor = User::factory()->create();
        $post          = Post::factory()->for($postOwner)->create();
        $comment       = Comment::factory()->for($post)->for($commentAuthor)->create();

        $comment->load('post');

        $this->assertTrue($this->policy->delete($postOwner, $comment));
    }

    public function test_unrelated_user_cannot_delete_a_comment(): void
    {
        $postOwner     = User::factory()->create();
        $commentAuthor = User::factory()->create();
        $stranger      = User::factory()->create();
        $post          = Post::factory()->for($postOwner)->create();
        $comment       = Comment::factory()->for($post)->for($commentAuthor)->create();

        $comment->load('post');

        $this->assertFalse($this->policy->delete($stranger, $comment));
    }

    public function test_admin_can_delete_any_comment(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $commentAuthor = User::factory()->create();
        $post = Post::factory()->for(User::factory())->create();
        $comment = Comment::factory()->for($post)->for($commentAuthor)->create();

        $this->assertTrue($admin->can('delete', $comment));
    }

    public function test_view_any_always_returns_false(): void
    {
        $user = User::factory()->create();

        $this->assertFalse($this->policy->viewAny($user));
    }

    public function test_update_always_returns_false(): void
    {
        $user    = User::factory()->create();
        $post    = Post::factory()->for(User::factory())->create();
        $comment = Comment::factory()->for($post)->for($user)->create();

        $this->assertFalse($this->policy->update($user, $comment));
    }
}
