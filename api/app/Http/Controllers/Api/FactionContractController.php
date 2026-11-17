<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FactionContract;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            'request_id' => ['required', 'uuid'],
        ]);
        $requestId = $validated['request_id'];
        unset($validated['request_id']);
        $validated['status'] ??= 'BELUM_DIMULAI';
        $actorId = (string) $request->user()->getAuthIdentifier();

        $existing = DB::table('faction_contract_mutations')->where('request_id', $requestId)->first();

        if ($existing !== null) {
            return $this->duplicateMutationResponse($existing, 'create', null, $actorId, $validated);
        }

        try {
            $contract = DB::transaction(function () use ($requestId, $actorId, $validated): FactionContract {
                $now = now();
                DB::table('faction_contract_mutations')->insert([
                    'request_id' => $requestId,
                    'operation' => 'create',
                    'actor_id' => $actorId,
                    'contract_id' => null,
                    'payload' => json_encode($validated, JSON_THROW_ON_ERROR),
                    'response' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $contract = FactionContract::query()->create($validated);
                DB::table('faction_contract_mutations')->where('request_id', $requestId)->update([
                    'contract_id' => $contract->id,
                    'response' => json_encode($contract->toArray(), JSON_THROW_ON_ERROR),
                    'updated_at' => now(),
                ]);

                return $contract;
            });
        } catch (QueryException $exception) {
            $existing = DB::table('faction_contract_mutations')->where('request_id', $requestId)->first();

            if ($existing === null) {
                throw $exception;
            }

            return $this->duplicateMutationResponse($existing, 'create', null, $actorId, $validated);
        }

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
            'request_id' => ['required', 'uuid'],
        ]);
        $requestId = $validated['request_id'];
        unset($validated['request_id']);
        $actorId = (string) $request->user()->getAuthIdentifier();
        $contractId = (string) $contract->getKey();

        $existing = DB::table('faction_contract_mutations')->where('request_id', $requestId)->first();

        if ($existing !== null) {
            return $this->duplicateMutationResponse($existing, 'update', $contractId, $actorId, $validated);
        }

        try {
            $contractData = DB::transaction(function () use ($requestId, $actorId, $contractId, $validated, $contract): array {
                $now = now();
                DB::table('faction_contract_mutations')->insert([
                    'request_id' => $requestId,
                    'operation' => 'update',
                    'actor_id' => $actorId,
                    'contract_id' => $contractId,
                    'payload' => json_encode($validated, JSON_THROW_ON_ERROR),
                    'response' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $contract->update($validated);
                $contractData = $contract->refresh()->toArray();
                DB::table('faction_contract_mutations')->where('request_id', $requestId)->update([
                    'response' => json_encode($contractData, JSON_THROW_ON_ERROR),
                    'updated_at' => now(),
                ]);

                return $contractData;
            });
        } catch (QueryException $exception) {
            $existing = DB::table('faction_contract_mutations')->where('request_id', $requestId)->first();

            if ($existing === null) {
                throw $exception;
            }

            return $this->duplicateMutationResponse($existing, 'update', $contractId, $actorId, $validated);
        }

        return response()->json($contractData);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function duplicateMutationResponse(
        object $existing,
        string $operation,
        ?string $contractId,
        string $actorId,
        array $payload,
    ): JsonResponse {
        $existingPayload = json_decode($existing->payload, true, flags: JSON_THROW_ON_ERROR);
        ksort($existingPayload);
        ksort($payload);

        if (
            $existing->operation !== $operation
            || ($operation === 'update' && $existing->contract_id !== $contractId)
            || $existing->actor_id !== $actorId
            || $existingPayload !== $payload
        ) {
            return response()->json([
                'message' => 'The request_id has already been used for a different contract mutation.',
            ], 409);
        }

        return response()->json([
            'status' => 'already_processed',
            'data' => json_decode($existing->response, true, flags: JSON_THROW_ON_ERROR),
        ]);
    }
}
