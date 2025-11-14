<?php

namespace Tests\Feature;

use App\Models\Tweet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TweetTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_tweet(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/tweets', ['body' => 'Hello world'])
            ->assertCreated()
            ->assertJsonFragment(['body' => 'Hello world']);
    }

    public function test_guest_cannot_create_tweet(): void
    {
        $this->postJson('/api/tweets', ['body' => 'Hello'])
            ->assertStatus(401);
    }

    public function test_user_can_list_tweets(): void
    {
        $user = User::factory()->create();
        Tweet::factory()->count(3)->for($user)->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/tweets')
            ->assertOk()
            ->assertJsonCount(3);
    }

    public function test_user_can_like_and_unlike_tweet(): void
    {
        $user = User::factory()->create();
        $tweet = Tweet::factory()->for($user)->create();

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/tweets/{$tweet->id}/like")
            ->assertOk()
            ->assertJsonFragment(['liked' => true]);

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/tweets/{$tweet->id}/like")
            ->assertOk()
            ->assertJsonFragment(['liked' => false]);
    }
}
