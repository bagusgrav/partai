<?php

namespace Tests\Feature;

use App\Models\EconomicIndicator;
use App\Models\GovernmentInsight;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EconomicInsightsTest extends TestCase
{
    use RefreshDatabase;

    public function test_economic_summary_returns_the_latest_record_for_each_indicator(): void
    {
        $this->travelTo(now()->startOfMinute());

        EconomicIndicator::query()->create([
            'indicator_type' => 'inflation',
            'title' => 'Inflasi',
            'value' => 2.5,
            'unit' => 'persen',
            'period' => '2026-11-15',
            'source' => 'Simulasi',
            'trend' => 'stabil',
        ]);
        EconomicIndicator::query()->create([
            'indicator_type' => 'inflation',
            'title' => 'Inflasi',
            'value' => 2.7,
            'unit' => 'persen',
            'period' => '2026-11-16',
            'source' => 'Simulasi',
            'trend' => 'naik',
        ]);
        EconomicIndicator::query()->create([
            'indicator_type' => 'debt',
            'title' => 'Utang Pemerintah',
            'value' => 39.5,
            'unit' => 'persen PDB',
            'period' => '2026-11-16',
            'source' => 'Simulasi',
            'trend' => 'stabil',
        ]);

        $this->getJson('/api/economic/summary')
            ->assertOk()
            ->assertJsonCount(2, 'indicators')
            ->assertJsonPath('indicators.0.indicator_type', 'inflation')
            ->assertJsonPath('indicators.0.value', 2.7)
            ->assertJsonPath('indicators.1.indicator_type', 'debt')
            ->assertJsonPath('last_updated', now()->toISOString());
    }

    public function test_economic_history_is_returned_oldest_first(): void
    {
        foreach (['2026-11-15' => 16200, '2026-11-16' => 16300] as $period => $value) {
            EconomicIndicator::query()->create([
                'indicator_type' => 'exchange_rate',
                'title' => 'Kurs USD/IDR',
                'value' => $value,
                'unit' => 'rupiah per USD',
                'period' => $period,
                'source' => 'API',
                'trend' => 'naik',
            ]);
        }

        $this->getJson('/api/economic/history/exchange_rate')
            ->assertOk()
            ->assertJsonPath('type', 'exchange_rate')
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.period', '2026-11-15')
            ->assertJsonPath('data.1.value', 16300);
    }

    public function test_news_and_cabinet_endpoints_return_only_their_category(): void
    {
        GovernmentInsight::query()->create([
            'category' => 'berita',
            'title' => 'Berita terbaru',
            'content' => 'Ringkasan berita.',
            'source_url' => 'https://example.test/berita',
            'published_at' => '2026-11-16 10:00:00',
        ]);
        GovernmentInsight::query()->create([
            'category' => 'kabinet',
            'title' => 'Menteri Contoh',
            'content' => 'Informasi kabinet.',
        ]);

        $this->getJson('/api/insights/news')
            ->assertOk()
            ->assertJsonCount(1, 'insights')
            ->assertJsonPath('insights.0.category', 'berita');

        $this->getJson('/api/insights/cabinet')
            ->assertOk()
            ->assertJsonCount(1, 'insights')
            ->assertJsonPath('insights.0.category', 'kabinet');
    }

    public function test_news_command_parses_rss_and_updates_existing_urls(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'https://www.antaranews.com/rss/terkini.xml' => Http::response(
                '<?xml version="1.0"?><rss version="2.0"><channel><item><title>Judul berita</title><link>https://www.antaranews.com/berita/123</link><description><![CDATA[Ringkasan <b>berita</b>.]]></description><pubDate>Mon, 16 Nov 2026 10:00:00 +0000</pubDate><enclosure url="https://example.test/image.jpg" type="image/jpeg" /></item></channel></rss>',
            ),
        ]);

        $this->artisan('fetch:news-insight')->assertSuccessful();
        $this->artisan('fetch:news-insight')->assertSuccessful();

        $this->assertDatabaseCount('government_insights', 1);
        $this->assertDatabaseHas('government_insights', [
            'category' => 'berita',
            'title' => 'Judul berita',
            'content' => 'Ringkasan berita.',
            'source_url' => 'https://www.antaranews.com/berita/123',
            'image_url' => 'https://example.test/image.jpg',
        ]);
        Http::assertSentCount(2);
    }

    public function test_exchange_rate_command_saves_a_valid_rate_idempotently(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'https://open.er-api.com/v6/latest/USD' => Http::response([
                'result' => 'success',
                'rates' => ['IDR' => 16325.75],
            ]),
        ]);

        $this->artisan('fetch:exchange-rate')->assertSuccessful();
        $this->artisan('fetch:exchange-rate')->assertSuccessful();

        $this->assertDatabaseCount('economic_indicators', 1);
        $this->assertDatabaseHas('economic_indicators', [
            'indicator_type' => 'exchange_rate',
            'value' => 16325.75,
            'unit' => 'rupiah per USD',
        ]);
        Http::assertSentCount(2);
    }

    public function test_economic_summary_command_refreshes_without_duplicating_same_day_records(): void
    {
        $this->travelTo(now()->setDate(2026, 11, 17)->startOfDay());

        $this->artisan('fetch:economic-summary')->assertSuccessful();
        $this->travel(1)->minute();
        $this->artisan('fetch:economic-summary')->assertSuccessful();

        $this->assertDatabaseCount('economic_indicators', 3);
        $this->assertDatabaseHas('economic_indicators', [
            'indicator_type' => 'inflation',
            'period' => '2026-11-17',
            'source' => 'Simulasi (bukan data resmi)',
        ]);

        $this->travelTo(now()->addDay());
        $this->artisan('fetch:economic-summary')->assertSuccessful();
        $this->assertDatabaseCount('economic_indicators', 6);
    }
}
