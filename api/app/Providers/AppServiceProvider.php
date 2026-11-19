<?php

namespace App\Providers;

use App\Events\ReputationActivity;
use App\Listeners\AwardReputationPoints;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(ReputationActivity::class, AwardReputationPoints::class);

        if (app()->environment('testing')) {
            return;
        }

        $connection = DB::connection();
        $databasePath = $connection->getConfig('database');

        if (
            $connection->getDriverName() === 'sqlite'
            && $databasePath !== ':memory:'
            && file_exists($databasePath)
        ) {
            DB::statement('PRAGMA journal_mode=WAL;');
            DB::statement('PRAGMA synchronous=NORMAL;');
            DB::statement('PRAGMA busy_timeout=5000;');
            DB::statement('PRAGMA foreign_keys=ON;');
            DB::statement('PRAGMA temp_store=MEMORY;');
            DB::statement('PRAGMA cache_size=-20000;');
        }
    }
}
