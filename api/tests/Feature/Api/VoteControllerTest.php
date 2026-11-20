<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class VoteControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_votes_include_options_and_exclude_votes_outside_their_time_window(): void
    {
        $this->travelTo('2026-11-16 12:00:00');
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $activeVoteId = $this->createVote();
        $this->createVoteOption($activeVoteId, 'Biru');
        $this->createVoteOption($activeVoteId, 'Merah');
        $this->createVote(['status' => 'draft']);
        $this->createVote(['starts_at' => now()->addMinute()]);
        $this->createVote(['ends_at' => now()->subMinute()]);

        $this->getJson('/api/votes/active')
            ->assertOk()
            ->assertJsonPath('can_vote', false)
            ->assertJsonCount(1, 'votes')
            ->assertJsonPath('votes.0.id', $activeVoteId)
            ->assertJsonPath('votes.0.vote_options.0.option_text', 'Biru')
            ->assertJsonPath('votes.0.vote_options.1.option_text', 'Merah');
    }

    public function test_active_votes_show_aggregated_results_only_after_the_user_has_voted(): void
    {
        $user = $this->activeMember();
        Sanctum::actingAs($user);
        $voteId = $this->createVote();
        $firstOptionId = $this->createVoteOption($voteId, 'Biru');
        $secondOptionId = $this->createVoteOption($voteId, 'Merah');

        $this->getJson('/api/votes/active')
            ->assertOk()
            ->assertJsonPath('can_vote', true)
            ->assertJsonPath('votes.0.has_voted', false)
            ->assertJsonMissingPath('votes.0.vote_options.0.votes_count');

        $this->postJson('/api/votes/submit', [
            'vote_id' => $voteId,
            'vote_option_id' => $firstOptionId,
            'request_id' => '593b8b34-5e85-427e-b979-39eb7f3e7a02',
        ])->assertCreated();

        $this->getJson('/api/votes/active')
            ->assertOk()
            ->assertJsonPath('votes.0.has_voted', true)
            ->assertJsonPath('votes.0.vote_options.0.votes_count', 1)
            ->assertJsonPath('votes.0.vote_options.1.votes_count', 0);

        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/votes/active')
            ->assertOk()
            ->assertJsonPath('can_vote', false)
            ->assertJsonPath('votes.0.has_voted', false)
            ->assertJsonMissingPath('votes.0.vote_options.0.votes_count');
    }

    public function test_valid_vote_is_recorded_once_and_request_id_replay_returns_the_same_submission(): void
    {
        $user = $this->activeMember();
        Sanctum::actingAs($user);
        $voteId = $this->createVote();
        $firstOptionId = $this->createVoteOption($voteId, 'Biru');
        $secondOptionId = $this->createVoteOption($voteId, 'Merah');
        $payload = [
            'vote_id' => $voteId,
            'vote_option_id' => $firstOptionId,
            'request_id' => '593b8b34-5e85-427e-b979-39eb7f3e7a02',
        ];

        $response = $this->postJson('/api/votes/submit', $payload)
            ->assertCreated()
            ->assertJsonPath('status', 'processed');
        $submissionId = $response->json('data.submission_id');

        $this->postJson('/api/votes/submit', [
            ...$payload,
            'vote_id' => (string) $voteId,
            'vote_option_id' => (string) $firstOptionId,
        ])
            ->assertOk()
            ->assertJsonPath('status', 'already_processed')
            ->assertJsonPath('data.submission_id', $submissionId);
        $this->postJson('/api/votes/submit', [...$payload, 'vote_option_id' => $secondOptionId])
            ->assertConflict();

        $this->assertDatabaseCount('vote_submissions', 1);
        $this->assertDatabaseHas('vote_submissions', [
            'id' => $submissionId,
            'vote_id' => $voteId,
            'vote_option_id' => $firstOptionId,
            'user_id' => $user->id,
        ]);
        $this->assertDatabaseHas('vote_options', ['id' => $firstOptionId, 'votes_count' => 1]);
        $this->assertDatabaseHas('vote_options', ['id' => $secondOptionId, 'votes_count' => 0]);
    }

    public function test_returns_409_when_a_user_submits_more_than_one_vote(): void
    {
        $user = $this->activeMember();
        Sanctum::actingAs($user);
        $voteId = $this->createVote();
        $optionId = $this->createVoteOption($voteId, 'Biru');
        $payload = [
            'vote_id' => $voteId,
            'vote_option_id' => $optionId,
            'request_id' => '593b8b34-5e85-427e-b979-39eb7f3e7a02',
        ];

        $this->postJson('/api/votes/submit', $payload)->assertCreated();
        $this->postJson('/api/votes/submit', [
            ...$payload,
            'request_id' => '45b90e84-a394-47c7-8fa4-b77a373a44ad',
        ])->assertConflict();

        $this->assertDatabaseCount('vote_submissions', 1);
        $this->assertDatabaseHas('vote_options', ['id' => $optionId, 'votes_count' => 1]);
    }

    public function test_returns_422_when_vote_is_inactive_or_option_belongs_to_another_vote(): void
    {
        $user = $this->activeMember();
        Sanctum::actingAs($user);
        $inactiveVoteId = $this->createVote(['status' => 'completed']);
        $inactiveOptionId = $this->createVoteOption($inactiveVoteId, 'Biru');
        $activeVoteId = $this->createVote();
        $otherVoteOptionId = $this->createVoteOption($this->createVote(), 'Merah');

        $this->postJson('/api/votes/submit', [
            'vote_id' => $inactiveVoteId,
            'vote_option_id' => $inactiveOptionId,
            'request_id' => '593b8b34-5e85-427e-b979-39eb7f3e7a02',
        ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Voting tidak sedang aktif.');

        $this->postJson('/api/votes/submit', [
            'vote_id' => $activeVoteId,
            'vote_option_id' => $otherVoteOptionId,
            'request_id' => '45b90e84-a394-47c7-8fa4-b77a373a44ad',
        ])->assertUnprocessable();

        $this->assertDatabaseCount('vote_submissions', 0);
        $this->assertDatabaseHas('vote_options', ['id' => $inactiveOptionId, 'votes_count' => 0]);
    }

    public function test_only_verified_members_can_submit_votes(): void
    {
        $voteId = $this->createVote();
        $optionId = $this->createVoteOption($voteId, 'Biru');

        foreach ([
            User::factory()->create(),
            User::factory()->create(['role' => 'member', 'is_verified' => false]),
            User::factory()->create(['role' => 'super_admin', 'is_verified' => true]),
        ] as $user) {
            Sanctum::actingAs($user);

            $this->postJson('/api/votes/submit', [
                'vote_id' => $voteId,
                'vote_option_id' => $optionId,
                'request_id' => '593b8b34-5e85-427e-b979-39eb7f3e7a02',
            ])
                ->assertForbidden()
                ->assertJsonPath('message', 'Hanya anggota terverifikasi yang dapat memilih.');
        }

        $this->assertDatabaseCount('vote_submissions', 0);
    }

    public function test_vote_routes_return_401_when_user_is_unauthenticated(): void
    {
        $this->getJson('/api/votes/active')->assertUnauthorized();
        $this->postJson('/api/votes/submit')->assertUnauthorized();
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createVote(array $overrides = []): int
    {
        return (int) DB::table('votes')->insertGetId([
            'title' => 'Pemilihan Warna Partai',
            'description' => 'Pilih warna partai.',
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addHour(),
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
            ...$overrides,
        ]);
    }

    private function createVoteOption(int $voteId, string $optionText): int
    {
        return (int) DB::table('vote_options')->insertGetId([
            'vote_id' => $voteId,
            'option_text' => $optionText,
            'votes_count' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function activeMember(): User
    {
        return User::factory()->create([
            'role' => 'member',
            'is_verified' => true,
        ]);
    }
}
