<?php

namespace App\Services;

use App\Models\ReputationLog;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class ReputationService
{
    public const QUIZ_CORRECT = 'quiz_correct';

    public const ESSAY_PASSED = 'essay_passed';

    public const ATTENDANCE_VALIDATED = 'attendance_validated';

    public const CITIZEN_JURY = 'citizen_jury';

    public function award(
        string $userId,
        string $action,
        int $points,
        string $description,
        string $requestId,
    ): ?ReputationLog {
        return DB::transaction(function () use ($userId, $action, $points, $description, $requestId) {
            $user = User::query()->lockForUpdate()->findOrFail($userId);
            $existingLog = ReputationLog::query()
                ->where('action', $action)
                ->where('request_id', $requestId)
                ->first();

            if ($existingLog !== null) {
                return null;
            }

            try {
                $log = ReputationLog::query()->create([
                    'user_id' => $userId,
                    'action' => $action,
                    'points' => $points,
                    'description' => $description,
                    'request_id' => $requestId,
                ]);
            } catch (QueryException $exception) {
                $existingLog = ReputationLog::query()
                    ->where('action', $action)
                    ->where('request_id', $requestId)
                    ->first();

                if ($existingLog === null) {
                    throw $exception;
                }

                return null;
            }

            $newTotal = (int) $user->reputation_points + $points;
            $user->forceFill([
                'reputation_points' => $newTotal,
                'reputation_level' => $this->levelForPoints($newTotal),
            ])->save();

            return $log;
        }, 3);
    }

    public function levelForPoints(int $points): string
    {
        return match (true) {
            $points >= 1000 => 'Pengawal Demokrasi',
            $points >= 500 => 'Penggerak',
            $points >= 100 => 'Warga Aktif',
            default => 'Warga Baru',
        };
    }
}
