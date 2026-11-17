<?php

namespace Tests\Feature;

use App\Filament\Resources\MemberReputationResource;
use App\Filament\Resources\MemberReputationResource\Pages\ListMemberReputations;
use App\Models\MemberReputation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MemberReputationResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_reputation_resource_is_read_only_and_limited_to_filament_roles(): void
    {
        $profile = new MemberReputation;
        $this->assertFalse(MemberReputationResource::canViewAny());

        $member = User::factory()->create();
        $member->assignRole(Role::create(['name' => 'member', 'guard_name' => 'web']));
        $this->actingAs($member);

        $this->assertFalse(MemberReputationResource::canViewAny());

        foreach (['super_admin', 'pengurus', 'panelis'] as $roleName) {
            $user = User::factory()->create();
            $user->assignRole(Role::create(['name' => $roleName, 'guard_name' => 'web']));
            $this->actingAs($user);

            $this->assertTrue(MemberReputationResource::canViewAny());
            $this->assertFalse(MemberReputationResource::canCreate());
            $this->assertFalse(MemberReputationResource::canEdit($profile));
            $this->assertFalse(MemberReputationResource::canDelete($profile));
        }
    }

    public function test_panelist_correction_is_written_as_a_ledger_entry(): void
    {
        $member = User::factory()->create(['role' => 'member', 'is_verified' => true]);
        $profile = MemberReputation::query()->create([
            'user_id' => $member->id,
            'mastery_scores' => array_fill_keys([
                'hak_konstitusional',
                'alasan_bernegara',
                'keuangan_negara',
                'hukum',
                'politik_bernegara',
            ], 0),
        ]);
        $panelist = User::factory()->create();
        $panelist->assignRole(Role::create(['name' => 'panelis', 'guard_name' => 'web']));
        $this->actingAs($panelist);

        Livewire::test(ListMemberReputations::class)
            ->assertTableActionExists('correction')
            ->callTableAction('correction', $profile, [
                'score_type' => 'participation',
                'category' => 'riset',
                'delta' => 3,
                'reason' => 'Audit atas riset yang telah diverifikasi.',
            ]);

        $this->assertDatabaseHas('reputation_ledgers', [
            'user_id' => $member->id,
            'actor_id' => $panelist->id,
            'score_type' => 'participation',
            'category' => 'riset',
            'source_type' => 'manual_adjustment',
            'delta' => 3,
            'reason' => 'Audit atas riset yang telah diverifikasi.',
        ]);
        $this->assertSame(3, $profile->fresh()->participation_score);
    }
}
