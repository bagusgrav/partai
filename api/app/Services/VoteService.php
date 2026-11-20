<?php

namespace App\Services;

use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class VoteService
{
    /**
     * @return array{votes: Collection<int, array<string, mixed>>}
     */
    public function active(string $userId): array
    {
        $now = now();
        $votes = DB::table('votes')
            ->where('status', 'active')
            ->where('starts_at', '<=', $now)
            ->where('ends_at', '>=', $now)
            ->orderBy('starts_at')
            ->get(['id', 'title', 'description', 'starts_at', 'ends_at']);

        $submittedVoteIds = DB::table('vote_submissions')
            ->whereIn('vote_id', $votes->pluck('id'))
            ->where('user_id', $userId)
            ->pluck('vote_id')
            ->map(fn (int|string $voteId): int => (int) $voteId)
            ->flip();

        $optionsByVote = DB::table('vote_options')
            ->whereIn('vote_id', $votes->pluck('id'))
            ->orderBy('id')
            ->get(['id', 'vote_id', 'option_text', 'votes_count'])
            ->groupBy('vote_id');

        return [
            'votes' => $votes->map(function (object $vote) use ($optionsByVote, $submittedVoteIds): array {
                $hasVoted = $submittedVoteIds->has((int) $vote->id);

                return [
                    'id' => (int) $vote->id,
                    'title' => $vote->title,
                    'description' => $vote->description,
                    'starts_at' => $vote->starts_at,
                    'ends_at' => $vote->ends_at,
                    'has_voted' => $hasVoted,
                    'vote_options' => $optionsByVote
                        ->get($vote->id, collect())
                        ->map(function (object $option) use ($hasVoted): array {
                            $result = [
                                'id' => (int) $option->id,
                                'option_text' => $option->option_text,
                            ];

                            if ($hasVoted) {
                                $result['votes_count'] = (int) $option->votes_count;
                            }

                            return $result;
                        })
                        ->values(),
                ];
            })->values(),
        ];
    }

    /**
     * @param  array{vote_id: int, vote_option_id: int, request_id: string}  $validated
     */
    public function submit(array $validated, string $userId): JsonResponse
    {
        $existingRequest = DB::table('vote_submissions')
            ->where('request_id', $validated['request_id'])
            ->first();

        if ($existingRequest !== null) {
            return $this->duplicateSubmissionResponse($existingRequest, $validated, $userId);
        }

        $timestamp = now();

        try {
            return DB::transaction(function () use ($validated, $userId, $timestamp): JsonResponse {
                $existingRequest = DB::table('vote_submissions')
                    ->where('request_id', $validated['request_id'])
                    ->first();

                if ($existingRequest !== null) {
                    return $this->duplicateSubmissionResponse($existingRequest, $validated, $userId);
                }

                $existingVote = DB::table('vote_submissions')
                    ->where('vote_id', $validated['vote_id'])
                    ->where('user_id', $userId)
                    ->first();

                if ($existingVote !== null) {
                    return $this->alreadyVotedResponse();
                }

                $activeVote = DB::table('votes')
                    ->where('id', $validated['vote_id'])
                    ->where('status', 'active')
                    ->where('starts_at', '<=', $timestamp)
                    ->where('ends_at', '>=', $timestamp)
                    ->exists();

                if (! $activeVote) {
                    return response()->json([
                        'message' => 'Voting tidak sedang aktif.',
                    ], 422);
                }

                $submissionId = DB::table('vote_submissions')->insertGetId([
                    'vote_id' => $validated['vote_id'],
                    'vote_option_id' => $validated['vote_option_id'],
                    'user_id' => $userId,
                    'request_id' => $validated['request_id'],
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);

                DB::table('vote_options')
                    ->where('id', $validated['vote_option_id'])
                    ->where('vote_id', $validated['vote_id'])
                    ->increment('votes_count', 1, ['updated_at' => $timestamp]);

                return response()->json([
                    'status' => 'processed',
                    'data' => ['submission_id' => (int) $submissionId],
                ], 201);
            });
        } catch (QueryException $exception) {
            $existingRequest = DB::table('vote_submissions')
                ->where('request_id', $validated['request_id'])
                ->first();

            if ($existingRequest !== null) {
                return $this->duplicateSubmissionResponse($existingRequest, $validated, $userId);
            }

            $existingVote = DB::table('vote_submissions')
                ->where('vote_id', $validated['vote_id'])
                ->where('user_id', $userId)
                ->first();

            if ($existingVote !== null) {
                return $this->alreadyVotedResponse();
            }

            throw $exception;
        }
    }

    /**
     * @param  array{vote_id: int, vote_option_id: int, request_id: string}  $validated
     */
    private function duplicateSubmissionResponse(
        object $submission,
        array $validated,
        string $userId,
    ): JsonResponse {
        if (
            (string) $submission->user_id !== $userId
            || (int) $submission->vote_id !== $validated['vote_id']
            || (int) $submission->vote_option_id !== $validated['vote_option_id']
        ) {
            return response()->json([
                'message' => 'request_id sudah digunakan untuk suara yang berbeda.',
            ], 409);
        }

        return response()->json([
            'status' => 'already_processed',
            'data' => ['submission_id' => (int) $submission->id],
        ]);
    }

    private function alreadyVotedResponse(): JsonResponse
    {
        return response()->json([
            'message' => 'Anda sudah memberikan suara untuk voting ini.',
        ], 409);
    }
}
