<?php

namespace App\Http\Controllers;

use App\Http\Requests\Tweet\StoreTweetRequest;
use App\Models\Tweet;
use Illuminate\Http\Request;

class TweetController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $tweets = Tweet::with(['user', 'likes', 'retweets'])
            ->withCount(['likes', 'retweets'])
            ->latest()
            ->get()
            ->map(function ($tweet) use ($user) {
                $tweet->liked = $user
                    ? $tweet->likes->contains('user_id', $user->id)
                    : false;
                $tweet->retweeted = $user
                    ? $tweet->retweets->contains('user_id', $user->id)
                    : false;

                unset($tweet->likes);
                unset($tweet->retweets);

                return $tweet;
            });

        return response()->json($tweets);
    }

    public function store(StoreTweetRequest $request)
    {
        $tweet = $request->user()->tweets()->create([
            'body' => $request->validated()['body'],
        ]);

        $tweet->load('user', 'likes', 'retweets');
        $tweet->likes_count = $tweet->likes()->count();
        $tweet->retweets_count = $tweet->retweets()->count();
        $tweet->liked = false;
        $tweet->retweeted = false;

        return response()->json($tweet, 201);
    }

    public function toggleLike(Request $request, Tweet $tweet)
    {
        $user = $request->user();

        $existing = $tweet->likes()->where('user_id', $user->id);

        if ($existing->exists()) {
            $existing->delete();
            $liked = false;
        } else {
            $tweet->likes()->create(['user_id' => $user->id]);
            $liked = true;
        }

        $count = $tweet->likes()->count();

        return response()->json([
            'liked'      => $liked,
            'likesCount' => $count,
        ]);
    }

    public function toggleRetweet(Request $request, Tweet $tweet)
    {
        $user = $request->user();

        $existing = $tweet->retweets()->where('user_id', $user->id);

        if ($existing->exists()) {
            $existing->delete();
            $retweeted = false;
        } else {
            $tweet->retweets()->create(['user_id' => $user->id]);
            $retweeted = true;
        }

        $count = $tweet->retweets()->count();

        return response()->json([
            'retweeted'      => $retweeted,
            'retweetsCount'  => $count,
        ]);
    }

    public function update(StoreTweetRequest $request, Tweet $tweet)
    {
        $user = $request->user();

        if ($tweet->user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $tweet->update([
            'body' => $request->validated()['body'],
        ]);

        $tweet->load(['user', 'likes', 'retweets']);
        $tweet->likes_count = $tweet->likes()->count();
        $tweet->retweets_count = $tweet->retweets()->count();
        $tweet->liked = $tweet->likes->contains('user_id', $user->id);
        $tweet->retweeted = $tweet->retweets->contains('user_id', $user->id);

        return response()->json($tweet);
    }

    public function destroy(Request $request, Tweet $tweet)
    {
        $user = $request->user();

        if ($tweet->user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $tweet->delete();

        return response()->json(null, 204);
    }
}
