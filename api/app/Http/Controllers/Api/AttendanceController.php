<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AttendanceController extends Controller
{
    public function claim(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event_id' => ['required', 'uuid', 'exists:events,id'],
            'token' => ['required', 'string', 'size:64'],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'request_id' => ['required', 'uuid'],
        ]);
        $userId = (string) $request->user()->getAuthIdentifier();
        $existing = DB::table('attendance_claims')
            ->where('request_id', $validated['request_id'])
            ->first();

        if ($existing !== null) {
            return $this->duplicateClaimResponse($existing, $validated, $userId);
        }

        $event = DB::table('events')
            ->where('id', $validated['event_id'])
            ->where('status', 'active')
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->first();

        if ($event === null) {
            return response()->json(['message' => 'Acara tidak sedang berlangsung.'], 422);
        }

        if (! $this->isValidToken($event->id, $validated['token'])) {
            throw ValidationException::withMessages([
                'token' => 'Token QR tidak valid atau sudah kedaluwarsa.',
            ]);
        }

        $distance = $this->distanceInMeters(
            (float) $validated['lat'],
            (float) $validated['lng'],
            (float) $event->location_lat,
            (float) $event->location_lng,
        );
        $distanceMeters = (int) round($distance);
        $status = $distance > (int) $event->radius_m ? 'rejected' : 'pending';
        $claimId = (string) Str::uuid();
        $timestamp = now();

        try {
            DB::table('attendance_claims')->insert([
                'id' => $claimId,
                'event_id' => $event->id,
                'user_id' => $userId,
                'lat' => $validated['lat'],
                'lng' => $validated['lng'],
                'distance_m' => $distanceMeters,
                'status' => $status,
                'request_id' => $validated['request_id'],
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);
        } catch (QueryException $exception) {
            $existing = DB::table('attendance_claims')
                ->where('request_id', $validated['request_id'])
                ->first();

            if ($existing === null) {
                throw $exception;
            }

            return $this->duplicateClaimResponse($existing, $validated, $userId);
        }

        if ($status === 'rejected') {
            return response()->json([
                'status' => 'rejected',
                'claim_id' => $claimId,
                'distance_m' => $distanceMeters,
                'radius_m' => (int) $event->radius_m,
            ], 201);
        }

        return response()->json([
            'status' => 'processed',
            'claim_id' => $claimId,
            'distance_m' => $distanceMeters,
        ], 201);
    }

    public function pending(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event_id' => ['required', 'uuid', 'exists:events,id'],
        ]);
        $userId = (string) $request->user()->getAuthIdentifier();
        $claims = DB::table('attendance_claims')
            ->join('users', 'attendance_claims.user_id', '=', 'users.id')
            ->where('attendance_claims.event_id', $validated['event_id'])
            ->where('attendance_claims.status', 'pending')
            ->where('attendance_claims.user_id', '!=', $userId)
            ->orderBy('attendance_claims.created_at')
            ->get([
                'attendance_claims.id',
                'attendance_claims.event_id',
                'attendance_claims.distance_m',
                'attendance_claims.created_at',
                'users.name as member_name',
            ]);

        return response()->json(['claims' => $claims]);
    }

    public function attest(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'claim_id' => ['required', 'uuid', 'exists:attendance_claims,id'],
            'request_id' => ['required', 'uuid'],
        ]);
        $attesterId = (string) $request->user()->getAuthIdentifier();
        $existingRequest = DB::table('attestations')
            ->where('request_id', $validated['request_id'])
            ->first();

        if ($existingRequest !== null) {
            return $this->duplicateAttestationResponse($existingRequest, $validated, $attesterId);
        }

        $claim = DB::table('attendance_claims')
            ->join('users as claimants', 'attendance_claims.user_id', '=', 'claimants.id')
            ->where('attendance_claims.id', $validated['claim_id'])
            ->select([
                'attendance_claims.id',
                'attendance_claims.status',
                'attendance_claims.user_id',
                'claimants.cluster_id as claimant_cluster_id',
            ])
            ->first();

        if ($claim === null) {
            return response()->json(['message' => 'Klaim tidak ditemukan.'], 404);
        }

        if ($claim->user_id === $attesterId) {
            return response()->json(['message' => 'Anda tidak dapat memvalidasi klaim sendiri.'], 403);
        }

        $existingAttestation = DB::table('attestations')
            ->where('claim_id', $claim->id)
            ->where('attester_id', $attesterId)
            ->first();

        if ($existingAttestation !== null) {
            return response()->json([
                'status' => 'already_processed',
                'claim_status' => $claim->status,
            ]);
        }

        if ($claim->status !== 'pending') {
            return response()->json(['message' => 'Klaim ini tidak lagi menunggu validasi.'], 409);
        }

        $attester = DB::table('users')->where('id', $attesterId)->first(['cluster_id']);

        if (
            $claim->claimant_cluster_id === null
            || $attester === null
            || $attester->cluster_id === null
        ) {
            throw ValidationException::withMessages([
                'claim_id' => 'Anggota harus memiliki cluster_id untuk melakukan validasi silang.',
            ]);
        }

        if ($claim->claimant_cluster_id === $attester->cluster_id) {
            return response()->json(['message' => 'Validator harus berasal dari cluster yang berbeda.'], 403);
        }

        $existingCluster = DB::table('attestations')
            ->where('attestations.claim_id', $claim->id)
            ->where('attestations.cluster_id', $attester->cluster_id)
            ->exists();

        if ($existingCluster) {
            return response()->json(['message' => 'Klaim ini sudah divalidasi oleh anggota dari cluster Anda.'], 403);
        }

        $timestamp = now();

        try {
            $result = DB::transaction(function () use ($validated, $attesterId, $attester, $claim, $timestamp): array {
                DB::table('attestations')->insert([
                    'id' => (string) Str::uuid(),
                    'claim_id' => $claim->id,
                    'attester_id' => $attesterId,
                    'cluster_id' => $attester->cluster_id,
                    'status' => 'trusted',
                    'request_id' => $validated['request_id'],
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);

                $distinctClusters = DB::table('attestations')
                    ->where('attestations.claim_id', $claim->id)
                    ->distinct()
                    ->count('attestations.cluster_id');
                $claimStatus = $distinctClusters >= 2 ? 'valid' : 'pending';

                if ($claimStatus === 'valid') {
                    DB::table('attendance_claims')
                        ->where('id', $claim->id)
                        ->where('status', 'pending')
                        ->update(['status' => 'valid', 'updated_at' => $timestamp]);
                }

                return [
                    'status' => 'processed',
                    'claim_status' => $claimStatus,
                    'attestations' => $distinctClusters,
                ];
            });
        } catch (QueryException $exception) {
            $existingRequest = DB::table('attestations')
                ->where('request_id', $validated['request_id'])
                ->first();

            if ($existingRequest !== null) {
                return $this->duplicateAttestationResponse($existingRequest, $validated, $attesterId);
            }

            $existingAttestation = DB::table('attestations')
                ->where('claim_id', $claim->id)
                ->where('attester_id', $attesterId)
                ->first();

            if ($existingAttestation !== null) {
                return response()->json([
                    'status' => 'already_processed',
                    'claim_status' => DB::table('attendance_claims')->where('id', $claim->id)->value('status'),
                ]);
            }

            $existingCluster = DB::table('attestations')
                ->where('claim_id', $claim->id)
                ->where('cluster_id', $attester->cluster_id)
                ->exists();

            if ($existingCluster) {
                return response()->json(['message' => 'Klaim ini sudah divalidasi oleh anggota dari cluster Anda.'], 403);
            }

            throw $exception;
        }

        return response()->json($result, 201);
    }

    private function isValidToken(string $eventId, string $token): bool
    {
        $period = intdiv(now()->timestamp, 90);
        $key = (string) config('app.key');

        for ($offset = -1; $offset <= 1; $offset++) {
            $expected = hash_hmac('sha256', "{$eventId}:".($period + $offset), $key);

            if (hash_equals($expected, $token)) {
                return true;
            }
        }

        return false;
    }

    private function distanceInMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $latitudeDelta = deg2rad($lat2 - $lat1);
        $longitudeDelta = deg2rad($lng2 - $lng1);
        $a = sin($latitudeDelta / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($longitudeDelta / 2) ** 2;
        $a = min(1.0, max(0.0, $a));

        return 6_371_000 * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function duplicateClaimResponse(object $claim, array $validated, string $userId): JsonResponse
    {
        if (
            $claim->user_id !== $userId
            || $claim->event_id !== $validated['event_id']
            || abs((float) $claim->lat - (float) $validated['lat']) > 0.000000005
            || abs((float) $claim->lng - (float) $validated['lng']) > 0.000000005
        ) {
            return response()->json([
                'message' => 'The request_id has already been used for a different attendance claim.',
            ], 409);
        }

        return response()->json([
            'status' => $claim->status === 'rejected' ? 'rejected' : 'already_processed',
            'claim_id' => $claim->id,
            'claim_status' => $claim->status,
            'distance_m' => (int) $claim->distance_m,
        ]);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function duplicateAttestationResponse(
        object $attestation,
        array $validated,
        string $attesterId,
    ): JsonResponse {
        if (
            $attestation->claim_id !== $validated['claim_id']
            || $attestation->attester_id !== $attesterId
        ) {
            return response()->json([
                'message' => 'The request_id has already been used for a different attestation.',
            ], 409);
        }

        return response()->json([
            'status' => 'already_processed',
            'claim_status' => DB::table('attendance_claims')
                ->where('id', $attestation->claim_id)
                ->value('status'),
        ]);
    }
}
