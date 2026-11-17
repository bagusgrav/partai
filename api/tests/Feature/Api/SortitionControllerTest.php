<?php

namespace Tests\Feature\Api;

use App\Models\MemberReputation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SortitionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_panels_are_limited_to_five_verified_members_with_mastery_level_four(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $eligibleIds = [];

        for ($memberIndex = 0; $memberIndex < 6; $memberIndex++) {
            $member = User::factory()->create([
                'role' => 'member',
                'is_verified' => true,
            ]);
            $this->createReputation($member, 4, 0);
            $eligibleIds[] = $member->id;
        }

        $underMastered = User::factory()->create([
            'role' => 'member',
            'is_verified' => true,
        ]);
        $this->createReputation($underMastered, 3, 10000);

        $unverified = User::factory()->create([
            'role' => 'member',
            'is_verified' => false,
        ]);
        $this->createReputation($unverified, 4, 0);

        $response = $this->getJson('/api/sortition/panels')
            ->assertOk()
            ->assertJsonCount(5, 'panels')
            ->assertJsonPath('panels.0.mastery', 4);

        foreach ($response->json('panels') as $panel) {
            $this->assertContains($panel['id'], $eligibleIds);
        }
    }

    private function createReputation(User $user, int $level, int $participationScore): void
    {
        MemberReputation::query()->create([
            'user_id' => $user->id,
            'mastery_scores' => [
                'hak_konstitusional' => $level === 4 ? 80 : 79,
                'alasan_bernegara' => $level === 4 ? 80 : 79,
                'keuangan_negara' => $level === 4 ? 80 : 79,
                'hukum' => $level === 4 ? 80 : 79,
                'politik_bernegara' => $level === 4 ? 80 : 79,
            ],
            'overall_mastery_level' => $level,
            'participation_score' => $participationScore,
        ]);
    }
}
