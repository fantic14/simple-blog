<?php

namespace Tests\Unit;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_belongs_to_a_user(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();

        $this->assertInstanceOf(User::class, $post->user);
        $this->assertEquals($user->id, $post->user->id);
    }

    public function test_post_has_many_comments(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();
        Comment::factory()->for($post)->for($user)->count(4)->create();

        $this->assertCount(4, $post->comments);
        $this->assertInstanceOf(Comment::class, $post->comments->first());
    }

    public function test_post_has_correct_fillable_fields(): void
    {
        $post = Post::factory()->make([
            'title'   => 'Hello World',
            'content' => 'Post content',
        ]);

        $this->assertEquals('Hello World', $post->title);
        $this->assertEquals('Post content', $post->content);
    }
}
