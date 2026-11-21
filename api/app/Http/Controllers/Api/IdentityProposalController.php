<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IdentityProposal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IdentityProposalController extends Controller
{
    public function index(): JsonResponse
    {
        $proposals = IdentityProposal::query()
            ->where('status', 'approved')
            ->latest()
            ->get([
                'id',
                'proposed_name',
                'proposed_color',
                'proposed_logo_desc',
                'created_at',
            ]);

        return response()->json(['proposals' => $proposals]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->nik_verified_at === null) {
            return response()->json([
                'message' => 'NIK belum terverifikasi.',
                'code' => 'nik_not_verified',
            ], 403);
        }

        $validated = $request->validate([
            'proposed_name' => ['required', 'string', 'max:255'],
            'proposed_color' => ['required', 'string', 'max:255'],
            'proposed_logo_desc' => ['required', 'string', 'max:10000'],
        ]);

        $proposal = IdentityProposal::query()->create([
            ...$validated,
            'user_id' => $user->getKey(),
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Usulan identitas berhasil dikirim untuk ditinjau.',
            'proposal' => [
                'id' => $proposal->id,
                'status' => $proposal->status,
            ],
        ], 201);
    }
}
