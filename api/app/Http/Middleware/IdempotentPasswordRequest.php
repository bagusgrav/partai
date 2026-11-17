<?php

namespace App\Http\Middleware;

use App\Models\ApiIdempotencyKey;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class IdempotentPasswordRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        $validated = $request->validate([
            'request_id' => ['required', 'uuid'],
        ]);
        $endpoint = (string) $request->route()?->uri();
        $payload = $request->except('request_id');
        $payloadHash = hash_hmac(
            'sha256',
            json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES),
            (string) config('app.key'),
        );

        return DB::transaction(function () use ($request, $next, $validated, $endpoint, $payloadHash): Response {
            $existing = ApiIdempotencyKey::query()
                ->where('request_id', $validated['request_id'])
                ->first();

            if ($existing !== null) {
                if ($existing->endpoint !== $endpoint || ! hash_equals($existing->payload_hash, $payloadHash)) {
                    return response()->json([
                        'message' => 'request_id sudah digunakan untuk permintaan berbeda.',
                    ], 409);
                }

                if ($existing->response_status === null || $existing->response_body === null) {
                    return response()->json([
                        'message' => 'Permintaan dengan request_id ini masih diproses.',
                    ], 409);
                }

                return response($existing->response_body, $existing->response_status)
                    ->header('Content-Type', 'application/json');
            }

            $idempotencyKey = ApiIdempotencyKey::query()->create([
                'request_id' => $validated['request_id'],
                'endpoint' => $endpoint,
                'payload_hash' => $payloadHash,
            ]);
            $response = $next($request);

            if ($response->getStatusCode() >= 500) {
                $idempotencyKey->delete();

                return $response;
            }

            $idempotencyKey->forceFill([
                'response_status' => $response->getStatusCode(),
                'response_body' => $response->getContent(),
            ])->save();

            return $response;
        }, 3);
    }
}
