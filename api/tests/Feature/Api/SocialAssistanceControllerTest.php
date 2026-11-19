<?php

namespace Tests\Feature\Api;

use App\Models\SocialAssistance;
use App\Models\User;
use Database\Seeders\SocialAssistanceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SocialAssistanceControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_the_program_status_and_period_for_a_registered_nik(): void
    {
        Sanctum::actingAs(User::factory()->create([
            'nik' => '3201010101010001',
            'nik_verified_at' => now(),
        ]));
        SocialAssistance::query()->create([
            'nik' => '3201010101010001',
            'name' => 'Nama Rahasia',
            'program' => 'PKH',
            'status' => 'DITERIMA',
            'period' => '2026',
            'region_code' => '3201',
        ]);

        $this->getJson('/api/rights/check')
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
        Sanctum::actingAs(User::factory()->create([
            'nik' => '3201010101010001',
            'nik_verified_at' => now(),
        ]));

        $this->getJson('/api/rights/check')
            ->assertNotFound()
            ->assertJsonPath('message', 'NIK tidak terdaftar sebagai penerima bansos')
            ->assertHeader('Cache-Control', 'no-store, private');
    }

    public function test_it_rejects_an_invalid_nik_on_the_authenticated_account(): void
    {
        Sanctum::actingAs(User::factory()->create([
            'nik' => '12345',
            'nik_verified_at' => now(),
        ]));

        $this->getJson('/api/rights/check')
            ->assertUnprocessable()
            ->assertJsonPath('message', 'NIK harus terdiri dari 16 digit angka.');
    }

    public function test_it_rejects_unauthenticated_checks(): void
    {
        $this->getJson('/api/rights/check')->assertUnauthorized();
    }

    public function test_missing_and_unverified_niks_return_the_same_forbidden_response(): void
    {
        Sanctum::actingAs(User::factory()->create(['nik' => '3201010101010001']));
        $unverifiedResponse = $this->getJson('/api/rights/check');

        Sanctum::actingAs(User::factory()->create(['nik' => null]));
        $missingNikResponse = $this->getJson('/api/rights/check');

        $expected = [
            'message' => 'NIK belum terverifikasi.',
            'code' => 'nik_not_verified',
        ];
        $unverifiedResponse
            ->assertForbidden()
            ->assertExactJson($expected)
            ->assertHeader('Cache-Control', 'no-store, private');
        $missingNikResponse
            ->assertForbidden()
            ->assertExactJson($expected)
            ->assertHeader('Cache-Control', 'no-store, private');
    }

    public function test_it_limits_checks_to_five_requests_per_minute(): void
    {
        RateLimiter::clear('127.0.0.1');
        Sanctum::actingAs(User::factory()->create([
            'nik' => '3201010101010001',
            'nik_verified_at' => now(),
        ]));

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->getJson('/api/rights/check')->assertNotFound();
        }

        $this->getJson('/api/rights/check')->assertTooManyRequests();
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
