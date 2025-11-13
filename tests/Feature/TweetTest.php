<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TweetTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_user_can_create_tweet()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/tweets', ['body' => 'Hello world'])
            ->assertCreated()
            ->assertJsonFragment(['body' => 'Hello world']);
    }
}
