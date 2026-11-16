<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\ReputationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReputationTest extends TestCase
{
    use RefreshDatabase;

    public function test_correct_quiz_answer_awards_points_once_per_request(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $this->createItem('QUIZ-001', ['type' => 'multiple-choice']);
        $payload = [
            'item_id' => 'QUIZ-001',
            'correct' => true,
            'request_id' => 'a27fdc51-8b86-49b0-a185-42cab168a876',
        ];

        $this->postJson('/api/answer', $payload)->assertOk();
        $this->postJson('/api/answer', $payload)->assertOk();
        $this->postJson('/api/answer', [
            ...$payload,
            'correct' => false,
            'request_id' => 'b82feb2e-6b98-49a7-b607-70d59cb9f248',
        ])->assertOk();

        $this->assertDatabaseCount('reputation_logs', 1);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'reputation_points' => 10,
            'reputation_level' => 'Warga Baru',
        ]);
    }

    public function test_essay_pass_awards_the_author_but_a_failing_score_does_not(): void
    {
        $passingAuthor = User::factory()->create();
        $failingAuthor = User::factory()->create();
        $reviewer = User::factory()->create();
        $this->createItem('ESSAY-001', ['type' => 'essay']);
        $passingSubmission = $this->createEssaySubmission($passingAuthor, 'ESSAY-001');
        $failingSubmission = $this->createEssaySubmission($failingAuthor, 'ESSAY-001');
        Sanctum::actingAs($reviewer);

        $this->postJson('/api/essay/review', [
            'submission_id' => $failingSubmission,
            'score' => 69,
            'request_id' => '60400c05-0c55-43a8-88c3-f5fead768e57',
        ])->assertOk();
        $this->postJson('/api/essay/review', [
            'submission_id' => $passingSubmission,
            'score' => 70,
            'request_id' => '723721b4-9c2c-4c62-8353-5ffdb3a4719c',
        ])->assertOk();

        $this->assertDatabaseHas('users', [
            'id' => $passingAuthor->id,
            'reputation_points' => 50,
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $failingAuthor->id,
            'reputation_points' => 0,
        ]);
        $this->assertDatabaseCount('reputation_logs', 1);
    }

    public function test_cross_cluster_attestations_award_jury_and_validated_attendance_points(): void
    {
        $coordinator = User::factory()->create();
        $claimant = User::factory()->create();
        $firstValidator = User::factory()->create();
        $secondValidator = User::factory()->create();
        DB::table('users')->where('id', $claimant->id)->update(['cluster_id' => 'cluster-a']);
        DB::table('users')->where('id', $firstValidator->id)->update(['cluster_id' => 'cluster-b']);
        DB::table('users')->where('id', $secondValidator->id)->update(['cluster_id' => 'cluster-c']);

        $eventId = (string) Str::uuid();
        DB::table('events')->insert([
            'id' => $eventId,
            'title' => 'Aksi warga',
            'description' => 'Kegiatan lapangan',
            'location_lat' => 0,
            'location_lng' => 0,
            'radius_m' => 100,
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addHour(),
            'coordinator_id' => $coordinator->id,
            'status' => 'active',
            'request_id' => (string) Str::uuid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $claimId = (string) Str::uuid();
        DB::table('attendance_claims')->insert([
            'id' => $claimId,
            'event_id' => $eventId,
            'user_id' => $claimant->id,
            'lat' => 0,
            'lng' => 0,
            'distance_m' => 0,
            'status' => 'pending',
            'request_id' => (string) Str::uuid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $firstAttestation = [
            'claim_id' => $claimId,
            'request_id' => 'a27fdc51-8b86-49b0-a185-42cab168a876',
        ];

        Sanctum::actingAs($firstValidator);
        $this->postJson('/api/attendance/attest', $firstAttestation)
            ->assertCreated()
            ->assertJsonPath('claim_status', 'pending');

        Sanctum::actingAs($secondValidator);
        $this->postJson('/api/attendance/attest', [
            'claim_id' => $claimId,
            'request_id' => 'b82feb2e-6b98-49a7-b607-70d59cb9f248',
        ])->assertCreated()->assertJsonPath('claim_status', 'valid');

        Sanctum::actingAs($firstValidator);
        $this->postJson('/api/attendance/attest', $firstAttestation)->assertOk();

        $this->assertDatabaseHas('users', ['id' => $firstValidator->id, 'reputation_points' => 20]);
        $this->assertDatabaseHas('users', ['id' => $secondValidator->id, 'reputation_points' => 20]);
        $this->assertDatabaseHas('users', [
            'id' => $claimant->id,
            'reputation_points' => 100,
            'reputation_level' => 'Warga Aktif',
        ]);
        $this->assertDatabaseCount('reputation_logs', 3);
    }

    public function test_reputation_endpoint_returns_profile_and_only_the_latest_ten_logs(): void
    {
        $user = User::factory()->create();
        $reputation = app(ReputationService::class);

        for ($index = 0; $index < 12; $index++) {
            $reputation->award(
                $user->id,
                'test_activity',
                10,
                'Test activity.',
                (string) Str::uuid(),
            );
        }

        Sanctum::actingAs($user);
        $this->getJson('/api/reputation')
            ->assertOk()
            ->assertJsonPath('reputation_points', 120)
            ->assertJsonPath('reputation_level', 'Warga Aktif')
            ->assertJsonCount(10, 'logs');
        $this->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('reputation_points', 120)
            ->assertJsonPath('reputation_level', 'Warga Aktif');

    }

    public function test_reputation_endpoint_requires_authentication(): void
    {
        $this->getJson('/api/reputation')->assertUnauthorized();
    }

    public function test_reputation_levels_change_at_each_threshold(): void
    {
        $user = User::factory()->create();
        $reputation = app(ReputationService::class);

        $reputation->award($user->id, 'test', 99, 'Test', (string) Str::uuid());
        $this->assertDatabaseHas('users', ['id' => $user->id, 'reputation_level' => 'Warga Baru']);

        $reputation->award($user->id, 'test', 1, 'Test', (string) Str::uuid());
        $this->assertDatabaseHas('users', ['id' => $user->id, 'reputation_level' => 'Warga Aktif']);

        $reputation->award($user->id, 'test', 400, 'Test', (string) Str::uuid());
        $this->assertDatabaseHas('users', ['id' => $user->id, 'reputation_level' => 'Penggerak']);

        $reputation->award($user->id, 'test', 500, 'Test', (string) Str::uuid());
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'reputation_points' => 1000,
            'reputation_level' => 'Pengawal Demokrasi',
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function createItem(string $itemId, array $payload): void
    {
        DB::table('items')->insert([
            'id' => $itemId,
            'domain' => 'hukum',
            'level' => 'terapkan',
            'payload' => json_encode($payload, JSON_THROW_ON_ERROR),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createEssaySubmission(User $author, string $itemId): int
    {
        return (int) DB::table('essay_submissions')->insertGetId([
            'user_id' => $author->id,
            'item_id' => $itemId,
            'answer_text' => 'Jawaban esai.',
            'status' => 'pending',
            'request_id' => (string) Str::uuid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
