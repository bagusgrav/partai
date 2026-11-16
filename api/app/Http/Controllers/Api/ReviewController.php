<?php

namespace App\Http\Controllers\Api;

use App\Events\ReputationActivity;
use App\Http\Controllers\Controller;
use App\Services\ReputationService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'limit' => ['sometimes', 'integer', 'min:1', 'max:20'],
        ]);
        $userId = $request->user()->getAuthIdentifier();

        $items = DB::table('items')
            ->leftJoin('user_item_states', function ($join) use ($userId) {
                $join->on('items.id', '=', 'user_item_states.item_id')
                    ->where('user_item_states.user_id', '=', $userId);
            })
            ->where(function ($query) {
                $query->whereRaw("json_extract(items.payload, '$.type') IS NULL")
                    ->orWhereRaw("json_extract(items.payload, '$.type') != ?", ['essay']);
            })
            ->where(function ($query) {
                $query->whereNull('user_item_states.next_review')
                    ->orWhere('user_item_states.next_review', '<=', now());
            })
            ->select([
                'items.id',
                'items.domain',
                'items.level',
                'items.payload',
                'user_item_states.reps',
                'user_item_states.interval_d',
                'user_item_states.ease',
                'user_item_states.next_review',
            ])
            ->limit($validated['limit'] ?? 5)
            ->get()
            ->map(function ($item) {
                $item->payload = json_decode($item->payload, true, flags: JSON_THROW_ON_ERROR);

                return $item;
            });

        return response()->json(['items' => $items]);
    }

    public function answer(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'item_id' => ['required', 'string', 'exists:items,id'],
            'correct' => ['required', 'boolean'],
            'request_id' => ['required', 'uuid'],
        ]);
        $userId = $request->user()->getAuthIdentifier();

        $existingReview = DB::table('reviews')
            ->where('request_id', $validated['request_id'])
            ->first();

        if ($existingReview !== null) {
            return $this->duplicateResponse($existingReview, $userId, $validated['item_id']);
        }

        $correct = filter_var($validated['correct'], FILTER_VALIDATE_BOOLEAN);
        $quality = $correct ? 4 : 1;
        $reviewedAt = now();

        try {
            $nextReview = DB::transaction(function () use ($validated, $userId, $correct, $quality, $reviewedAt) {
                $state = DB::table('user_item_states')
                    ->where('user_id', $userId)
                    ->where('item_id', $validated['item_id'])
                    ->first();

                $reps = (int) ($state->reps ?? 0);
                $interval = (int) ($state->interval_d ?? 0);
                $ease = (float) ($state->ease ?? 2.5);

                $newEase = max(
                    1.3,
                    $ease + 0.1 - (5 - $quality) * (0.08 + (5 - $quality) * 0.02),
                );

                if ($quality < 3) {
                    $reps = 0;
                    $interval = 1;
                } elseif ($reps === 0) {
                    $reps = 1;
                    $interval = 1;
                } elseif ($reps === 1) {
                    $reps = 2;
                    $interval = 6;
                } else {
                    $interval = (int) round($interval * $ease);
                    $reps++;
                }

                $nextReview = $reviewedAt->copy()->addDays($interval);
                $stateValues = [
                    'reps' => $reps,
                    'interval_d' => $interval,
                    'ease' => $newEase,
                    'next_review' => $nextReview,
                    'last_review' => $reviewedAt,
                    'updated_at' => $reviewedAt,
                    'created_at' => $state->created_at ?? $reviewedAt,
                ];

                DB::table('user_item_states')->updateOrInsert(
                    ['user_id' => $userId, 'item_id' => $validated['item_id']],
                    $stateValues,
                );

                DB::table('reviews')->insert([
                    'user_id' => $userId,
                    'item_id' => $validated['item_id'],
                    'correct' => $correct,
                    'quality' => $quality,
                    'request_id' => $validated['request_id'],
                    'reviewed_at' => $reviewedAt,
                    'created_at' => $reviewedAt,
                    'updated_at' => $reviewedAt,
                ]);

                if ($correct) {
                    event(new ReputationActivity(
                        (string) $userId,
                        ReputationService::QUIZ_CORRECT,
                        10,
                        'Menjawab kuis dengan benar.',
                        $validated['request_id'],
                    ));
                }

                return $nextReview;
            });
        } catch (QueryException $exception) {
            $existingReview = DB::table('reviews')
                ->where('request_id', $validated['request_id'])
                ->first();

            if ($existingReview === null) {
                throw $exception;
            }

            return $this->duplicateResponse($existingReview, $userId, $validated['item_id']);
        }

        return response()->json([
            'status' => 'processed',
            'data' => ['next_review_at' => $nextReview->toISOString()],
        ]);
    }

    private function duplicateResponse(object $review, string $userId, string $itemId): JsonResponse
    {
        if ($review->user_id !== $userId || $review->item_id !== $itemId) {
            return response()->json([
                'message' => 'The request_id has already been used for a different review.',
            ], 409);
        }

        $state = DB::table('user_item_states')
            ->where('user_id', $userId)
            ->where('item_id', $itemId)
            ->first();

        return response()->json([
            'status' => 'already_processed',
            'data' => ['state' => $state],
        ]);
    }
}
