<?php

namespace Tests\Feature;

use App\Filament\Resources\RegionWelfareIndexResource;
use App\Models\EconomicIndicatorObservation;
use App\Models\Event;
use App\Models\FactionContract;
use App\Models\RegionWelfareIndex;
use App\Models\User;
use App\Models\WelfareData;
use App\Services\WelfareIndexService;
use App\Services\WelfareOpenDataAdapter;
use Database\Seeders\RegionWelfareIndexSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WelfareIndexTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_composite_score_is_deterministic_and_tiers_include_each_boundary(): void
    {
        $service = app(WelfareIndexService::class);

        foreach ([
            0 => 'rendah',
            29.99 => 'rendah',
            30 => 'sedang',
            49.99 => 'sedang',
            50 => 'tinggi',
            69.99 => 'tinggi',
            70 => 'kritis',
            100 => 'kritis',
        ] as $target => $tier) {
            $indicators = [
                'poverty_rate' => 35 * ($target / 100),
                'stunting_rate' => 45 * ($target / 100),
                'gini_ratio' => 0.25 + 0.30 * ($target / 100),
                'open_unemployment_rate' => 15 * ($target / 100),
                'disaster_risk_score' => $target,
            ];
            $first = $service->calculate($indicators);
            $second = $service->calculate($indicators);

            $this->assertSame($first, $second);
            $this->assertEquals($target, $first['ikk_score']);
            $this->assertSame($tier, $first['vulnerability_tier']);
        }
    }

    public function test_index_filters_summary_and_supported_sort_orders(): void
    {
        $this->createRegion('1101', 'Kota Rendah', 'Provinsi A', 10, 0.28, 2, 8, 10);
        $this->createRegion('2201', 'Kota Sedang', 'Provinsi B', 15, 0.34, 5, 20, 45);
        $this->createRegion('1102', 'Kota Kritis', 'Provinsi A', 30, 0.52, 12, 40, 95);

        $this->getJson('/api/welfare/ikk?province=Provinsi%20A')
            ->assertOk()
            ->assertJsonCount(2, 'regions.data')
            ->assertJsonPath('summary.distribution.kritis', 1)
            ->assertJsonPath('summary.distribution.rendah', 1)
            ->assertJsonPath('summary.methodology.poverty_rate.weight', 30)
            ->assertJsonPath('summary.methodology.disaster_risk_score.weight', 15);

        $this->getJson('/api/welfare/ikk?tier=sedang')
            ->assertOk()
            ->assertJsonCount(1, 'regions.data')
            ->assertJsonPath('regions.data.0.region_code', '2201');

        $this->getJson('/api/welfare/ikk?search=1102')
            ->assertOk()
            ->assertJsonCount(1, 'regions.data')
            ->assertJsonPath('regions.data.0.region_name', 'Kota Kritis');

        $this->getJson('/api/welfare/ikk?sort=ikk_asc')
            ->assertOk()
            ->assertJsonPath('regions.data.0.region_name', 'Kota Rendah');

        $this->getJson('/api/welfare/ikk?sort=poverty_desc')
            ->assertOk()
            ->assertJsonPath('regions.data.0.region_name', 'Kota Kritis');

        $this->getJson('/api/welfare/ikk?sort=disaster_desc')
            ->assertOk()
            ->assertJsonPath('regions.data.0.region_name', 'Kota Kritis');
    }

    public function test_region_profile_returns_subscores_citations_and_associated_party_work(): void
    {
        $region = $this->createRegion('9901', 'Kota Contoh', 'Provinsi Contoh', 10, 0.35, 4, 20, 55);
        FactionContract::query()->create([
            'politician_name' => 'Ayu Pratiwi',
            'title' => 'Program layanan kesehatan',
            'description' => 'Program prioritas warga.',
            'status' => 'SEDANG_BERJALAN',
            'region_code' => $region->region_code,
        ]);
        FactionContract::query()->create([
            'politician_name' => 'Bima Santoso',
            'title' => 'Kontrak provinsi',
            'description' => 'Agenda tingkat provinsi.',
            'status' => 'BELUM_DIMULAI',
            'province_name' => $region->province_name,
        ]);
        FactionContract::query()->create([
            'politician_name' => 'Citra Wulandari',
            'title' => 'Kontrak selesai',
            'description' => 'Tidak lagi aktif.',
            'status' => 'TERPENUHI',
            'region_code' => $region->region_code,
        ]);
        $coordinator = User::factory()->create();
        Event::query()->create([
            'title' => 'Gerak Warga wilayah',
            'description' => 'Agenda layanan publik.',
            'location_lat' => -6.2,
            'location_lng' => 106.8,
            'radius_m' => 100,
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDays(2),
            'coordinator_id' => $coordinator->id,
            'status' => 'active',
            'request_id' => (string) Str::uuid(),
            'province_name' => $region->province_name,
        ]);

        $this->getJson('/api/welfare/ikk/9901')
            ->assertOk()
            ->assertJsonPath('region.region_code', '9901')
            ->assertJsonPath('region.ikk_score', $region->ikk_score)
            ->assertJsonPath('sources.bps', 'BPS Kota Contoh Dalam Angka 2025')
            ->assertJsonPath('sources.bnpb', 'BNPB DIBI 2024')
            ->assertJsonPath('sub_scores.disaster_risk_score', 55)
            ->assertJsonCount(2, 'internal_work.faction_contracts')
            ->assertJsonPath('internal_work.events.0.title', 'Gerak Warga wilayah');
    }

    public function test_ingest_authorization_validation_and_request_idempotency(): void
    {
        $requestId = 'c63f1c53-16d5-4db4-b0ea-0a0ca8be0380';
        $payload = ['request_id' => $requestId, 'rows' => [$this->ingestRow()]];

        $this->postJson('/api/welfare/ikk/ingest', $payload)->assertUnauthorized();

        $panelis = $this->userWithRole('panelis');
        $this->actingAs($panelis)
            ->postJson('/api/welfare/ikk/ingest', $payload)
            ->assertForbidden();

        $pengurus = $this->userWithRole('pengurus');
        $this->actingAs($pengurus)
            ->postJson('/api/welfare/ikk/ingest', $payload)
            ->assertCreated()
            ->assertJsonPath('status', 'processed')
            ->assertJsonPath('records_saved', 1);

        $this->postJson('/api/welfare/ikk/ingest', $payload)
            ->assertOk()
            ->assertJsonPath('status', 'already_processed')
            ->assertJsonPath('records_saved', 1);

        $differentPayload = $payload;
        $differentPayload['rows'][0]['poverty_rate'] = 40;
        $this->postJson('/api/welfare/ikk/ingest', $differentPayload)
            ->assertStatus(409);

        $this->postJson('/api/welfare/ikk/ingest', [
            'request_id' => 'not-a-uuid',
            'rows' => [$this->ingestRow()],
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['request_id']);

        $untrustedScore = $payload;
        $untrustedScore['request_id'] = '8d2a303f-01bd-41f0-82c7-888f3fb7890d';
        $untrustedScore['rows'][0]['ikk_score'] = 1;
        $this->postJson('/api/welfare/ikk/ingest', $untrustedScore)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['rows.0']);
    }

    public function test_curated_seed_contains_at_least_sixteen_regions_and_sync_falls_back_offline(): void
    {
        $this->assertGreaterThanOrEqual(16, count(RegionWelfareIndexSeeder::records()));
        config()->set('bps.ikk_data_url', null);
        config()->set('bnpb.ikk_data_url', null);

        $this->artisan('welfare:sync-open-data')
            ->expectsOutputToContain('curated offline baseline')
            ->assertSuccessful();

        $this->assertGreaterThanOrEqual(16, RegionWelfareIndex::query()->count());
    }

    public function test_economic_observations_can_seed_ikk_for_34_provinces_and_national(): void
    {
        $indicators = [
            'kemiskinan' => 10.0,
            'tpt' => 5.0,
            'gini' => 0.35,
            'stunting' => 20.0,
        ];

        foreach (array_merge(
            [['9999', 'Nasional (Indonesia)']],
            array_map(
                static fn (int $number): array => [sprintf('%02d00', $number), "Provinsi {$number}"],
                range(11, 44),
            ),
        ) as [$regionCode, $regionName]) {
            foreach ($indicators as $indicator => $value) {
                EconomicIndicatorObservation::query()->create([
                    'indicator_name' => $indicator,
                    'region_code' => $regionCode,
                    'region_name' => $regionName,
                    'period_year' => 2024,
                    'period_month' => in_array($indicator, ['kemiskinan', 'tpt'], true) ? 12 : null,
                    'value' => $value,
                    'unit' => $indicator === 'gini' ? 'rasio' : 'persen',
                    'source' => 'BPS WebAPI',
                ]);
            }
        }

        WelfareData::query()->create([
            'region_code' => '11',
            'region_name' => 'Provinsi 11',
            'indicator' => 'bencana',
            'value' => 12,
            'unit' => 'kejadian',
            'year' => 2024,
            'source' => 'BNPB',
        ]);

        $this->artisan('welfare:sync-economic-indicators')
            ->expectsOutputToContain('Synchronized 35 IKK rows')
            ->assertSuccessful();

        $this->assertSame(35, RegionWelfareIndex::query()->where('period_year', 2024)->count());
        $this->assertDatabaseHas('region_welfare_indices', [
            'region_code' => '9999',
            'region_name' => 'Nasional (Indonesia)',
            'period_year' => 2024,
        ]);
    }

    public function test_economic_ikk_sync_warns_and_succeeds_by_default_for_partial_data(): void
    {
        foreach (['kemiskinan', 'tpt', 'gini', 'stunting'] as $indicator) {
            EconomicIndicatorObservation::query()->create([
                'indicator_name' => $indicator,
                'region_code' => '1100',
                'region_name' => 'Aceh',
                'period_year' => 2024,
                'period_month' => null,
                'value' => 1,
                'unit' => 'persen',
                'source' => 'BPS WebAPI',
            ]);
        }

        $this->artisan('welfare:sync-economic-indicators')
            ->expectsOutputToContain('Expected at least 34 complete province rows')
            ->assertSuccessful();

        $this->assertDatabaseCount('region_welfare_indices', 0);
    }

    public function test_economic_ikk_sync_fails_for_incomplete_data_only_in_strict_mode(): void
    {
        $warning = 'Expected at least 34 complete province rows and one national row for 2024; found 0 provinces and no national row.';

        $this->artisan('welfare:sync-economic-indicators')
            ->expectsOutputToContain($warning)
            ->assertSuccessful();

        $this->artisan('welfare:sync-economic-indicators', ['--strict' => true])
            ->expectsOutputToContain($warning)
            ->assertFailed();
    }

    public function test_open_data_adapter_joins_normalized_bps_and_bnpb_records(): void
    {
        config()->set('bps.ikk_data_url', 'https://bps.test/ikk/{api_key}');
        config()->set('bps.api_key', 'bps-token');
        config()->set('bnpb.ikk_data_url', 'https://ckan.test/api/records/{resource_id}');
        config()->set('bnpb.ikk_resource_id', 'resource-1');
        config()->set('bnpb.api_key', 'bnpb-token');

        Http::fake([
            'https://bps.test/ikk/*' => Http::response([
                'data' => [[
                    'region_code' => '9903',
                    'region_name' => 'Kota API',
                    'province_name' => 'Provinsi API',
                    'period_year' => 2025,
                    'poverty_rate' => 11,
                    'gini_ratio' => 0.34,
                    'open_unemployment_rate' => 5,
                    'stunting_rate' => 19,
                    'bps_source_citation' => 'BPS API 2025',
                ]],
            ]),
            'https://ckan.test/api/records/*' => Http::response([
                'result' => [
                    'records' => [[
                        'region_code' => '9903',
                        'period_year' => 2025,
                        'disaster_events_count' => 7,
                        'affected_population' => 1800,
                        'disaster_risk_score' => 40,
                        'bnpb_source_citation' => 'BNPB CKAN 2025',
                    ]],
                ],
            ]),
        ]);

        $records = app(WelfareOpenDataAdapter::class)->records();

        $this->assertNotNull($records);
        $this->assertCount(1, $records);
        $this->assertSame('9903', $records[0]['region_code']);
        $this->assertSame(7, $records[0]['disaster_events_count']);
        $this->assertSame('BPS API 2025', $records[0]['bps_source_citation']);
        $this->assertSame('BNPB CKAN 2025', $records[0]['bnpb_source_citation']);
        Http::assertSent(fn ($request): bool => $request->hasHeader('Authorization', 'bnpb-token'));
    }

    public function test_filament_rbac_gives_panelis_read_access_and_limits_mutations(): void
    {
        $region = $this->createRegion('9801', 'Kota Audit', 'Provinsi Audit', 10, 0.3, 5, 15, 20);
        $panelis = $this->userWithRole('panelis');
        $this->actingAs($panelis);

        $this->assertTrue(RegionWelfareIndexResource::canViewAny());
        $this->assertFalse(RegionWelfareIndexResource::canCreate());
        $this->assertFalse(RegionWelfareIndexResource::canEdit($region));
        $this->assertFalse(RegionWelfareIndexResource::canDelete($region));
        $this->get('/admin/indeks-kerentanan-ikk')->assertOk();

        $pengurus = $this->userWithRole('pengurus');
        $this->actingAs($pengurus);
        $this->assertTrue(RegionWelfareIndexResource::canViewAny());
        $this->assertTrue(RegionWelfareIndexResource::canCreate());
        $this->assertTrue(RegionWelfareIndexResource::canEdit($region));
    }

    /**
     * @return array<string, int|float|string>
     */
    private function ingestRow(): array
    {
        return [
            'region_code' => '9902',
            'region_name' => 'Kota Ingest',
            'province_name' => 'Provinsi Ingest',
            'period_year' => 2025,
            'poverty_rate' => 12.5,
            'gini_ratio' => 0.35,
            'open_unemployment_rate' => 6.2,
            'stunting_rate' => 21.3,
            'disaster_events_count' => 12,
            'affected_population' => 4500,
            'disaster_risk_score' => 42.5,
            'bps_source_citation' => 'BPS Kota Ingest Dalam Angka 2025',
            'bnpb_source_citation' => 'BNPB DIBI 2025',
        ];
    }

    private function createRegion(
        string $code,
        string $name,
        string $province,
        float $poverty,
        float $gini,
        float $unemployment,
        float $stunting,
        float $disasterRisk,
    ): RegionWelfareIndex {
        $indicators = [
            'poverty_rate' => $poverty,
            'gini_ratio' => $gini,
            'open_unemployment_rate' => $unemployment,
            'stunting_rate' => $stunting,
            'disaster_risk_score' => $disasterRisk,
        ];
        $calculation = app(WelfareIndexService::class)->calculate($indicators);

        return RegionWelfareIndex::query()->create([
            ...$indicators,
            'region_code' => $code,
            'region_name' => $name,
            'province_name' => $province,
            'period_year' => 2024,
            'disaster_events_count' => 4,
            'affected_population' => 400,
            'ikk_score' => $calculation['ikk_score'],
            'vulnerability_tier' => $calculation['vulnerability_tier'],
            'bps_source_citation' => 'BPS '.$name.' Dalam Angka 2025',
            'bnpb_source_citation' => 'BNPB DIBI 2024',
        ]);
    }

    private function userWithRole(string $role): User
    {
        Role::query()->firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
