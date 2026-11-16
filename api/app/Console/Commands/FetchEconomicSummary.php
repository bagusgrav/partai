<?php

namespace App\Console\Commands;

use App\Models\EconomicIndicator;
use Illuminate\Console\Command;

class FetchEconomicSummary extends Command
{
    protected $signature = 'fetch:economic-summary';

    protected $description = 'Refresh the clearly labeled simulated economic summary';

    public function handle(): int
    {
        $period = now()->toDateString();
        $records = [
            ['indicator_type' => 'debt', 'title' => 'Utang Pemerintah', 'value' => 39.5, 'unit' => 'persen PDB'],
            ['indicator_type' => 'apbn', 'title' => 'APBN', 'value' => 3850.0, 'unit' => 'triliun rupiah'],
            ['indicator_type' => 'inflation', 'title' => 'Inflasi', 'value' => 2.5, 'unit' => 'persen'],
        ];

        foreach ($records as $record) {
            $previousValue = EconomicIndicator::query()
                ->where('indicator_type', $record['indicator_type'])
                ->where('period', '<', $period)
                ->orderByDesc('period')
                ->value('value');

            EconomicIndicator::query()->updateOrCreate(
                ['indicator_type' => $record['indicator_type'], 'period' => $period],
                [
                    ...$record,
                    'source' => 'Simulasi (bukan data resmi)',
                    'trend' => $this->trend($record['value'], $previousValue),
                ],
            );
        }

        $this->info('Refreshed 3 simulated economic indicators.');

        return self::SUCCESS;
    }

    private function trend(float $value, mixed $previousValue): string
    {
        if (! is_numeric($previousValue) || abs($value - (float) $previousValue) < 0.0001) {
            return 'stabil';
        }

        return $value > (float) $previousValue ? 'naik' : 'turun';
    }
}
