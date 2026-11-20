<?php

namespace Tests\Feature;

use App\Filament\Resources\CivicQuestionResource;
use App\Filament\Resources\ItemResource;
use App\Filament\Widgets\AdminStatsOverview;
use App\Models\CivicQuestion;
use App\Models\Post;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationGroup;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminStatsOverviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_stats_show_totals_and_only_currently_active_votes(): void
    {
        $user = User::factory()->create();

        Post::query()->create([
            'user_id' => $user->id,
            'body' => 'Test community post',
            'visibility' => 'public',
            'status' => 'published',
            'request_id' => '5b2ce1bd-ecb2-4c63-aa01-d5a264277b1d',
        ]);

        CivicQuestion::query()->create([
            'domain' => 'hak_konstitusional',
            'mastery_level' => 1,
            'prompt' => 'Test question',
            'options' => [
                ['key' => 'A', 'text' => 'Answer A'],
                ['key' => 'B', 'text' => 'Answer B'],
                ['key' => 'C', 'text' => 'Answer C'],
                ['key' => 'D', 'text' => 'Answer D'],
            ],
            'correct_option' => 'A',
            'explanation' => 'Test explanation',
            'source_citation' => 'Test source',
        ]);

        $now = now();
        DB::table('votes')->insert([
            [
                'title' => 'Current vote',
                'description' => 'Currently active',
                'starts_at' => $now->copy()->subMinute(),
                'ends_at' => $now->copy()->addMinute(),
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title' => 'Expired vote',
                'description' => 'No longer active',
                'starts_at' => $now->copy()->subMinutes(2),
                'ends_at' => $now->copy()->subMinute(),
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $widget = new class extends AdminStatsOverview
        {
            public function statsForTest(): array
            {
                return $this->getStats();
            }
        };

        $stats = $widget->statsForTest();

        $this->assertSame(
            ['Total User', 'Total Post Komunitas', 'Total Voting Aktif', 'Total Soal Kuis'],
            array_map(static fn (Stat $stat): string => (string) $stat->getLabel(), $stats),
        );
        $this->assertSame(
            [1, 1, 1, 1],
            array_map(static fn (Stat $stat): int => (int) $stat->getValue(), $stats),
        );
    }

    public function test_admin_brand_and_navigation_groups_are_configured_in_order(): void
    {
        $panel = Filament::getPanel('admin');

        $this->assertSame('PARTAI', $panel->getBrandName());
        $this->assertStringEndsWith('/partai-favicon.svg', $panel->getFavicon());
        $this->assertContains(AdminStatsOverview::class, $panel->getWidgets());
        $this->assertTrue(CivicQuestionResource::shouldRegisterNavigation());
        $this->assertFalse(ItemResource::shouldRegisterNavigation());
        $this->assertSame(
            [
                'Komunitas',
                'Pendidikan',
                'Aksi Lapangan',
                'Tata Kelola',
                'Keuangan & Pengawasan',
                'Komunikasi Publik',
            ],
            array_map(
                static fn (NavigationGroup|string $group): ?string => $group instanceof NavigationGroup
                    ? $group->getLabel()
                    : $group,
                $panel->getNavigationGroups(),
            ),
        );
    }
}
