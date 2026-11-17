<?php

namespace Tests\Feature;

use App\Models\MemberReputation;
use App\Models\User;
use App\Services\ReputationService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReputationTest extends TestCase
{
    use RefreshDatabase;

    public function test_correct_quiz_answer_awards_points_once_per_request(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $this->createItem('QUIZ-001', [
            'type' => 'multiple-choice',
            'options' => ['First', 'Second'],
            'answer' => 0,
        ]);
        $payload = [
            'item_id' => 'QUIZ-001',
            'selected_option' => 0,
            'request_id' => 'a27fdc51-8b86-49b0-a185-42cab168a876',
        ];

        $this->postJson('/api/answer', $payload)->assertOk();
        $this->postJson('/api/answer', $payload)->assertOk();
        $this->postJson('/api/answer', [
            ...$payload,
            'selected_option' => 0,
            'request_id' => 'b82feb2e-6b98-49a7-b607-70d59cb9f248',
        ])->assertConflict();

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

    public function test_verified_contributions_are_idempotent_equal_weight_and_separate_from_mastery(): void
    {
        $member = User::factory()->create(['role' => 'member', 'is_verified' => true]);
        $panelist = User::factory()->create();
        Role::findOrCreate('panelis', 'web');
        $panelist->assignRole('panelis');
        Sanctum::actingAs($panelist);

        $fieldContribution = [
            'request_id' => (string) Str::uuid(),
            'user_id' => $member->id,
            'category' => 'lapangan',
            'source_type' => 'event',
            'source_id' => (string) Str::uuid(),
            'reason' => 'Kehadiran terverifikasi pada kegiatan warga.',
        ];
        $this->postJson('/api/reputation/contributions', $fieldContribution)
            ->assertCreated()
            ->assertJsonPath('participation_score', 1);
        $this->postJson('/api/reputation/contributions', $fieldContribution)
            ->assertOk()
            ->assertJsonPath('status', 'already_processed');

        $this->postJson('/api/reputation/contributions', [
            ...$fieldContribution,
            'request_id' => (string) Str::uuid(),
            'category' => 'riset',
            'source_id' => (string) Str::uuid(),
            'reason' => 'Riset asinkron yang telah ditinjau.',
        ])->assertCreated()
            ->assertJsonPath('participation_score', 2);

        foreach (['bantuan_hukum', 'logistik', 'editorial'] as $category) {
            $this->postJson('/api/reputation/contributions', [
                ...$fieldContribution,
                'request_id' => (string) Str::uuid(),
                'category' => $category,
                'source_id' => (string) Str::uuid(),
                'reason' => 'Kontribusi non-fisik yang telah ditinjau.',
            ])->assertCreated();
        }

        $profile = MemberReputation::query()->where('user_id', $member->id)->firstOrFail();
        $this->assertSame(array_fill_keys(ReputationService::DOMAINS, 0), $profile->mastery_scores);
        $this->assertSame(5, $profile->participation_score);
        $this->assertSame(0, $member->fresh()->reputation_points);
        $this->assertDatabaseCount('reputation_ledgers', 5);

        $this->getJson('/api/reputation/leaderboard-stats')
            ->assertOk()
            ->assertJsonPath('members_count', 1)
            ->assertJsonPath('contributions.lapangan', 1)
            ->assertJsonPath('contributions.non_fisik', 4)
            ->assertJsonPath('contributions.total', 5)
            ->assertJsonMissingPath('user_id')
            ->assertJsonMissingPath('email');
    }

    public function test_reused_contribution_request_id_with_different_data_is_rejected(): void
    {
        $member = User::factory()->create(['role' => 'member', 'is_verified' => true]);
        $panelist = User::factory()->create();
        Role::findOrCreate('panelis', 'web');
        $panelist->assignRole('panelis');
        Sanctum::actingAs($panelist);

        $requestId = (string) Str::uuid();
        $payload = [
            'request_id' => $requestId,
            'user_id' => $member->id,
            'category' => 'lapangan',
            'source_type' => 'event',
            'source_id' => (string) Str::uuid(),
            'reason' => 'Kontribusi terverifikasi.',
        ];

        $this->postJson('/api/reputation/contributions', $payload)->assertCreated();
        $this->postJson('/api/reputation/contributions', [
            ...$payload,
            'reason' => 'Alasan berbeda.',
        ])->assertConflict();

        $this->assertDatabaseCount('reputation_ledgers', 1);
    }

    public function test_mastery_levels_use_absolute_thresholds_and_ledger_hashes_are_immutable(): void
    {
        $member = User::factory()->create();
        $reputation = app(ReputationService::class);

        foreach (ReputationService::DOMAINS as $domain) {
            $reputation->record([
                'user_id' => $member->id,
                'score_type' => 'mastery',
                'domain' => $domain,
                'source_type' => 'assessment',
                'source_id' => (string) Str::uuid(),
                'delta' => 79,
                'reason' => 'Hasil asesmen domain.',
                'request_id' => (string) Str::uuid(),
            ]);
        }

        $profile = $reputation->recalculate($member->id);
        $this->assertSame(3, $profile->overall_mastery_level);
        $this->assertFalse($profile->eligible_roles['bakal_caleg']);

        foreach (ReputationService::DOMAINS as $domain) {
            $reputation->record([
                'user_id' => $member->id,
                'score_type' => 'mastery',
                'domain' => $domain,
                'source_type' => 'assessment',
                'source_id' => (string) Str::uuid(),
                'delta' => 1,
                'reason' => 'Koreksi hasil asesmen.',
                'request_id' => (string) Str::uuid(),
            ]);
        }

        $profile = $reputation->recalculate($member->id);
        $this->assertSame(4, $profile->overall_mastery_level);
        $this->assertSame(80, $profile->mastery_scores['hukum']);
        $this->assertTrue($profile->eligible_roles['panel_audit']);
        $this->assertTrue($profile->eligible_roles['panel_etik']);
        $this->assertTrue($profile->eligible_roles['bakal_caleg']);
        $this->assertSame(0, $profile->participation_score);
        $this->assertTrue($reputation->verifyLedger($member->id));

        $this->expectException(QueryException::class);
        DB::table('reputation_ledgers')->where('user_id', $member->id)->update(['reason' => 'Tried to edit']);
    }

    public function test_member_reputation_endpoints_require_authentication_and_contribution_requires_role(): void
    {
        $this->getJson('/api/reputation/me')->assertUnauthorized();

        $member = User::factory()->create();
        Sanctum::actingAs($member);
        $this->getJson('/api/reputation/me')
            ->assertOk()
            ->assertJsonPath('mastery_scores.hak_konstitusional', 0)
            ->assertJsonPath('overall_mastery_level', 1)
            ->assertJsonPath('participation_score', 0)
            ->assertJsonCount(0, 'ledger.data');

        $this->postJson('/api/reputation/contributions', [
            'request_id' => (string) Str::uuid(),
            'user_id' => $member->id,
            'category' => 'lapangan',
            'source_type' => 'event',
            'source_id' => (string) Str::uuid(),
            'reason' => 'Kontribusi terverifikasi.',
        ])->assertForbidden();
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
