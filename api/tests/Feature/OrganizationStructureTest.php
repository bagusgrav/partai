<?php

namespace Tests\Feature;

use App\Models\MemberReputation;
use App\Models\OrganizationSeat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OrganizationStructureTest extends TestCase
{
    use RefreshDatabase;

    private function seat(array $overrides = []): OrganizationSeat
    {
        return OrganizationSeat::query()->create(array_merge([
            'seat_code' => 'TEST-PANEL-'.Str::uuid(),
            'layer' => 'peran_fungsional_sortisi',
            'title' => 'Panel Pengujian',
            'territory_level' => 'nasional',
            'answering_scope' => 'Mengajukan temuan; tidak mengubah hak suara.',
            'selection_method' => 'sortisi_acak',
            'required_mastery_level' => 3,
            'required_domain' => 'hukum',
            'required_min_participation' => 5,
            'status' => 'open',
        ], $overrides));
    }

    public function test_tree_returns_hierarchy_holders_and_responsibility_matrix(): void
    {
        $parent = $this->seat(['seat_code' => 'NATIONAL-1', 'status' => 'filled', 'holder_display_name' => 'Pimpinan Uji']);
        $this->seat(['seat_code' => 'CLUSTER-1', 'parent_id' => $parent->id, 'layer' => 'cluster_teritorial', 'territory_level' => 'kecamatan_cluster']);

        $this->getJson('/api/organization/tree')
            ->assertOk()
            ->assertJsonStructure([
                'tree',
                'seats',
                'summary',
                'holders',
                'responsibility_matrix',
            ])
            ->assertJsonPath('seats.0.answering_scope', fn ($scope) => is_string($scope));
    }

    public function test_eligibility_uses_mastery_and_participation_without_finance(): void
    {
        $user = User::factory()->create();
        MemberReputation::query()->create([
            'user_id' => $user->id,
            'mastery_scores' => [
                'hak_konstitusional' => 0,
                'alasan_bernegara' => 0,
                'keuangan_negara' => 0,
                'hukum' => 40,
                'politik_bernegara' => 0,
            ],
            'overall_mastery_level' => 2,
            'participation_score' => 3,
            'eligible_roles' => [],
        ]);
        $seat = $this->seat();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/organization/eligibility')
            ->assertOk()
            ->assertJsonPath('seats.0.eligible', false)
            ->assertJsonPath('seats.0.current_participation_score', 3);

        $profile = MemberReputation::query()->where('user_id', $user->id)->first();
        $profile->forceFill([
            'mastery_scores' => array_merge($profile->mastery_scores, ['hukum' => 60]),
            'overall_mastery_level' => 3,
            'participation_score' => 5,
        ])->save();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/organization/eligibility')
            ->assertOk()
            ->assertJsonPath('seats.0.eligible', true);
    }

    public function test_application_rejects_below_threshold_accepts_eligible_and_is_idempotent(): void
    {
        $user = User::factory()->create();
        MemberReputation::query()->create([
            'user_id' => $user->id,
            'mastery_scores' => [
                'hak_konstitusional' => 0,
                'alasan_bernegara' => 0,
                'keuangan_negara' => 0,
                'hukum' => 20,
                'politik_bernegara' => 0,
            ],
            'overall_mastery_level' => 1,
            'participation_score' => 0,
            'eligible_roles' => [],
        ]);
        $seat = $this->seat();
        $requestId = (string) Str::uuid();

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/organization/seats/{$seat->id}/apply", [
                'request_id' => $requestId,
                'statement' => 'Saya berkomitmen menjaga integritas dan menjalankan rencana kerja.',
            ])
            ->assertUnprocessable();

        $profile = MemberReputation::query()->where('user_id', $user->id)->first();
        $profile->forceFill([
            'mastery_scores' => array_merge($profile->mastery_scores, ['hukum' => 60]),
            'overall_mastery_level' => 3,
            'participation_score' => 5,
        ])->save();

        $payload = [
            'request_id' => $requestId,
            'statement' => 'Saya berkomitmen menjaga integritas dan menjalankan rencana kerja.',
        ];
        $this->actingAs($user, 'sanctum')
            ->postJson("/api/organization/seats/{$seat->id}/apply", $payload)
            ->assertCreated();

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/organization/seats/{$seat->id}/apply", $payload)
            ->assertOk();

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/organization/seats/{$seat->id}/apply", [
                'request_id' => $requestId,
                'statement' => 'Pernyataan yang berbeda tetapi menggunakan request_id yang sama.',
            ])
            ->assertStatus(409);
    }
}
