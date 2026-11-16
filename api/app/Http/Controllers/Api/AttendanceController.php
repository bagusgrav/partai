<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    public function claim(Request $request, AttendanceService $attendance): JsonResponse
    {
        $validated = $request->validate([
            'event_id' => ['required', 'uuid', 'exists:events,id'],
            'token' => ['required', 'string', 'size:64'],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'request_id' => ['required', 'uuid'],
        ]);
        $userId = (string) $request->user()->getAuthIdentifier();

        return $attendance->claim($validated, $userId);
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

    public function attest(Request $request, AttendanceService $attendance): JsonResponse
    {
        $validated = $request->validate([
            'claim_id' => ['required', 'uuid', 'exists:attendance_claims,id'],
            'request_id' => ['required', 'uuid'],
        ]);
        $attesterId = (string) $request->user()->getAuthIdentifier();

        return $attendance->attest($validated, $attesterId);
    }
}
