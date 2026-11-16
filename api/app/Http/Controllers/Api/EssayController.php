<?php

namespace App\Http\Controllers\Api;

use App\Events\ReputationActivity;
use App\Http\Controllers\Controller;
use App\Services\ReputationService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EssayController extends Controller
{
    public function question(Request $request): JsonResponse
    {
        $userId = (string) $request->user()->getAuthIdentifier();

        $item = DB::table('items')
            ->whereRaw("json_extract(payload, '$.type') = ?", ['essay'])
            ->whereNotExists(function ($query) use ($userId) {
                $query->selectRaw('1')
                    ->from('essay_submissions')
                    ->whereColumn('essay_submissions.item_id', 'items.id')
                    ->where('essay_submissions.user_id', $userId);
            })
            ->inRandomOrder()
            ->select(['id', 'domain', 'level', 'payload'])
            ->first();

        if ($item !== null) {
            $item->payload = json_decode($item->payload, true, flags: JSON_THROW_ON_ERROR);
        }

        return response()->json(['item' => $item]);
    }

    public function submit(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'item_id' => ['required', 'string', 'exists:items,id'],
            'answer_text' => ['required', 'string', 'max:20000'],
            'request_id' => ['required', 'uuid'],
        ]);
        $userId = (string) $request->user()->getAuthIdentifier();

        $itemPayload = DB::table('items')
            ->where('id', $validated['item_id'])
            ->value('payload');
        $item = json_decode($itemPayload, true, flags: JSON_THROW_ON_ERROR);

        if (($item['type'] ?? null) !== 'essay') {
            throw ValidationException::withMessages([
                'item_id' => 'Item ini tidak menerima jawaban esai.',
            ]);
        }

        $existingSubmission = DB::table('essay_submissions')
            ->where('request_id', $validated['request_id'])
            ->first();

        if ($existingSubmission !== null) {
            return $this->duplicateSubmissionResponse(
                $existingSubmission,
                $userId,
                $validated['item_id'],
                $validated['answer_text'],
            );
        }

        $timestamp = now();

        try {
            $submissionId = DB::table('essay_submissions')->insertGetId([
                'user_id' => $userId,
                'item_id' => $validated['item_id'],
                'answer_text' => $validated['answer_text'],
                'status' => 'pending',
                'request_id' => $validated['request_id'],
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);
        } catch (QueryException $exception) {
            $existingSubmission = DB::table('essay_submissions')
                ->where('request_id', $validated['request_id'])
                ->first();

            if ($existingSubmission === null) {
                throw $exception;
            }

            return $this->duplicateSubmissionResponse(
                $existingSubmission,
                $userId,
                $validated['item_id'],
                $validated['answer_text'],
            );
        }

        return response()->json([
            'status' => 'processed',
            'data' => ['submission_id' => $submissionId],
        ], 201);
    }

    public function pending(Request $request): JsonResponse
    {
        $userId = (string) $request->user()->getAuthIdentifier();

        $submission = DB::table('essay_submissions')
            ->join('items', 'essay_submissions.item_id', '=', 'items.id')
            ->where('essay_submissions.status', 'pending')
            ->where('essay_submissions.user_id', '!=', $userId)
            ->inRandomOrder()
            ->select([
                'essay_submissions.id',
                'essay_submissions.item_id',
                'essay_submissions.answer_text',
                'essay_submissions.created_at',
                'items.payload',
            ])
            ->first();

        if ($submission === null) {
            return response()->json(['submission' => null]);
        }

        $submission->item = [
            'id' => $submission->item_id,
            'payload' => json_decode($submission->payload, true, flags: JSON_THROW_ON_ERROR),
        ];
        unset($submission->payload, $submission->item_id);

        return response()->json(['submission' => $submission]);
    }

    public function review(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'submission_id' => ['required', 'integer', 'exists:essay_submissions,id'],
            'score' => ['required', 'integer', 'between:0,100'],
            'feedback' => ['nullable', 'string', 'max:10000'],
            'request_id' => ['required', 'uuid'],
        ]);
        $userId = (string) $request->user()->getAuthIdentifier();

        $existingReview = DB::table('essay_submissions')
            ->where('review_request_id', $validated['request_id'])
            ->first();

        if ($existingReview !== null) {
            return $this->duplicateReviewResponse($existingReview, $validated, $userId);
        }

        $submission = DB::table('essay_submissions')
            ->where('id', $validated['submission_id'])
            ->first();

        if ($submission === null) {
            return response()->json(['message' => 'Esai tidak ditemukan.'], 404);
        }

        if ($submission->user_id === $userId) {
            return response()->json(['message' => 'Anda tidak dapat menilai esai sendiri.'], 403);
        }

        if ($submission->status !== 'pending') {
            return response()->json(['message' => 'Esai ini sudah dinilai.'], 409);
        }

        try {
            $updated = DB::transaction(function () use ($validated, $userId, $submission): int {
                $updated = DB::table('essay_submissions')
                    ->where('id', $validated['submission_id'])
                    ->where('status', 'pending')
                    ->where('user_id', '!=', $userId)
                    ->update([
                        'status' => 'reviewed',
                        'score' => $validated['score'],
                        'reviewer_id' => $userId,
                        'feedback' => $validated['feedback'] ?? null,
                        'review_request_id' => $validated['request_id'],
                        'updated_at' => now(),
                    ]);

                if ($updated === 1 && $validated['score'] >= 70) {
                    event(new ReputationActivity(
                        (string) $submission->user_id,
                        ReputationService::ESSAY_PASSED,
                        50,
                        'Lulus peer-review esai dengan nilai minimal 70.',
                        $validated['request_id'],
                    ));
                }

                return $updated;
            });
        } catch (QueryException $exception) {
            $existingReview = DB::table('essay_submissions')
                ->where('review_request_id', $validated['request_id'])
                ->first();

            if ($existingReview === null) {
                throw $exception;
            }

            return $this->duplicateReviewResponse($existingReview, $validated, $userId);
        }

        if ($updated === 0) {
            $existingReview = DB::table('essay_submissions')
                ->where('review_request_id', $validated['request_id'])
                ->first();

            if ($existingReview !== null) {
                return $this->duplicateReviewResponse($existingReview, $validated, $userId);
            }

            return response()->json(['message' => 'Esai ini sudah dinilai.'], 409);
        }

        return response()->json([
            'status' => 'processed',
            'data' => ['submission_id' => $validated['submission_id']],
        ]);
    }

    private function duplicateSubmissionResponse(
        object $submission,
        string $userId,
        string $itemId,
        string $answerText,
    ): JsonResponse {
        if (
            $submission->user_id !== $userId
            || $submission->item_id !== $itemId
            || $submission->answer_text !== $answerText
        ) {
            return response()->json([
                'message' => 'The request_id has already been used for a different submission.',
            ], 409);
        }

        return response()->json([
            'status' => 'already_processed',
            'data' => ['submission_id' => $submission->id],
        ]);
    }

    /**
     * @param  array{submission_id: int, score: int, feedback?: ?string, request_id: string}  $validated
     */
    private function duplicateReviewResponse(
        object $submission,
        array $validated,
        string $userId,
    ): JsonResponse {
        if (
            (int) $submission->id !== $validated['submission_id']
            || $submission->reviewer_id !== $userId
            || (int) $submission->score !== $validated['score']
            || $submission->feedback !== ($validated['feedback'] ?? null)
        ) {
            return response()->json([
                'message' => 'The request_id has already been used for a different review.',
            ], 409);
        }

        return response()->json([
            'status' => 'already_processed',
            'data' => ['submission_id' => $submission->id],
        ]);
    }
}
