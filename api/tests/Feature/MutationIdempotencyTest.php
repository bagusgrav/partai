<?php

namespace Tests\Feature;

use App\Models\FactionContract;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MutationIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_contract_creation_replay_returns_the_existing_contract(): void
    {
        $this->actingAs($this->contractManager(), 'sanctum');
        $payload = [
            'request_id' => '8cd0e89a-8508-42df-9e84-e68145bad29a',
            'politician_name' => 'Politisi Uji',
            'title' => 'Janji pertama',
            'description' => 'Deskripsi kontrak uji.',
        ];

        $this->postJson('/api/contracts', $payload)
            ->assertCreated()
            ->assertJsonPath('title', 'Janji pertama');

        $this->postJson('/api/contracts', $payload)
            ->assertOk()
            ->assertJsonPath('status', 'already_processed')
            ->assertJsonPath('data.title', 'Janji pertama');

        $this->assertDatabaseCount('faction_contracts', 1);
        $this->assertDatabaseCount('faction_contract_mutations', 1);
    }

    public function test_contract_update_replay_does_not_apply_the_update_again(): void
    {
        $this->actingAs($this->contractManager(), 'sanctum');
        $contract = FactionContract::query()->create([
            'politician_name' => 'Politisi Uji',
            'title' => 'Janji awal',
            'description' => 'Deskripsi kontrak uji.',
        ]);
        $payload = [
            'request_id' => '3c3e3a4b-94bc-42ac-9ec8-f418b5e3a38f',
            'title' => 'Hasil pembaruan pertama',
        ];

        $this->patchJson("/api/contracts/{$contract->id}", $payload)
            ->assertOk()
            ->assertJsonPath('title', 'Hasil pembaruan pertama');

        DB::table('faction_contracts')->where('id', $contract->id)->update([
            'title' => 'Perubahan berikutnya',
        ]);

        $this->patchJson("/api/contracts/{$contract->id}", $payload)
            ->assertOk()
            ->assertJsonPath('status', 'already_processed')
            ->assertJsonPath('data.title', 'Hasil pembaruan pertama');

        $this->assertDatabaseHas('faction_contracts', [
            'id' => $contract->id,
            'title' => 'Perubahan berikutnya',
        ]);
        $this->assertDatabaseCount('faction_contract_mutations', 1);
    }

    public function test_marking_a_notification_read_is_idempotent(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');
        $notificationId = (string) Str::uuid();
        $requestId = 'c12b301e-9779-4bdb-a914-4b71a3ba20c8';

        DB::table('notifications')->insert([
            'id' => $notificationId,
            'type' => 'test',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => json_encode(['type' => 'test'], JSON_THROW_ON_ERROR),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $firstResponse = $this->postJson("/api/notifications/{$notificationId}/read", [
            'request_id' => $requestId,
        ])->assertOk()->assertJsonPath('status', 'read');
        $readAt = DB::table('notifications')->where('id', $notificationId)->value('read_at');

        $this->postJson("/api/notifications/{$notificationId}/read", [
            'request_id' => $requestId,
        ])->assertOk()
            ->assertJsonPath('status', 'already_processed')
            ->assertJsonPath('read_at', $firstResponse->json('read_at'));

        $this->assertSame(
            $readAt,
            DB::table('notifications')->where('id', $notificationId)->value('read_at'),
        );
    }

    public function test_contract_and_notification_mutations_require_uuid_request_ids(): void
    {
        $this->actingAs($this->contractManager(), 'sanctum');
        $contract = FactionContract::query()->create([
            'politician_name' => 'Politisi Uji',
            'title' => 'Janji awal',
            'description' => 'Deskripsi kontrak uji.',
        ]);

        $this->postJson('/api/contracts', [
            'politician_name' => 'Politisi Uji',
            'title' => 'Janji baru',
            'description' => 'Deskripsi kontrak uji.',
        ])->assertUnprocessable()->assertJsonValidationErrors(['request_id']);

        $this->patchJson("/api/contracts/{$contract->id}", ['title' => 'Janji baru'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['request_id']);

        $this->postJson('/api/notifications/'.Str::uuid().'/read')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['request_id']);
    }

    private function contractManager(): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::firstOrCreate([
            'name' => 'pengurus',
            'guard_name' => 'web',
        ]));

        return $user;
    }
}
