<?php

namespace Tests\Feature;

use App\Filament\Resources\IdentityProposalResource;
use App\Filament\Resources\IdentityProposalResource\Pages\ListIdentityProposals;
use App\Models\IdentityProposal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class IdentityProposalTest extends TestCase
{
    use RefreshDatabase;

    public function test_identity_proposal_submission_requires_authentication(): void
    {
        $this->postJson('/api/identity-proposals', $this->proposalPayload())
            ->assertUnauthorized();
    }

    public function test_unverified_user_cannot_submit_an_identity_proposal(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/identity-proposals', $this->proposalPayload())
            ->assertForbidden()
            ->assertJsonPath('code', 'nik_not_verified');

        $this->assertDatabaseCount('identity_proposals', 0);
    }

    public function test_verified_user_can_submit_an_identity_proposal(): void
    {
        $user = User::factory()->create(['nik_verified_at' => now()]);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/identity-proposals', $this->proposalPayload())
            ->assertCreated()
            ->assertJsonPath('proposal.status', 'pending');

        $this->assertDatabaseHas('identity_proposals', [
            'id' => $response->json('proposal.id'),
            'user_id' => $user->id,
            'proposed_name' => 'Partai Warga',
            'proposed_color' => '#123456',
            'proposed_logo_desc' => 'Logo berbentuk lingkaran dengan padi dan kapas.',
            'status' => 'pending',
        ]);
    }

    public function test_submission_validates_all_identity_fields(): void
    {
        Sanctum::actingAs(User::factory()->create(['nik_verified_at' => now()]));

        $this->postJson('/api/identity-proposals', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'proposed_name',
                'proposed_color',
                'proposed_logo_desc',
            ]);
    }

    public function test_public_index_returns_only_approved_proposals_without_submitter_ids(): void
    {
        $user = User::factory()->create(['nik_verified_at' => now()]);
        $approved = $this->createProposal($user, 'approved');
        $this->createProposal($user, 'pending');
        $this->createProposal($user, 'rejected');

        $this->getJson('/api/identity-proposals')
            ->assertOk()
            ->assertJsonCount(1, 'proposals')
            ->assertJsonPath('proposals.0.id', $approved->id)
            ->assertJsonPath('proposals.0.proposed_name', $approved->proposed_name)
            ->assertJsonMissingPath('proposals.0.user_id');
    }

    public function test_only_super_admin_can_access_and_review_identity_proposals(): void
    {
        $proposal = $this->createProposal(User::factory()->create(), 'pending');

        $this->assertFalse(IdentityProposalResource::canViewAny());

        $pengurus = User::factory()->create();
        $pengurus->assignRole(Role::create(['name' => 'pengurus', 'guard_name' => 'web']));
        $this->actingAs($pengurus);
        $this->assertFalse(IdentityProposalResource::canViewAny());

        $admin = User::factory()->create();
        $admin->assignRole(Role::create(['name' => 'super_admin', 'guard_name' => 'web']));
        $this->actingAs($admin);
        $this->assertTrue(IdentityProposalResource::canViewAny());
        $this->assertFalse(IdentityProposalResource::canCreate());
        $this->assertFalse(IdentityProposalResource::canEdit($proposal));
        $this->assertFalse(IdentityProposalResource::canDelete($proposal));

        Livewire::test(ListIdentityProposals::class)
            ->callTableAction('approve', $proposal)
            ->assertHasNoTableActionErrors();
        $this->assertSame('approved', $proposal->fresh()->status);

        $rejected = $this->createProposal(User::factory()->create(), 'pending');
        Livewire::test(ListIdentityProposals::class)
            ->callTableAction('reject', $rejected)
            ->assertHasNoTableActionErrors();
        $this->assertSame('rejected', $rejected->fresh()->status);
    }

    private function proposalPayload(): array
    {
        return [
            'proposed_name' => 'Partai Warga',
            'proposed_color' => '#123456',
            'proposed_logo_desc' => 'Logo berbentuk lingkaran dengan padi dan kapas.',
        ];
    }

    private function createProposal(User $user, string $status): IdentityProposal
    {
        return IdentityProposal::query()->create([
            'user_id' => $user->id,
            ...$this->proposalPayload(),
            'status' => $status,
        ]);
    }
}
