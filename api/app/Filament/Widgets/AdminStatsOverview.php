<?php

namespace App\Filament\Widgets;

use App\Models\CivicQuestion;
use App\Models\Post;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class AdminStatsOverview extends BaseWidget
{
    protected static ?int $sort = -2;

    protected function getStats(): array
    {
        $now = now();

        $activeVotes = DB::table('votes')
            ->where('status', 'active')
            ->where('starts_at', '<=', $now)
            ->where('ends_at', '>=', $now)
            ->count();

        return [
            Stat::make('Total User', User::query()->count())
                ->description('Akun terdaftar')
                ->icon('heroicon-o-users')
                ->color('primary'),
            Stat::make('Total Post Komunitas', Post::query()->count())
                ->description('Konten komunitas')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('info'),
            Stat::make('Total Voting Aktif', $activeVotes)
                ->description('Sedang berlangsung')
                ->icon('heroicon-o-check-circle')
                ->color('success'),
            Stat::make('Total Soal Kuis', CivicQuestion::query()->count())
                ->description('Bank Soal Kewargaan')
                ->icon('heroicon-o-academic-cap')
                ->color('warning'),
        ];
    }
}
