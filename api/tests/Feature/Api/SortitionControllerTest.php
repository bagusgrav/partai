<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SortitionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_panels_are_randomly_limited_to_five_verified_members_with_three_mastered_items(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $eligibleIds = [];

        for ($memberIndex = 0; $memberIndex < 6; $memberIndex++) {
            $member = User::factory()->create([
                'role' => 'member',
                'is_verified' => true,
            ]);
            $this->addMasteredItems($member, 3, 3);
            $eligibleIds[] = $member->id;
        }

        $underMastered = User::factory()->create([
            'role' => 'member',
            'is_verified' => true,
        ]);
        $this->addMasteredItems($underMastered, 2, 3);

        $unverified = User::factory()->create([
            'role' => 'member',
            'is_verified' => false,
        ]);
        $this->addMasteredItems($unverified, 4, 3);

        $response = $this->getJson('/api/sortition/panels')
            ->assertOk()
            ->assertJsonCount(5, 'panels')
            ->assertJsonPath('panels.0.mastery', 3);

        foreach ($response->json('panels') as $panel) {
            $this->assertContains($panel['id'], $eligibleIds);
        }
    }

    private function addMasteredItems(User $user, int $count, int $reps): void
    {
        for ($itemIndex = 0; $itemIndex < $count; $itemIndex++) {
            $itemId = (string) Str::uuid();
            $timestamp = now();
            DB::table('items')->insert([
                'id' => $itemId,
                'domain' => 'hukum',
                'level' => 'dasar',
                'payload' => '{}',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);
            DB::table('user_item_states')->insert([
                'user_id' => $user->id,
                'item_id' => $itemId,
                'reps' => $reps,
                'interval_d' => 6,
                'ease' => 2.5,
                'next_review' => $timestamp,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);
        }
    }
}
