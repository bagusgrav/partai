<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReviewControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_due_items_with_decoded_payloads(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        DB::table('items')->insert([
            [
                'id' => 'DUE-001',
                'domain' => 'civics',
                'level' => 'basic',
                'payload' => '{"prompt":"Due","answer":0,"explanation":"Private"}',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'FUTURE-001',
                'domain' => 'civics',
                'level' => 'basic',
                'payload' => '{"prompt":"Future"}',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'ESSAY-001',
                'domain' => 'civics',
                'level' => 'apply',
                'payload' => '{"type":"essay","question":"Essay"}',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
        DB::table('user_item_states')->insert([
            'user_id' => $user->id,
            'item_id' => 'FUTURE-001',
            'reps' => 1,
            'interval_d' => 1,
            'ease' => 2.5,
            'next_review' => now()->addDay(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->getJson('/api/review')
            ->assertOk()
            ->assertJsonCount(1, 'items')
            ->assertJsonPath('items.0.id', 'DUE-001')
            ->assertJsonPath('items.0.payload.prompt', 'Due')
            ->assertJsonMissingPath('items.0.payload.answer')
            ->assertJsonMissingPath('items.0.payload.explanation');
    }

    public function test_answer_processes_request_id_only_once(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        DB::table('items')->insert([
            'id' => 'KEU-KENALI-001',
            'domain' => 'civics',
            'level' => 'basic',
            'payload' => '{"options":["First","Second"],"answer":0,"explanation":"<p>Answer</p>"}',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $payload = [
            'item_id' => 'KEU-KENALI-001',
            'selected_option' => 0,
            'request_id' => '0f65529b-d69c-4980-a4b4-541e6b1a2bab',
        ];

        $this->postJson('/api/answer', $payload)
            ->assertOk()
            ->assertJsonPath('status', 'processed')
            ->assertJsonPath('data.correct', true)
            ->assertJsonPath('data.correct_option', 0)
            ->assertJsonPath('data.explanation', 'Answer');

        $this->postJson('/api/answer', $payload)
            ->assertOk()
            ->assertJsonPath('status', 'already_processed')
            ->assertJsonPath('data.correct', true)
            ->assertJsonPath('data.explanation', 'Answer')
            ->assertJsonPath('data.state.reps', 1)
            ->assertJsonPath('data.state.interval_d', 1);

        $this->postJson('/api/answer', [...$payload, 'selected_option' => 1])
            ->assertConflict();

        $this->assertDatabaseCount('reviews', 1);
        $this->assertDatabaseHas('reviews', [
            'request_id' => $payload['request_id'],
            'quality' => 4,
        ]);
    }

    public function test_quiz_reputation_is_earned_only_when_the_selected_option_is_correct(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        DB::table('items')->insert([
            'id' => 'KEU-KENALI-002',
            'domain' => 'civics',
            'level' => 'basic',
            'payload' => '{"options":["First","Second"],"answer":0}',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->postJson('/api/answer', [
            'item_id' => 'KEU-KENALI-002',
            'selected_option' => 1,
            'request_id' => '71644e34-1762-4c4a-95ab-c0a0cbd17065',
        ])
            ->assertOk()
            ->assertJsonPath('data.correct', false);

        $this->postJson('/api/answer', [
            'item_id' => 'KEU-KENALI-002',
            'selected_option' => 0,
            'request_id' => 'b6871d65-9ab4-45f1-b7ad-9a8f72344526',
        ])->assertConflict();

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'item_id' => 'KEU-KENALI-002',
            'correct' => false,
            'quality' => 1,
        ]);
        $this->assertDatabaseMissing('reputation_logs', ['user_id' => $user->id]);
    }
}
