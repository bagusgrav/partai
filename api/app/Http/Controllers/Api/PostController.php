<?php

namespace App\Http\Controllers\Api;

use App\Enums\PostStatus;
use App\Enums\PostVisibility;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class PostController extends Controller
{
    public function publicIndex(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
            'cursor' => ['sometimes', 'string'],
        ]);
        $posts = Post::query()
            ->with('user:id,name')
            ->where('visibility', PostVisibility::Public->value)
            ->where('status', PostStatus::Published->value)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->cursorPaginate($validated['per_page'] ?? 20);

        return PostResource::collection($posts)
            ->response()
            ->header('Cache-Control', 'public, max-age=30, s-maxage=30');
    }

    public function memberIndex(Request $request): JsonResponse
    {
        if ($request->user()->role === 'guest') {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $validated = $request->validate([
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
            'cursor' => ['sometimes', 'string'],
        ]);
        $posts = Post::query()
            ->with('user:id,name')
            ->where('visibility', PostVisibility::Members->value)
            ->where('status', PostStatus::Published->value)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->cursorPaginate($validated['per_page'] ?? 20);

        return PostResource::collection($posts)
            ->response()
            ->header('Cache-Control', 'no-store, private')
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }

    public function store(StorePostRequest $request): JsonResponse
    {
        Gate::authorize('create', Post::class);

        $validated = $request->validated();
        $userId = (string) $request->user()->getAuthIdentifier();
        $visibility = $validated['visibility'] ?? PostVisibility::Members->value;

        $existing = Post::withTrashed()->where('request_id', $validated['request_id'])->first();
        if ($existing !== null) {
            return $this->replayResponse($existing, $userId, $validated['body'], $visibility);
        }

        try {
            $post = DB::transaction(fn (): Post => Post::query()->create([
                'user_id' => $userId,
                'body' => $validated['body'],
                'visibility' => $visibility,
                'status' => PostStatus::Published,
                'request_id' => $validated['request_id'],
            ]));
        } catch (QueryException $exception) {
            $existing = Post::withTrashed()->where('request_id', $validated['request_id'])->first();
            if ($existing === null) {
                throw $exception;
            }

            return $this->replayResponse($existing, $userId, $validated['body'], $visibility);
        }

        $post->load('user:id,name');

        $response = (new PostResource($post))->response()->setStatusCode(201);

        return $this->privatePostResponse($response, $post);
    }

    public function update(UpdatePostRequest $request, Post $post): JsonResponse
    {
        Gate::authorize('update', $post);

        $post->fill($request->validated());
        $post->edited_at = now();
        $post->save();
        $post->load('user:id,name');

        return $this->privatePostResponse((new PostResource($post))->response(), $post);
    }

    public function destroy(Request $request, Post $post): JsonResponse
    {
        Gate::authorize('delete', $post);
        $post->delete();

        return response()->json(['status' => 'deleted']);
    }

    private function replayResponse(Post $post, string $userId, string $body, string $visibility): JsonResponse
    {
        if (
            $post->user_id !== $userId
            || $post->body !== $body
            || $post->visibility->value !== $visibility
        ) {
            return response()->json([
                'message' => 'The request_id has already been used for a different post.',
            ], 409);
        }

        $post->load('user:id,name');

        $response = response()->json([
            'status' => 'already_processed',
            'data' => (new PostResource($post))->resolve(),
        ]);

        return $this->privatePostResponse($response, $post);
    }

    private function privatePostResponse(JsonResponse $response, Post $post): JsonResponse
    {
        $response->header('Cache-Control', 'no-store, private');

        if ($post->visibility === PostVisibility::Members) {
            $response->header('X-Robots-Tag', 'noindex, nofollow');
        }

        return $response;
    }
}
