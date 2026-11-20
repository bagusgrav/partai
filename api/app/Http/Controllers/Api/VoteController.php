<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\VoteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VoteController extends Controller
{
    public function active(Request $request, VoteService $votes): JsonResponse
    {
        $response = $votes->active((string) $request->user()->getAuthIdentifier());
        $response['can_vote'] = $this->isEligibleVoter($request);

        return response()->json($response);
    }

    public function submit(Request $request, VoteService $votes): JsonResponse
    {
        if (! $this->isEligibleVoter($request)) {
            return response()->json([
                'message' => 'Hanya anggota terverifikasi yang dapat memilih.',
            ], 403);
        }

        $validated = $request->validate([
            'vote_id' => ['required', 'integer', 'exists:votes,id'],
            'vote_option_id' => [
                'required',
                'integer',
                Rule::exists('vote_options', 'id')->where('vote_id', $request->integer('vote_id')),
            ],
            'request_id' => ['required', 'uuid'],
        ]);
        $validated['vote_id'] = (int) $validated['vote_id'];
        $validated['vote_option_id'] = (int) $validated['vote_option_id'];
        $userId = (string) $request->user()->getAuthIdentifier();

        return $votes->submit($validated, $userId);
    }

    private function isEligibleVoter(Request $request): bool
    {
        $user = $request->user();

        return $user?->role === 'member' && $user?->is_verified === true;
    }
}
