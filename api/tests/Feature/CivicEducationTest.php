<?php

namespace Tests\Feature;

use App\Filament\Resources\CivicQuestionResource;
use App\Models\CivicQuestion;
use App\Models\MemberReputation;
use App\Models\ReputationLedger;
use App\Models\User;
use App\Models\UserQuestionSchedule;
use Database\Seeders\CivicQuestionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CivicEducationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_overview_includes_digest_and_does_not_expose_preview_answer_keys(): void
    {
        $question = $this->createQuestion();

        $this->getJson('/api/education/overview')
            ->assertOk()
            ->assertJsonPath('daily_digest.featured_question.id', $question->id)
            ->assertJsonPath('distribution.hak_konstitusional.count', 1)
            ->assertJsonPath('distribution.hak_konstitusional.mastery_points', 5)
            ->assertJsonCount(1, 'preview_questions')
            ->assertJsonMissingPath('preview_questions.0.correct_option')
            ->assertJsonMissingPath('daily_digest.featured_question.correct_option');
    }

    public function test_member_session_prioritizes_due_questions_then_adds_unseen_questions(): void
    {
        $user = User::factory()->create(['role' => 'member']);
        $due = $this->createQuestion(['prompt' => 'Soal jatuh tempo']);
        $notDue = $this->createQuestion(['prompt' => 'Soal belum jatuh tempo']);
        $new = $this->createQuestion(['prompt' => 'Soal baru']);
        UserQuestionSchedule::query()->create([
            'user_id' => $user->id,
            'civic_question_id' => $due->id,
            'next_review_at' => now()->subMinute(),
        ]);
        UserQuestionSchedule::query()->create([
            'user_id' => $user->id,
            'civic_question_id' => $notDue->id,
            'next_review_at' => now()->addDay(),
        ]);
        Sanctum::actingAs($user);

        $this->getJson('/api/education/session')
            ->assertOk()
            ->assertJsonPath('questions.0.id', $due->id)
            ->assertJsonPath('questions.0.is_due', true)
            ->assertJsonPath('questions.1.id', $new->id)
            ->assertJsonPath('questions.1.is_due', false)
            ->assertJsonCount(2, 'questions');
    }

    public function test_correct_member_answer_adds_mastery_once_even_for_later_reviews(): void
    {
        $user = User::factory()->create(['role' => 'member']);
        $question = $this->createQuestion(['correct_option' => 'B']);
        Sanctum::actingAs($user);
        $requestId = (string) Str::uuid();
        $payload = [
            'request_id' => $requestId,
            'question_id' => $question->id,
            'selected_option' => 'B',
            'confidence' => 'yakin',
        ];

        $this->postJson('/api/education/answers', $payload)
            ->assertOk()
            ->assertJsonPath('correct', true)
            ->assertJsonPath('correct_option', 'B')
            ->assertJsonPath('mastery_awarded', true)
            ->assertJsonPath('points_delta', 5)
            ->assertJsonPath('updated_reputation.mastery_scores.hak_konstitusional', 5);
        $this->postJson('/api/education/answers', $payload)
            ->assertOk()
            ->assertJsonPath('mastery_awarded', true)
            ->assertJsonPath('points_delta', 5);
        $this->postJson('/api/education/answers', [
            ...$payload,
            'request_id' => (string) Str::uuid(),
        ])
            ->assertOk()
            ->assertJsonPath('correct', true)
            ->assertJsonPath('mastery_awarded', false)
            ->assertJsonPath('points_delta', 0);

        $this->assertSame(1, ReputationLedger::query()->where('user_id', $user->id)->count());
        $this->assertSame(5, MemberReputation::query()->where('user_id', $user->id)->firstOrFail()->mastery_scores['hak_konstitusional']);
        $this->assertDatabaseHas('user_question_schedules', [
            'user_id' => $user->id,
            'civic_question_id' => $question->id,
            'mastery_awarded' => true,
            'repetitions' => 2,
            'interval_days' => 6,
            'ease_factor' => 2.70,
        ]);
    }

    public function test_answer_request_id_returns_same_response_on_retry_and_conflicts_on_changed_payload(): void
    {
        $user = User::factory()->create(['role' => 'member']);
        $question = $this->createQuestion();
        Sanctum::actingAs($user);
        $payload = [
            'request_id' => (string) Str::uuid(),
            'question_id' => $question->id,
            'selected_option' => 'A',
        ];

        $first = $this->postJson('/api/education/answers', $payload)->assertOk();
        $retry = $this->postJson('/api/education/answers', $payload)->assertOk();
        $this->assertSame($first->json(), $retry->json());
        $this->postJson('/api/education/answers', [
            ...$payload,
            'selected_option' => 'B',
        ])->assertConflict();
        $this->assertSame(1, ReputationLedger::query()->where('user_id', $user->id)->count());
    }

    public function test_wrong_answer_resets_sm2_interval_and_does_not_award_points(): void
    {
        $user = User::factory()->create(['role' => 'member']);
        $question = $this->createQuestion();
        $schedule = UserQuestionSchedule::query()->create([
            'user_id' => $user->id,
            'civic_question_id' => $question->id,
            'repetitions' => 3,
            'interval_days' => 15,
            'ease_factor' => 2.50,
            'next_review_at' => now()->subMinute(),
        ]);
        Sanctum::actingAs($user);

        $this->travelTo(now()->startOfSecond());
        $this->postJson('/api/education/answers', [
            'request_id' => (string) Str::uuid(),
            'question_id' => $question->id,
            'selected_option' => 'B',
        ])
            ->assertOk()
            ->assertJsonPath('correct', false)
            ->assertJsonPath('mastery_awarded', false)
            ->assertJsonPath('points_delta', 0)
            ->assertJsonPath('next_review_at', now()->addDay()->toISOString());

        $schedule->refresh();
        $this->assertSame(0, $schedule->repetitions);
        $this->assertSame(1, $schedule->interval_days);
        $this->assertSame(0, ReputationLedger::query()->where('user_id', $user->id)->count());
    }

    public function test_guest_can_practice_without_a_saved_schedule_or_mastery_score(): void
    {
        $guest = User::factory()->create(['role' => 'guest']);
        $question = $this->createQuestion();
        Sanctum::actingAs($guest);

        $this->postJson('/api/education/answers', [
            'request_id' => (string) Str::uuid(),
            'question_id' => $question->id,
            'selected_option' => 'A',
        ])
            ->assertOk()
            ->assertJsonPath('correct', true)
            ->assertJsonPath('points_delta', 0)
            ->assertJsonPath('updated_reputation', null);

        $this->assertDatabaseCount('user_question_schedules', 0);
        $this->assertDatabaseCount('reputation_ledgers', 0);
    }

    public function test_civic_question_resource_is_restricted_to_editorial_roles(): void
    {
        $ordinaryMember = User::factory()->create(['role' => 'member']);
        $this->actingAs($ordinaryMember);
        $this->assertFalse(CivicQuestionResource::canViewAny());
        $this->assertFalse(CivicQuestionResource::canView(new CivicQuestion));

        foreach (['panelis', 'pengurus', 'super_admin'] as $roleName) {
            $user = User::factory()->create(['role' => 'member']);
            Role::findOrCreate($roleName, 'web');
            $user->assignRole($roleName);
            $this->actingAs($user);
            $this->assertTrue(CivicQuestionResource::canViewAny());
            $this->assertTrue(CivicQuestionResource::canView(new CivicQuestion));
            $this->assertTrue(CivicQuestionResource::canCreate());
            $this->assertTrue(CivicQuestionResource::canEdit(new CivicQuestion));
        }
    }

    public function test_seeder_publishes_one_hundred_questions_across_all_domains_and_levels(): void
    {
        $this->seed(CivicQuestionSeeder::class);
        $this->seed(CivicQuestionSeeder::class);

        $this->assertDatabaseCount('civic_questions', 100);
        foreach (['hak_konstitusional', 'alasan_bernegara', 'keuangan_negara', 'hukum', 'politik_bernegara'] as $domain) {
            $this->assertSame(20, CivicQuestion::query()->where('domain', $domain)->count());
        }
        $this->assertSame([1, 2], CivicQuestion::query()->distinct()->orderBy('mastery_level')->pluck('mastery_level')->all());
        $this->assertSame(100, CivicQuestion::query()->where('source_citation', '!=', '')->count());

        foreach (CivicQuestion::query()->get() as $question) {
            $this->assertCount(4, $question->options);
            $this->assertContains($question->correct_option, array_column($question->options, 'key'));
            preg_match_all('/[.!?](?=\s+[A-Z“"]|$)/u', trim($question->explanation), $sentences);
            $this->assertLessThanOrEqual(2, count($sentences[0]), $question->prompt);
        }
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createQuestion(array $overrides = []): CivicQuestion
    {
        return CivicQuestion::query()->create([
            'domain' => 'hak_konstitusional',
            'mastery_level' => 1,
            'prompt' => 'Skenario kewargaan '.Str::uuid(),
            'options' => [
                ['key' => 'A', 'text' => 'Jawaban benar'],
                ['key' => 'B', 'text' => 'Pilihan kedua'],
                ['key' => 'C', 'text' => 'Pilihan ketiga'],
                ['key' => 'D', 'text' => 'Pilihan keempat'],
            ],
            'correct_option' => 'A',
            'explanation' => 'Penjelasan rasional.',
            'source_citation' => 'Pasal 28D ayat (1) UUD NRI 1945',
            'review_status' => 'published',
            'mastery_points' => 5,
            ...$overrides,
        ]);
    }
}
