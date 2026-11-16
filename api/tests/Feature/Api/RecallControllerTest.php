<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RecallControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_recall_request_is_idempotent_and_rejects_reuse_with_different_data(): void
    {
        $requester = $this->activeMember();
        $target = $this->activeMember();
        Sanctum::actingAs($requester);
        $payload = [
            'target_user_id' => $target->id,
            'reason' => 'Tidak memenuhi kontrak faksi.',
            'evidence_url' => 'https://example.test/bukti',
            'request_id' => 'd97a75ea-8422-422b-9eb3-4f34d2eb95e0',
        ];

        $response = $this->postJson('/api/recall/request', $payload)
            ->assertCreated()
            ->assertJsonPath('status', 'processed')
            ->assertJsonPath('recall.status', 'voting');
        $recallId = $response->json('recall.id');

        $this->postJson('/api/recall/request', $payload)
            ->assertOk()
            ->assertJsonPath('status', 'already_processed')
            ->assertJsonPath('recall.id', $recallId);
        $this->postJson('/api/recall/request', [...$payload, 'reason' => 'Alasan berbeda.'])
            ->assertConflict();
        $this->postJson('/api/recall/request', [
            ...$payload,
            'evidence_url' => 'javascript:alert(1)',
            'request_id' => '45b90e84-a394-47c7-8fa4-b77a373a44ad',
        ])->assertUnprocessable()->assertJsonValidationErrors(['evidence_url']);

        $this->assertDatabaseCount('recall_requests', 1);
        $this->assertDatabaseHas('recall_requests', [
            'id' => $recallId,
            'target_user_id' => $target->id,
            'status' => 'voting',
        ]);
    }

    public function test_two_thirds_of_active_members_approve_recall_and_demote_target(): void
    {
        $requester = $this->activeMember();
        $target = $this->activeMember();
        $secondVoter = $this->activeMember();
        Sanctum::actingAs($requester);
        $request = $this->postJson('/api/recall/request', [
            'target_user_id' => $target->id,
            'reason' => 'Pelanggaran etik.',
            'request_id' => 'd97a75ea-8422-422b-9eb3-4f34d2eb95e0',
        ])->assertCreated();
        $recallId = $request->json('recall.id');
        $firstVote = [
            'recall_request_id' => $recallId,
            'vote' => true,
            'request_id' => '593b8b34-5e85-427e-b979-39eb7f3e7a02',
        ];

        $this->postJson('/api/recall/vote', $firstVote)
            ->assertCreated()
            ->assertJsonPath('data.recall_status', 'voting')
            ->assertJsonPath('data.votes_required', 2);

        Sanctum::actingAs($secondVoter);
        $this->getJson('/api/recall/active')
            ->assertOk()
            ->assertJsonCount(1, 'recalls')
            ->assertJsonPath('recalls.0.agree_count', 1)
            ->assertJsonPath('recalls.0.has_voted', false);

        $secondVote = [
            'recall_request_id' => $recallId,
            'vote' => true,
            'request_id' => '45b90e84-a394-47c7-8fa4-b77a373a44ad',
        ];
        $this->postJson('/api/recall/vote', $secondVote)
            ->assertCreated()
            ->assertJsonPath('data.recall_status', 'approved')
            ->assertJsonPath('data.agree_count', 2);
        $this->postJson('/api/recall/vote', $secondVote)
            ->assertOk()
            ->assertJsonPath('status', 'already_processed');
        $this->postJson('/api/recall/vote', [...$secondVote, 'vote' => false])
            ->assertConflict();

        $this->assertDatabaseHas('recall_requests', ['id' => $recallId, 'status' => 'approved']);
        $this->assertDatabaseHas('users', ['id' => $target->id, 'role' => 'guest']);
        $this->assertDatabaseCount('recall_votes', 2);
    }

    public function test_reject_votes_are_counted_without_approving_the_recall(): void
    {
        $requester = $this->activeMember();
        $target = $this->activeMember();
        $voter = $this->activeMember();
        Sanctum::actingAs($requester);
        $recallId = $this->postJson('/api/recall/request', [
            'target_user_id' => $target->id,
            'reason' => 'Pelanggaran etik.',
            'request_id' => 'd97a75ea-8422-422b-9eb3-4f34d2eb95e0',
        ])->assertCreated()->json('recall.id');

        Sanctum::actingAs($voter);
        $this->postJson('/api/recall/vote', [
            'recall_request_id' => $recallId,
            'vote' => false,
            'request_id' => '593b8b34-5e85-427e-b979-39eb7f3e7a02',
        ])->assertCreated()->assertJsonPath('data.recall_status', 'voting');

        $this->getJson('/api/recall/active')
            ->assertOk()
            ->assertJsonPath('recalls.0.reject_count', 1)
            ->assertJsonPath('recalls.0.has_voted', true);
        $this->assertDatabaseHas('users', ['id' => $target->id, 'role' => 'member']);
    }

    public function test_guest_users_cannot_submit_recall_requests_or_votes(): void
    {
        $guest = User::factory()->create();
        $target = $this->activeMember();
        $recallId = (string) Str::uuid();
        DB::table('recall_requests')->insert([
            'id' => $recallId,
            'target_user_id' => $target->id,
            'reason' => 'Pelanggaran etik.',
            'status' => 'voting',
            'request_id' => 'd97a75ea-8422-422b-9eb3-4f34d2eb95e0',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        Sanctum::actingAs($guest);

        $this->postJson('/api/recall/request', [
            'target_user_id' => $target->id,
            'reason' => 'Alasan.',
            'request_id' => '45b90e84-a394-47c7-8fa4-b77a373a44ad',
        ])->assertForbidden();
        $this->postJson('/api/recall/vote', [
            'recall_request_id' => $recallId,
            'vote' => true,
            'request_id' => '593b8b34-5e85-427e-b979-39eb7f3e7a02',
        ])->assertForbidden();
    }

    private function activeMember(): User
    {
        return User::factory()->create([
            'role' => 'member',
            'is_verified' => true,
        ]);
    }
}
