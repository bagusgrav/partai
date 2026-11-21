<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class StatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_landing_stats_return_aggregates_and_latest_poverty_rate(): void
    {
        $user = User::factory()->create();
        User::factory()->create();
        $now = now();

        DB::table('items')->insert([
            'id' => 'STATS-QUIZ-001',
            'domain' => 'civics',
            'level' => 'basic',
            'payload' => '{"prompt":"Test question"}',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('reviews')->insert([
            [
                'user_id' => $user->id,
                'item_id' => 'STATS-QUIZ-001',
                'correct' => true,
                'quality' => 4,
                'request_id' => (string) Str::uuid(),
                'reviewed_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'user_id' => $user->id,
                'item_id' => 'STATS-QUIZ-001',
                'correct' => false,
                'quality' => 1,
                'request_id' => (string) Str::uuid(),
                'reviewed_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
        DB::table('civic_answer_requests')->insert([
            [
                'id' => (string) Str::uuid(),
                'user_id' => $user->id,
                'request_id' => (string) Str::uuid(),
                'payload_hash' => str_repeat('a', 64),
                'response' => json_encode(['correct' => true], JSON_THROW_ON_ERROR),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => (string) Str::uuid(),
                'user_id' => $user->id,
                'request_id' => (string) Str::uuid(),
                'payload_hash' => str_repeat('b', 64),
                'response' => json_encode(['correct' => false], JSON_THROW_ON_ERROR),
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
        $activeVoteId = DB::table('votes')->insertGetId([
            'title' => 'Active vote',
            'description' => 'Currently active',
            'starts_at' => $now->copy()->subMinute(),
            'ends_at' => $now->copy()->addMinute(),
            'status' => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('votes')->insert([
            'title' => 'Future vote',
            'description' => 'Not active yet',
            'starts_at' => $now->copy()->addMinute(),
            'ends_at' => $now->copy()->addHours(2),
            'status' => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('vote_options')->insert([
            [
                'vote_id' => $activeVoteId,
                'option_text' => 'Option A',
                'votes_count' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'vote_id' => $activeVoteId,
                'option_text' => 'Option B',
                'votes_count' => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
        DB::table('welfare_data')->insert([
            [
                'region_code' => '0000',
                'region_name' => 'Test Region',
                'indicator' => 'kemiskinan',
                'value' => 50.0,
                'unit' => '%',
                'year' => 2024,
                'source' => 'BPS',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'region_code' => '0001',
                'region_name' => 'Test Region 1',
                'indicator' => 'kemiskinan',
                'value' => 8.0,
                'unit' => '%',
                'year' => 2025,
                'source' => 'BPS',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'region_code' => '0002',
                'region_name' => 'Test Region 2',
                'indicator' => 'kemiskinan',
                'value' => 9.0,
                'unit' => '%',
                'year' => 2025,
                'source' => 'BPS',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $this->getJson('/api/stats/landing')
            ->assertOk()
            ->assertJsonStructure([
                'total_members',
                'total_votes_cast',
                'total_quizzes_completed',
                'poverty_rate',
                'active_votes',
            ])
            ->assertJsonPath('total_members', 2)
            ->assertJsonPath('total_votes_cast', 7)
            ->assertJsonPath('total_quizzes_completed', 2)
            ->assertJsonPath('poverty_rate', 8.5)
            ->assertJsonPath('active_votes', 1);
    }

    public function test_public_landing_stats_use_poverty_fallback_when_data_is_empty(): void
    {
        $this->getJson('/api/stats/landing')
            ->assertOk()
            ->assertJsonStructure([
                'total_members',
                'total_votes_cast',
                'total_quizzes_completed',
                'poverty_rate',
                'active_votes',
            ])
            ->assertJsonPath('poverty_rate', 9);
    }
}
