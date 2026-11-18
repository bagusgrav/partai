<?php

namespace Tests\Feature;

use App\Filament\Resources\PartyFinanceEntryResource;
use App\Models\MemberReputation;
use App\Models\PartyFinanceEntry;
use App\Models\User;
use App\Services\PartyFinanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use LogicException;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Tests\TestCase;

class PartyFinanceTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'request_id' => (string) Str::uuid(),
            'entry_type' => 'inflow',
            'channel' => 'individual_donation',
            'fiscal_year' => (int) date('Y'),
            'transaction_date' => date('Y-m-d'),
            'amount_idr' => 100000,
            'donor_type' => 'individual',
            'user_id' => null,
            'contributor_display_name' => 'Sumbangan Individu',
            'identity_hash' => hash('sha256', '3271000000000001'),
            'masked_identity' => 'NIK ••••••••0001',
            'legal_declaration_accepted' => true,
            'purpose_or_program' => 'Pendidikan politik',
            'region_code' => null,
            'province_name' => null,
            'evidence_reference' => 'BANK-001',
            'compliance_status' => 'pending_audit',
            'recorded_by' => null,
            'reversal_of_id' => null,
        ], $overrides);
    }

    public function test_chain_integrity_is_valid_and_append_only_protections_work(): void
    {
        $service = app(PartyFinanceService::class);
        $first = $service->recordTransaction($this->payload())['entry'];
        $second = $service->recordTransaction($this->payload([
            'request_id' => (string) Str::uuid(),
            'amount_idr' => 200000,
            'identity_hash' => hash('sha256', '3271000000000002'),
        ]))['entry'];

        $this->assertNull($first->previous_hash);
        $this->assertSame($first->hash, $second->previous_hash);
        $this->assertTrue($service->verifyChainIntegrity()['valid']);

        $this->expectException(LogicException::class);
        $first->update(['amount_idr' => 1]);
    }

    public function test_delete_is_blocked_by_model(): void
    {
        $entry = app(PartyFinanceService::class)->recordTransaction($this->payload())['entry'];

        $this->expectException(LogicException::class);
        $entry->delete();
    }

    public function test_donation_limits_are_enforced_and_identity_is_masked(): void
    {
        $identity = '3271000000000001';
        $this->postJson('/api/finance/donations', [
            'request_id' => (string) Str::uuid(),
            'donor_type' => 'individual',
            'contributor_name' => 'Siti',
            'identity_number' => $identity,
            'amount_idr' => 1000000001,
            'transaction_date' => date('Y-m-d'),
            'evidence_reference' => 'TRX-1',
            'legal_declaration_accepted' => true,
        ])->assertUnprocessable();

        $ledger = $this->getJson('/api/finance/ledger')->assertOk();
        $this->assertStringNotContainsString($identity, $ledger->getContent());
        $this->assertStringNotContainsString('identity_hash', $ledger->getContent());
        $this->assertStringNotContainsString('"user_id"', $ledger->getContent());
    }

    public function test_corporate_limit_and_member_quota_are_enforced(): void
    {
        $service = app(PartyFinanceService::class);

        $this->expectException(ValidationException::class);
        $service->recordTransaction($this->payload([
            'donor_type' => 'corporate',
            'channel' => 'corporate_donation',
            'identity_hash' => hash('sha256', '017777777777000'),
            'masked_identity' => 'NPWP 01.•••.•••.0-000.000',
            'amount_idr' => 7500000001,
        ]));
    }

    public function test_member_quota_and_reputation_are_separate(): void
    {
        $user = User::factory()->create();
        $reputation = MemberReputation::query()->create([
            'user_id' => $user->id,
            'mastery_scores' => [],
            'overall_mastery_level' => 0,
            'participation_score' => 0,
            'eligible_roles' => [],
        ]);
        $payload = $this->payload([
            'donor_type' => 'member',
            'channel' => 'member_dues',
            'user_id' => $user->id,
            'identity_hash' => null,
            'masked_identity' => null,
            'amount_idr' => 120000001,
        ]);

        try {
            app(PartyFinanceService::class)->recordTransaction($payload);
            $this->fail('Member dues limit should reject excessive payment.');
        } catch (ValidationException) {
            $reputation->refresh();
            $this->assertSame([], $reputation->mastery_scores);
            $this->assertSame(0, $reputation->participation_score);
        }
    }

    public function test_request_id_is_idempotent_and_conflicts_on_different_payload(): void
    {
        $payload = $this->payload();
        $service = app(PartyFinanceService::class);
        $first = $service->recordTransaction($payload);
        $retry = $service->recordTransaction($payload);

        $this->assertFalse($retry['created']);
        $this->assertSame($first['entry']->id, $retry['entry']->id);

        try {
            $service->recordTransaction(array_merge($payload, ['amount_idr' => 999]));
            $this->fail('Different payload with the same request_id should conflict.');
        } catch (ConflictHttpException) {
            $this->assertTrue(true);
        }
    }

    public function test_member_dues_api_and_filament_audit_role_permissions(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/finance/my-dues', [
            'request_id' => (string) Str::uuid(),
            'amount_idr' => 50000,
            'transaction_date' => date('Y-m-d'),
            'evidence_reference' => 'BANK-DUES-1',
            'legal_declaration_accepted' => true,
        ]);
        $response->assertCreated();

        $this->getJson('/api/finance/my-dues')
            ->assertOk()
            ->assertJsonPath('annual_accumulated_idr', 50000);

        foreach (['panelis', 'pengurus', 'super_admin'] as $roleName) {
            Role::findOrCreate($roleName, 'web');
        }
        $panelis = User::factory()->create();
        $panelis->assignRole('panelis');
        $this->actingAs($panelis);

        $this->assertTrue(PartyFinanceEntryResource::canViewAny());
        $this->assertFalse(PartyFinanceEntryResource::canCreate());
        $this->assertFalse(PartyFinanceEntryResource::canDelete(new PartyFinanceEntry));
    }
}
