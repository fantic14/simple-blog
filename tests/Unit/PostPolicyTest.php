<?php

namespace Tests\Unit;

use App\Models\Post;
use App\Models\User;
use App\Policies\PostPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostPolicyTest extends TestCase
{
    use RefreshDatabase;

    private PostPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new PostPolicy();
    }

    public function test_owner_can_update_their_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();

        $this->assertTrue($this->policy->update($user, $post));
    }

    public function test_non_owner_cannot_update_a_post(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $post  = Post::factory()->for($owner)->create();

        $this->assertFalse($this->policy->update($other, $post));
    }

    public function test_owner_can_delete_their_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();

        $this->assertTrue($this->policy->delete($user, $post));
    }

    public function test_non_owner_cannot_delete_a_post(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $post  = Post::factory()->for($owner)->create();

        $this->assertFalse($this->policy->delete($other, $post));
    }

    public function test_view_any_always_returns_false(): void
    {
        $user = User::factory()->create();

        $this->assertFalse($this->policy->viewAny($user));
    }

    public function test_create_always_returns_false(): void
    {
        $user = User::factory()->create();

        $this->assertFalse($this->policy->create($user));
    }
}
