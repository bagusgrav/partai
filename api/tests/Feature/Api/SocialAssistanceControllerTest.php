<?php

namespace Tests\Feature\Api;

use App\Models\SocialAssistance;
use Database\Seeders\SocialAssistanceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class SocialAssistanceControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_the_program_status_and_period_for_a_registered_nik(): void
    {
        SocialAssistance::query()->create([
            'nik' => '3201010101010001',
            'name' => 'Nama Rahasia',
            'program' => 'PKH',
            'status' => 'DITERIMA',
            'period' => '2026',
            'region_code' => '3201',
        ]);

        $this->getJson('/api/rights/check/3201010101010001')
            ->assertOk()
            ->assertJson([
                'program' => 'PKH',
                'status' => 'DITERIMA',
                'period' => '2026',
            ])
            ->assertJsonMissingPath('name')
            ->assertHeader('Cache-Control', 'no-store, private');
    }

    public function test_it_returns_not_found_for_an_unregistered_nik(): void
    {
        $this->getJson('/api/rights/check/3201010101010001')
            ->assertNotFound()
            ->assertJsonPath('message', 'NIK tidak terdaftar sebagai penerima bansos')
            ->assertHeader('Cache-Control', 'no-store, private');
    }

    public function test_it_rejects_nik_values_that_are_not_sixteen_digits(): void
    {
        $this->getJson('/api/rights/check/12345')
            ->assertUnprocessable()
            ->assertJsonPath('message', 'NIK harus terdiri dari 16 digit angka.');

        $this->getJson('/api/rights/check/123456789012345a')
            ->assertUnprocessable();
    }

    public function test_it_limits_public_checks_to_ten_requests_per_minute(): void
    {
        RateLimiter::clear('127.0.0.1');

        for ($attempt = 0; $attempt < 10; $attempt++) {
            $this->getJson('/api/rights/check/3201010101010001')->assertNotFound();
        }

        $this->getJson('/api/rights/check/3201010101010001')->assertTooManyRequests();
    }

    public function test_seeder_creates_a_repeatable_set_of_synthetic_sixteen_digit_niks(): void
    {
        $seeder = app(SocialAssistanceSeeder::class);
        $seeder->run();
        $count = SocialAssistance::query()->count();

        $this->assertGreaterThanOrEqual(5, $count);
        $this->assertLessThanOrEqual(10, $count);
        $this->assertSame(
            $count,
            SocialAssistance::query()->whereRaw('length(nik) = 16')->count(),
        );

        $seeder->run();
        $this->assertSame($count, SocialAssistance::query()->count());
    }
}
