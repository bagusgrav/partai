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
                'payload' => '{"prompt":"Due"}',
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
            ->assertJsonPath('items.0.payload.prompt', 'Due');
    }

    public function test_answer_processes_request_id_only_once(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        DB::table('items')->insert([
            'id' => 'KEU-KENALI-001',
            'domain' => 'civics',
            'level' => 'basic',
            'payload' => '{}',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $payload = [
            'item_id' => 'KEU-KENALI-001',
            'correct' => true,
            'request_id' => '0f65529b-d69c-4980-a4b4-541e6b1a2bab',
        ];

        $this->postJson('/api/answer', $payload)
            ->assertOk()
            ->assertJsonPath('status', 'processed');

        $this->postJson('/api/answer', [...$payload, 'correct' => false])
            ->assertOk()
            ->assertJsonPath('status', 'already_processed')
            ->assertJsonPath('data.state.reps', 1)
            ->assertJsonPath('data.state.interval_d', 1);

        $this->assertDatabaseCount('reviews', 1);
        $this->assertDatabaseHas('reviews', [
            'request_id' => $payload['request_id'],
            'quality' => 4,
        ]);
    }
}
