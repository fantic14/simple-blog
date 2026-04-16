<?php

namespace Tests\Unit;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_comment_belongs_to_a_user(): void
    {
        $user    = User::factory()->create();
        $post    = Post::factory()->for($user)->create();
        $comment = Comment::factory()->for($post)->for($user)->create();

        $this->assertInstanceOf(User::class, $comment->user);
        $this->assertEquals($user->id, $comment->user->id);
    }

    public function test_comment_belongs_to_a_post(): void
    {
        $user    = User::factory()->create();
        $post    = Post::factory()->for($user)->create();
        $comment = Comment::factory()->for($post)->for($user)->create();

        $this->assertInstanceOf(Post::class, $comment->post);
        $this->assertEquals($post->id, $comment->post->id);
    }

    public function test_comment_has_correct_fillable_fields(): void
    {
        $comment = Comment::factory()->make(['comment' => 'A test comment']);

        $this->assertEquals('A test comment', $comment->comment);
    }
}
