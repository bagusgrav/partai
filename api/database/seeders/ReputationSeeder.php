<?php

namespace Database\Seeders;

use App\Models\MemberReputation;
use App\Models\User;
use App\Services\ReputationService;
use Illuminate\Database\Seeder;

class ReputationSeeder extends Seeder
{
    public function run(): void
    {
        $masteryScores = app(ReputationService::class)->emptyMasteryScores();

        User::query()
            ->where('role', 'member')
            ->where('is_verified', true)
            ->each(function (User $user) use ($masteryScores): void {
                MemberReputation::query()->firstOrCreate(
                    ['user_id' => $user->getKey()],
                    ['mastery_scores' => $masteryScores],
                );
            });
    }
}
