<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EventController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'location_lat' => ['required', 'numeric', 'between:-90,90'],
            'location_lng' => ['required', 'numeric', 'between:-180,180'],
            'radius_m' => ['sometimes', 'integer', 'min:1'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'request_id' => ['required', 'uuid'],
        ]);
        $coordinatorId = (string) $request->user()->getAuthIdentifier();
        $existing = DB::table('events')->where('request_id', $validated['request_id'])->first();

        if ($existing !== null) {
            return $this->duplicateEventResponse($existing, $validated, $coordinatorId);
        }

        $eventId = (string) Str::uuid();
        $timestamp = now();

        try {
            DB::table('events')->insert([
                'id' => $eventId,
                'title' => $validated['title'],
                'description' => $validated['description'],
                'location_lat' => $validated['location_lat'],
                'location_lng' => $validated['location_lng'],
                'radius_m' => $validated['radius_m'] ?? 100,
                'starts_at' => $validated['starts_at'],
                'ends_at' => $validated['ends_at'],
                'coordinator_id' => $coordinatorId,
                'status' => 'active',
                'request_id' => $validated['request_id'],
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);
        } catch (QueryException $exception) {
            $existing = DB::table('events')->where('request_id', $validated['request_id'])->first();

            if ($existing === null) {
                throw $exception;
            }

            return $this->duplicateEventResponse($existing, $validated, $coordinatorId);
        }

        return response()->json([
            'status' => 'processed',
            'event' => ['id' => $eventId],
        ], 201);
    }

    public function active(): JsonResponse
    {
        $events = DB::table('events')
            ->where('status', 'active')
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->orderBy('starts_at')
            ->get([
                'id',
                'title',
                'description',
                'location_lat',
                'location_lng',
                'radius_m',
                'starts_at',
                'ends_at',
                'coordinator_id',
            ]);

        return response()->json(['events' => $events]);
    }

    public function qr(Request $request, string $id): JsonResponse
    {
        $event = DB::table('events')->where('id', $id)->first();

        if ($event === null) {
            return response()->json(['message' => 'Acara tidak ditemukan.'], 404);
        }

        if ($event->coordinator_id !== (string) $request->user()->getAuthIdentifier()) {
            return response()->json(['message' => 'Hanya koordinator acara yang dapat melihat QR.'], 403);
        }

        if ($event->status !== 'active') {
            return response()->json(['message' => 'Acara ini tidak aktif.'], 409);
        }

        $period = intdiv(now()->timestamp, 90);
        $token = hash_hmac('sha256', "{$event->id}:{$period}", (string) config('app.key'));

        return response()->json($token);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function duplicateEventResponse(object $event, array $validated, string $coordinatorId): JsonResponse
    {
        if (
            $event->coordinator_id !== $coordinatorId
            || $event->title !== $validated['title']
            || $event->description !== $validated['description']
            || abs((float) $event->location_lat - (float) $validated['location_lat']) > 0.000000005
            || abs((float) $event->location_lng - (float) $validated['location_lng']) > 0.000000005
            || (int) $event->radius_m !== (int) ($validated['radius_m'] ?? 100)
            || ! Carbon::parse($event->starts_at)->equalTo(Carbon::parse($validated['starts_at']))
            || ! Carbon::parse($event->ends_at)->equalTo(Carbon::parse($validated['ends_at']))
        ) {
            return response()->json([
                'message' => 'The request_id has already been used for a different event.',
            ], 409);
        }

        return response()->json([
            'status' => 'already_processed',
            'event' => ['id' => $event->id],
        ]);
    }
}
