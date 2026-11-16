<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RecallRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RecallController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        if (! $this->isActiveMember($request)) {
            return response()->json(['message' => 'Hanya anggota terverifikasi yang dapat mengajukan recall.'], 403);
        }

        $validated = $request->validate([
            'target_user_id' => ['required', 'uuid', 'exists:users,id'],
            'reason' => ['required', 'string', 'max:10000'],
            'evidence_url' => ['nullable', 'max:2048', Rule::url()->protocols(['http', 'https'])],
            'request_id' => ['required', 'uuid'],
        ]);

        $existingRequest = DB::table('recall_requests')
            ->where('request_id', $validated['request_id'])
            ->first();

        if ($existingRequest !== null) {
            return $this->duplicateRequestResponse($existingRequest, $validated);
        }

        $targetIsActiveMember = DB::table('users')
            ->where('id', $validated['target_user_id'])
            ->where('role', 'member')
            ->where('is_verified', true)
            ->exists();

        if (! $targetIsActiveMember) {
            return response()->json(['message' => 'Target recall harus merupakan anggota terverifikasi.'], 422);
        }

        $recallId = (string) Str::uuid();
        $timestamp = now();

        try {
            DB::table('recall_requests')->insert([
                'id' => $recallId,
                'target_user_id' => $validated['target_user_id'],
                'reason' => $validated['reason'],
                'evidence_url' => $validated['evidence_url'] ?? null,
                'status' => 'voting',
                'request_id' => $validated['request_id'],
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);
        } catch (QueryException $exception) {
            $existingRequest = DB::table('recall_requests')
                ->where('request_id', $validated['request_id'])
                ->first();

            if ($existingRequest === null) {
                throw $exception;
            }

            return $this->duplicateRequestResponse($existingRequest, $validated);
        }

        return response()->json([
            'status' => 'processed',
            'recall' => [
                'id' => $recallId,
                'target_user_id' => $validated['target_user_id'],
                'reason' => $validated['reason'],
                'evidence_url' => $validated['evidence_url'] ?? null,
                'status' => 'voting',
            ],
        ], 201);
    }

    public function active(Request $request): JsonResponse
    {
        $recalls = RecallRequest::query()
            ->with('target:id,name')
            ->withCount([
                'votes as agree_count' => fn (Builder $query) => $query->where('vote', true),
                'votes as reject_count' => fn (Builder $query) => $query->where('vote', false),
            ])
            ->where('status', 'voting')
            ->orderBy('created_at')
            ->get([
                'id',
                'target_user_id',
                'reason',
                'evidence_url',
                'created_at',
            ]);

        $recallIds = $recalls->pluck('id');
        $activeMemberCount = $this->activeMemberCount();
        $userVotes = DB::table('recall_votes')
            ->where('user_id', $request->user()->getAuthIdentifier())
            ->whereIn('recall_request_id', $recallIds)
            ->pluck('recall_request_id')
            ->all();

        return response()->json([
            'recalls' => $recalls->map(function (object $recall) use (
                $activeMemberCount,
                $userVotes,
            ): array {
                return [
                    'id' => $recall->id,
                    'target_user_id' => $recall->target_user_id,
                    'target_name' => $recall->target->name,
                    'reason' => $recall->reason,
                    'evidence_url' => $recall->evidence_url,
                    'created_at' => $recall->created_at,
                    'status' => 'voting',
                    'agree_count' => (int) $recall->agree_count,
                    'reject_count' => (int) $recall->reject_count,
                    'active_member_count' => $activeMemberCount,
                    'votes_required' => $this->requiredVotes($activeMemberCount),
                    'has_voted' => in_array($recall->id, $userVotes, true),
                ];
            })->values(),
        ]);
    }

    public function vote(Request $request): JsonResponse
    {
        if (! $this->isActiveMember($request)) {
            return response()->json(['message' => 'Hanya anggota terverifikasi yang dapat memberikan suara.'], 403);
        }

        $validated = $request->validate([
            'recall_request_id' => ['required', 'uuid', 'exists:recall_requests,id'],
            'vote' => ['required', 'boolean'],
            'request_id' => ['required', 'uuid'],
        ]);
        $vote = (bool) $validated['vote'];

        $existingRequest = DB::table('recall_votes')
            ->where('request_id', $validated['request_id'])
            ->first();

        if ($existingRequest !== null) {
            return $this->duplicateVoteResponse($existingRequest, $validated, $request, $vote);
        }

        $userId = (string) $request->user()->getAuthIdentifier();
        $timestamp = now();

        try {
            return DB::transaction(function () use ($validated, $vote, $userId, $timestamp, $request): JsonResponse {
                $existingRequest = DB::table('recall_votes')
                    ->where('request_id', $validated['request_id'])
                    ->first();

                if ($existingRequest !== null) {
                    return $this->duplicateVoteResponse($existingRequest, $validated, $request, $vote);
                }

                $recall = DB::table('recall_requests')
                    ->where('id', $validated['recall_request_id'])
                    ->lockForUpdate()
                    ->first();

                if ($recall === null) {
                    return response()->json(['message' => 'Pengajuan recall tidak ditemukan.'], 404);
                }

                if ($recall->status !== 'voting') {
                    return response()->json(['message' => 'Voting recall ini tidak lagi aktif.'], 409);
                }

                $existingVote = DB::table('recall_votes')
                    ->where('recall_request_id', $recall->id)
                    ->where('user_id', $userId)
                    ->first();

                if ($existingVote !== null) {
                    return response()->json(['message' => 'Anda sudah memberikan suara untuk recall ini.'], 409);
                }

                $voteId = (string) Str::uuid();
                DB::table('recall_votes')->insert([
                    'id' => $voteId,
                    'recall_request_id' => $recall->id,
                    'user_id' => $userId,
                    'vote' => $vote,
                    'request_id' => $validated['request_id'],
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);

                $activeMemberCount = $this->activeMemberCount();
                $agreeCount = DB::table('recall_votes')
                    ->where('recall_request_id', $recall->id)
                    ->where('vote', true)
                    ->count();
                $approved = $agreeCount >= $this->requiredVotes($activeMemberCount);

                if ($approved) {
                    DB::table('recall_requests')
                        ->where('id', $recall->id)
                        ->update(['status' => 'approved', 'updated_at' => $timestamp]);
                    DB::table('users')
                        ->where('id', $recall->target_user_id)
                        ->update(['role' => 'guest', 'updated_at' => $timestamp]);
                }

                return response()->json([
                    'status' => 'processed',
                    'data' => [
                        'vote_id' => $voteId,
                        'recall_status' => $approved ? 'approved' : 'voting',
                        'agree_count' => $agreeCount,
                        'votes_required' => $this->requiredVotes($activeMemberCount),
                    ],
                ], 201);
            });
        } catch (QueryException $exception) {
            $existingRequest = DB::table('recall_votes')
                ->where('request_id', $validated['request_id'])
                ->first();

            if ($existingRequest !== null) {
                return $this->duplicateVoteResponse($existingRequest, $validated, $request, $vote);
            }

            $existingVote = DB::table('recall_votes')
                ->where('recall_request_id', $validated['recall_request_id'])
                ->where('user_id', $userId)
                ->exists();

            if ($existingVote) {
                return response()->json(['message' => 'Anda sudah memberikan suara untuk recall ini.'], 409);
            }

            throw $exception;
        }
    }

    private function isActiveMember(Request $request): bool
    {
        return $request->user()?->role === 'member'
            && $request->user()?->is_verified === true;
    }

    private function activeMemberCount(): int
    {
        return DB::table('users')
            ->where('role', 'member')
            ->where('is_verified', true)
            ->count();
    }

    private function requiredVotes(int $activeMemberCount): int
    {
        return intdiv((2 * $activeMemberCount) + 2, 3);
    }

    /**
     * @param  array{target_user_id: string, reason: string, evidence_url?: ?string, request_id: string}  $validated
     */
    private function duplicateRequestResponse(object $existing, array $validated): JsonResponse
    {
        if (
            $existing->target_user_id !== $validated['target_user_id']
            || $existing->reason !== $validated['reason']
            || $existing->evidence_url !== ($validated['evidence_url'] ?? null)
        ) {
            return response()->json(['message' => 'request_id sudah digunakan untuk pengajuan recall yang berbeda.'], 409);
        }

        return response()->json([
            'status' => 'already_processed',
            'recall' => [
                'id' => $existing->id,
                'target_user_id' => $existing->target_user_id,
                'status' => $existing->status,
            ],
        ]);
    }

    /**
     * @param  array{recall_request_id: string, request_id: string}  $validated
     */
    private function duplicateVoteResponse(
        object $existing,
        array $validated,
        Request $request,
        bool $vote,
    ): JsonResponse {
        if (
            $existing->user_id !== (string) $request->user()->getAuthIdentifier()
            || $existing->recall_request_id !== $validated['recall_request_id']
            || (bool) $existing->vote !== $vote
        ) {
            return response()->json(['message' => 'request_id sudah digunakan untuk suara yang berbeda.'], 409);
        }

        return response()->json([
            'status' => 'already_processed',
            'data' => ['vote_id' => $existing->id],
        ]);
    }
}
