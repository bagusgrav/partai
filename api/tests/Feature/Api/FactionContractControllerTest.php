<?php

namespace Tests\Feature\Api;

use App\Models\FactionContract;
use App\Models\User;
use Database\Seeders\FactionContractSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FactionContractControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_public_endpoint_lists_contracts_and_filters_by_status(): void
    {
        FactionContract::query()->create([
            'politician_name' => 'Politisi Terpenuhi',
            'title' => 'Janji terpenuhi',
            'description' => 'Detail janji yang sudah terpenuhi.',
            'status' => 'TERPENUHI',
        ]);
        FactionContract::query()->create([
            'politician_name' => 'Politisi Gagal',
            'title' => 'Janji gagal',
            'description' => 'Detail janji yang gagal.',
            'status' => 'GAGAL',
        ]);

        $this->getJson('/api/contracts')
            ->assertOk()
            ->assertJsonCount(2)
            ->assertJsonFragment(['title' => 'Janji terpenuhi'])
            ->assertJsonFragment(['title' => 'Janji gagal']);

        $this->getJson('/api/contracts?status=TERPENUHI')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonFragment(['title' => 'Janji terpenuhi'])
            ->assertJsonMissing(['title' => 'Janji gagal']);
    }

    public function test_the_public_endpoint_rejects_unknown_status_filters(): void
    {
        $this->getJson('/api/contracts?status=UNKNOWN')->assertUnprocessable();
    }

    public function test_only_pengurus_or_super_admin_can_create_and_update_contracts(): void
    {
        $payload = [
            'politician_name' => 'Politisi Uji',
            'title' => 'Janji uji',
            'description' => 'Detail janji uji.',
        ];

        $this->postJson('/api/contracts', $payload)->assertUnauthorized();

        $regularUser = User::factory()->create();
        $this->actingAs($regularUser, 'sanctum')
            ->postJson('/api/contracts', $payload)
            ->assertForbidden();

        $pengurus = User::factory()->create();
        $pengurus->assignRole(Role::create(['name' => 'pengurus', 'guard_name' => 'web']));

        $this->actingAs($pengurus, 'sanctum')
            ->postJson('/api/contracts', $payload)
            ->assertCreated()
            ->assertJsonPath('politician_name', 'Politisi Uji')
            ->assertJsonPath('status', 'BELUM_DIMULAI')
            ->assertJsonPath('id', fn (string $id): bool => (bool) preg_match(
                '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
                $id,
            ));

        $contract = FactionContract::query()->firstOrFail();
        $this->patchJson("/api/contracts/{$contract->id}", [
            'status' => 'TERPENUHI',
            'evidence_url' => 'https://example.org/bukti',
        ])
            ->assertOk()
            ->assertJsonPath('status', 'TERPENUHI')
            ->assertJsonPath('evidence_url', 'https://example.org/bukti');
    }

    public function test_contract_updates_validate_status_and_evidence_url(): void
    {
        $contract = FactionContract::query()->create([
            'politician_name' => 'Politisi Uji',
            'title' => 'Janji uji',
            'description' => 'Detail janji uji.',
        ]);
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(Role::create(['name' => 'super_admin', 'guard_name' => 'web']));

        $this->actingAs($superAdmin, 'sanctum')
            ->patchJson("/api/contracts/{$contract->id}", [
                'status' => 'INVALID',
                'evidence_url' => 'not-a-url',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['status', 'evidence_url']);
    }

    public function test_the_demo_seeder_is_repeatable(): void
    {
        $seeder = app(FactionContractSeeder::class);
        $seeder->run();
        $this->assertSame(4, FactionContract::query()->count());

        $seeder->run();
        $this->assertSame(4, FactionContract::query()->count());
    }
}
