<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $notifications = $user->notifications()
            ->latest()
            ->limit(50)
            ->get(['id', 'type', 'data', 'read_at', 'created_at'])
            ->map(fn ($notification): array => [
                'id' => $notification->id,
                'type' => $notification->data['type'] ?? $notification->type,
                'data' => $notification->data,
                'read_at' => $notification->read_at?->toISOString(),
                'created_at' => $notification->created_at?->toISOString(),
            ]);

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $user->notifications()->whereNull('read_at')->count(),
        ]);
    }

    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()->notifications()->whereKey($id)->firstOrFail();
        $notification->markAsRead();

        return response()->json([
            'status' => 'read',
            'id' => $notification->id,
            'read_at' => $notification->read_at?->toISOString(),
        ]);
    }
}
