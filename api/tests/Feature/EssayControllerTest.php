<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EssayControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_submission_is_created_once_for_a_request_id(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $this->createItem('ESSAY-001', [
            'type' => 'essay',
            'question' => 'Question',
            'explanation' => '<p>Ideal answer</p><p>Second paragraph</p>',
        ]);
        $payload = [
            'item_id' => 'ESSAY-001',
            'answer_text' => 'My answer',
            'request_id' => '593b8b34-5e85-427e-b979-39eb7f3e7a02',
        ];

        $this->postJson('/api/essay/submit', $payload)
            ->assertCreated()
            ->assertJsonPath('status', 'processed')
            ->assertJsonPath('data.explanation', "Ideal answer\n\nSecond paragraph");
        $this->postJson('/api/essay/submit', $payload)
            ->assertOk()
            ->assertJsonPath('status', 'already_processed')
            ->assertJsonPath('data.explanation', "Ideal answer\n\nSecond paragraph");
        $this->postJson('/api/essay/submit', [...$payload, 'answer_text' => 'Changed answer'])
            ->assertConflict();

        $this->assertDatabaseCount('essay_submissions', 1);
        $this->assertDatabaseHas('essay_submissions', [
            'user_id' => $user->id,
            'item_id' => 'ESSAY-001',
            'answer_text' => 'My answer',
            'status' => 'pending',
        ]);
    }

    public function test_submission_rejects_non_essay_items(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $this->createItem('QUIZ-001', ['type' => 'multiple-choice', 'question' => 'Question']);

        $this->postJson('/api/essay/submit', [
            'item_id' => 'QUIZ-001',
            'answer_text' => 'My answer',
            'request_id' => '45b90e84-a394-47c7-8fa4-b77a373a44ad',
        ])->assertUnprocessable();

        $this->assertDatabaseCount('essay_submissions', 0);
    }

    public function test_pending_essay_excludes_the_current_users_submissions(): void
    {
        $author = User::factory()->create();
        $reviewer = User::factory()->create();
        $this->createItem('ESSAY-001', [
            'type' => 'essay',
            'question' => 'Question',
            'answer' => 'Private answer',
            'explanation' => 'Private until submission',
        ]);
        $ownSubmission = $this->createSubmission($author, 'ESSAY-001', 'My own answer');
        $otherSubmission = $this->createSubmission($reviewer, 'ESSAY-001', 'Another answer');
        Sanctum::actingAs($author);

        $this->getJson('/api/essay/review')
            ->assertOk()
            ->assertJsonPath('submission.id', $otherSubmission)
            ->assertJsonPath('submission.answer_text', 'Another answer')
            ->assertJsonPath('submission.item.payload.question', 'Question')
            ->assertJsonMissingPath('submission.item.payload.answer')
            ->assertJsonMissingPath('submission.item.payload.explanation');

        $this->assertDatabaseHas('essay_submissions', ['id' => $ownSubmission]);
    }

    public function test_question_returns_an_unanswered_essay_item(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $this->createItem('ESSAY-001', [
            'type' => 'essay',
            'question' => 'Question',
            'answer' => 'Private answer',
            'explanation' => 'Private until submission',
            'reference_answer' => 'Alternate private answer',
        ]);
        $this->createItem('QUIZ-001', ['type' => 'multiple-choice', 'question' => 'Quiz']);

        $this->getJson('/api/essay/question')
            ->assertOk()
            ->assertJsonPath('item.id', 'ESSAY-001')
            ->assertJsonPath('item.payload.type', 'essay')
            ->assertJsonMissingPath('item.payload.answer')
            ->assertJsonMissingPath('item.payload.explanation')
            ->assertJsonMissingPath('item.payload.reference_answer');
    }

    public function test_submission_cannot_be_reviewed_by_its_author_and_reviews_are_idempotent(): void
    {
        $author = User::factory()->create();
        $reviewer = User::factory()->create();
        $this->createItem('ESSAY-001', ['type' => 'essay', 'question' => 'Question']);
        $submissionId = $this->createSubmission($author, 'ESSAY-001', 'My answer');
        $payload = [
            'submission_id' => $submissionId,
            'score' => 90,
            'feedback' => 'Clear reasoning.',
            'request_id' => 'a27fdc51-8b86-49b0-a185-42cab168a876',
        ];

        Sanctum::actingAs($author);
        $this->postJson('/api/essay/review', $payload)->assertForbidden();

        Sanctum::actingAs($reviewer);
        $this->postJson('/api/essay/review', $payload)
            ->assertOk()
            ->assertJsonPath('status', 'processed');
        $this->postJson('/api/essay/review', $payload)
            ->assertOk()
            ->assertJsonPath('status', 'already_processed');
        $this->postJson('/api/essay/review', [...$payload, 'score' => 70])
            ->assertConflict();

        $this->assertDatabaseHas('essay_submissions', [
            'id' => $submissionId,
            'status' => 'reviewed',
            'score' => 90,
            'reviewer_id' => $reviewer->id,
            'feedback' => 'Clear reasoning.',
            'review_request_id' => $payload['request_id'],
        ]);
    }

    public function test_essay_routes_require_authentication(): void
    {
        $this->getJson('/api/essay/review')->assertUnauthorized();
        $this->postJson('/api/essay/submit')->assertUnauthorized();
        $this->postJson('/api/essay/review')->assertUnauthorized();
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

    private function createSubmission(User $user, string $itemId, string $answer): int
    {
        return (int) DB::table('essay_submissions')->insertGetId([
            'user_id' => $user->id,
            'item_id' => $itemId,
            'answer_text' => $answer,
            'status' => 'pending',
            'request_id' => fake()->uuid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
