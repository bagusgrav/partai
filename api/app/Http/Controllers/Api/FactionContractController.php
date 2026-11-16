<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FactionContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FactionContractController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['sometimes', 'required', Rule::in(FactionContract::STATUSES)],
        ]);

        $contracts = FactionContract::query()
            ->when(
                isset($validated['status']),
                fn ($query) => $query->where('status', $validated['status']),
            )
            ->orderByDesc('created_at')
            ->get();

        return response()->json($contracts);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'politician_id' => ['nullable', 'uuid', 'exists:users,id'],
            'politician_name' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'status' => ['sometimes', 'required', Rule::in(FactionContract::STATUSES)],
            'evidence_url' => ['nullable', 'url', 'max:255'],
            'deadline' => ['nullable', 'date_format:Y-m-d'],
        ]);
        $validated['status'] ??= 'BELUM_DIMULAI';

        $contract = FactionContract::query()->create($validated);

        return response()->json($contract, 201);
    }

    public function update(Request $request, FactionContract $contract): JsonResponse
    {
        $validated = $request->validate([
            'politician_id' => ['sometimes', 'nullable', 'uuid', 'exists:users,id'],
            'politician_name' => ['sometimes', 'required', 'string', 'max:255'],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string'],
            'status' => ['sometimes', 'required', Rule::in(FactionContract::STATUSES)],
            'evidence_url' => ['sometimes', 'nullable', 'url', 'max:255'],
            'deadline' => ['sometimes', 'nullable', 'date_format:Y-m-d'],
        ]);

        $contract->update($validated);

        return response()->json($contract->refresh());
    }
}
