<?php

namespace App\Console\Commands;

use App\Models\EconomicIndicator;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class FetchExchangeRate extends Command
{
    protected $signature = 'fetch:exchange-rate';

    protected $description = 'Fetch the latest USD to IDR exchange rate';

    public function handle(): int
    {
        $url = 'https://open.er-api.com/v6/latest/USD';

        try {
            $response = Http::acceptJson()
                ->connectTimeout(5)
                ->timeout(20)
                ->retry([200, 500], throw: false)
                ->get($url);
        } catch (ConnectionException $exception) {
            $this->error('Unable to connect to the public exchange-rate API: '.$exception->getMessage());

            return self::FAILURE;
        }

        $payload = $response->json();
        $rate = is_array($payload) ? ($payload['rates']['IDR'] ?? null) : null;

        if (! $response->successful() || ($payload['result'] ?? null) !== 'success' || ! is_numeric($rate) || (float) $rate <= 0) {
            $this->error('The exchange-rate API returned an invalid response.');

            return self::FAILURE;
        }

        $period = now()->toDateString();
        $previousValue = EconomicIndicator::query()
            ->where('indicator_type', 'exchange_rate')
            ->where('period', '<', $period)
            ->orderByDesc('period')
            ->value('value');

        EconomicIndicator::query()->updateOrCreate(
            ['indicator_type' => 'exchange_rate', 'period' => $period],
            [
                'title' => 'Kurs USD/IDR',
                'value' => (float) $rate,
                'unit' => 'rupiah per USD',
                'source' => $url,
                'trend' => $this->trend((float) $rate, $previousValue),
            ],
        );

        $this->info(sprintf('Saved USD/IDR exchange rate: %s.', number_format((float) $rate, 2, '.', ',')));

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
