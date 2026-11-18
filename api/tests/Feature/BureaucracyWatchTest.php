<?php

namespace Tests\Feature;

use App\Filament\Resources\BureaucracyReportResource;
use App\Filament\Resources\PublicAgencyScorecardResource;
use App\Models\BureaucracyReport;
use App\Models\PublicAgencyScorecard;
use App\Models\User;
use App\Services\BureaucracyWatchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BureaucracyWatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_composite_score_and_tier_are_deterministic(): void
    {
        $service = app(BureaucracyWatchService::class);
        $this->assertSame([
            'bureaucracy_integrity_score' => 85.18,
            'performance_tier' => 'sangat_baik',
        ], $service->calculateScore(82.5, 340, 88));

        $this->assertSame('baik', $service->calculateScore(70, 300, 70)['performance_tier']);
        $this->assertSame('perlu_perbaikan', $service->calculateScore(55, 220, 55)['performance_tier']);
        $this->assertSame('kritis', $service->calculateScore(40, 180, 45)['performance_tier']);
    }

    public function test_scorecard_filters_search_sort_and_summary(): void
    {
        $this->createScorecard('PEMDA-01', 'Pemerintah Provinsi A', 'pemerintah_provinsi', 82, 'AA', 350, 90);
        $this->createScorecard('SAMSAT-01', 'Samsat Kota B', 'pelayanan_publik', 45, 'C', 200, 55);

        $this->getJson('/api/bureaucracy/scorecards?category=pelayanan_publik')
            ->assertOk()
            ->assertJsonCount(1, 'scorecards.data')
            ->assertJsonPath('scorecards.data.0.agency_code', 'SAMSAT-01');

        $this->getJson('/api/bureaucracy/scorecards?search=Pemerintah')
            ->assertOk()
            ->assertJsonPath('scorecards.data.0.agency_code', 'PEMDA-01');

        $this->getJson('/api/bureaucracy/scorecards?sort=score_asc')
            ->assertOk()
            ->assertJsonPath('scorecards.data.0.agency_code', 'SAMSAT-01');
    }

    public function test_report_creation_is_validated_idempotent_and_hides_secret_plaintext(): void
    {
        $requestId = (string) Str::uuid();
        $payload = [
            'request_id' => $requestId,
            'agency_name' => 'Dinas Pelayanan Contoh',
            'province_name' => 'Jawa Barat',
            'region_code' => '3273',
            'category' => 'maladministrasi',
            'title' => 'Permohonan layanan tertunda',
            'chronology' => str_repeat('Dokumen lengkap telah diajukan dan belum ditindaklanjuti.', 2),
            'incident_date' => '2026-11-17',
            'asset_identifier' => null,
            'evidence_url' => null,
        ];

        $response = $this->postJson('/api/bureaucracy/reports', $payload)
            ->assertCreated()
            ->assertJsonStructure(['ticket_code', 'reporter_secret', 'verification_status', 'created_at']);
        $secret = $response->json('reporter_secret');
        $ticket = $response->json('ticket_code');

        $report = BureaucracyReport::query()->where('ticket_code', $ticket)->firstOrFail();
        $this->assertTrue(hash_equals(hash('sha256', $secret), $report->reporter_token_hash));
        $this->assertNotSame($secret, $report->reporter_token_hash);

        $this->postJson('/api/bureaucracy/reports', $payload)
            ->assertOk()
            ->assertJsonPath('status', 'already_processed');

        $changed = [...$payload, 'title' => 'Judul berbeda'];
        $this->postJson('/api/bureaucracy/reports', $changed)
            ->assertStatus(409);

        $this->postJson('/api/bureaucracy/reports', [
            'request_id' => (string) Str::uuid(),
            'agency_name' => 'Instansi',
            'category' => 'maladministrasi',
            'title' => 'Singkat',
            'chronology' => 'pendek',
            'incident_date' => '2027-01-01',
        ])->assertUnprocessable();
    }

    public function test_public_listing_hides_submitted_and_rejected_reports(): void
    {
        $visible = $this->createReport('verified', 'Laporan terlihat');
        $this->createReport('submitted', 'Laporan rahasia');
        $this->createReport('rejected', 'Laporan ditolak');

        $this->getJson('/api/bureaucracy/reports')
            ->assertOk()
            ->assertJsonFragment(['ticket_code' => $visible->ticket_code])
            ->assertJsonMissing(['title' => 'Laporan rahasia'])
            ->assertJsonMissing(['title' => 'Laporan ditolak']);
    }

    public function test_track_requires_secret_for_raw_chronology_and_disables_cache(): void
    {
        $report = $this->createReport('submitted', 'Laporan privat');
        $secret = 'pelapor-rahasia';
        $report->reporter_token_hash = hash('sha256', $secret);
        $report->save();

        $public = $this->getJson('/api/bureaucracy/reports/track/'.$report->ticket_code);
        $public->assertOk()
            ->assertJsonMissingExact(['chronology' => $report->chronology]);
        $this->assertStringContainsString('no-store', (string) $public->headers->get('Cache-Control'));

        $this->getJson('/api/bureaucracy/reports/track/'.$report->ticket_code.'?secret='.urlencode($secret))
            ->assertOk()
            ->assertJsonPath('chronology', $report->chronology);
    }

    public function test_filament_resources_allow_the_three_roles_and_create_permissions(): void
    {
        foreach (['super_admin', 'pengurus', 'panelis'] as $roleName) {
            Role::findOrCreate($roleName, 'web');
        }
        $user = User::factory()->create();
        $user->assignRole('panelis');
        $this->actingAs($user);

        $this->assertTrue(PublicAgencyScorecardResource::canViewAny());
        $this->assertFalse(PublicAgencyScorecardResource::canCreate());
        $this->assertTrue(BureaucracyReportResource::canViewAny());
        $this->assertTrue(BureaucracyReportResource::canEdit(new BureaucracyReport));

        $admin = User::factory()->create();
        $admin->assignRole('super_admin');
        $this->actingAs($admin);
        $this->assertTrue(PublicAgencyScorecardResource::canCreate());
    }

    private function createScorecard(
        string $code,
        string $name,
        string $category,
        float $rb,
        string $grade,
        float $merit,
        float $ombudsman,
    ): PublicAgencyScorecard {
        $calculation = app(BureaucracyWatchService::class)->calculateScore($rb, $merit, $ombudsman);

        return PublicAgencyScorecard::query()->create([
            'agency_code' => $code,
            'agency_name' => $name,
            'agency_category' => $category,
            'province_name' => null,
            'region_code' => null,
            'period_year' => 2026,
            'rb_index_score' => $rb,
            'sakip_grade' => $grade,
            'merit_system_score' => $merit,
            'ombudsman_compliance_score' => $ombudsman,
            'source_citation' => 'KemenPANRB/Ombudsman/BKN 2025',
            ...$calculation,
        ]);
    }

    private function createReport(string $status, string $title): BureaucracyReport
    {
        return BureaucracyReport::query()->create([
            'ticket_code' => 'BW-2026-'.strtoupper(Str::random(8)),
            'reporter_token_hash' => hash('sha256', Str::random(32)),
            'request_id' => (string) Str::uuid(),
            'payload_hash' => hash('sha256', Str::random(32)),
            'agency_name' => 'Instansi Contoh',
            'province_name' => 'Jawa Barat',
            'region_code' => '32',
            'category' => 'maladministrasi',
            'title' => $title,
            'chronology' => str_repeat('Kronologi laporan contoh yang cukup panjang untuk pengujian publik. ', 2),
            'incident_date' => '2026-11-17',
            'verification_status' => $status,
        ]);
    }
}
