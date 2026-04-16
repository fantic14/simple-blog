<?php

namespace Tests\Unit;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_many_posts(): void
    {
        $user = User::factory()->create();
        Post::factory()->for($user)->count(3)->create();

        $this->assertCount(3, $user->posts);
        $this->assertInstanceOf(Post::class, $user->posts->first());
    }

    public function test_password_is_hidden_in_json_serialisation(): void
    {
        $user  = User::factory()->create();
        $array = $user->toArray();

        $this->assertArrayNotHasKey('password', $array);
        $this->assertArrayNotHasKey('remember_token', $array);
    }

    public function test_password_is_automatically_hashed_when_cast(): void
    {
        $user = User::factory()->create(['password' => bcrypt('plaintext')]);

        // The stored value must NOT equal the plain-text password.
        $this->assertNotEquals('plaintext', $user->password);
    }

    public function test_user_has_fillable_required_fields(): void
    {
        $user = User::factory()->make([
            'name'  => 'Test Name',
            'email' => 'test@example.com',
        ]);

        $this->assertEquals('Test Name', $user->name);
        $this->assertEquals('test@example.com', $user->email);
    }
}
