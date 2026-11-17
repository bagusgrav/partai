<?php

namespace App\Console\Commands;

use App\Services\WelfareIndexService;
use App\Services\WelfareOpenDataAdapter;
use Database\Seeders\RegionWelfareIndexSeeder;
use Illuminate\Console\Command;

class SyncWelfareOpenData extends Command
{
    protected $signature = 'welfare:sync-open-data';

    protected $description = 'Synchronize the regional IKK dataset from BPS and BNPB';

    public function handle(WelfareOpenDataAdapter $adapter, WelfareIndexService $service): int
    {
        $records = $adapter->records();

        if ($records === null) {
            $records = array_map(
                static fn (array $record): array => [...$record, 'last_synced_at' => now()->toDateTimeString()],
                RegionWelfareIndexSeeder::records(),
            );
            $this->warn('BPS/BNPB endpoints are unavailable or incomplete; using the curated offline baseline.');
        }

        $count = $service->upsert($records);
        $this->info("Synchronized {$count} regional IKK records.");

        return self::SUCCESS;
    }
}
