<?php

namespace Tests\Feature;

use App\Mail\MemberVerificationMail;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class MemberRegistrationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_registration_creates_a_member_and_returns_a_valid_token(): void
    {
        Mail::fake();

        $response = $this->postJson('/api/auth/register', [
            'request_id' => '7957ce94-9678-4bc8-ac0e-55f68e33fef7',
            'name' => 'Anggota Uji',
            'email' => 'anggota@example.test',
            'password' => 'rahasia-anggota',
            'cluster_id' => '32',
            'nik' => '3201010101010001',
        ]);

        $response->assertCreated()->assertJsonStructure(['token']);
        $this->assertDatabaseCount('users', 1);

        $user = User::query()->where('email', 'anggota@example.test')->firstOrFail();
        Mail::assertSent(MemberVerificationMail::class, function (MemberVerificationMail $mail) use ($user): bool {
            return $mail->hasTo($user->email);
        });
        $this->assertSame('member', $user->role);
        $this->assertSame('32', $user->cluster_id);
        $this->assertSame('3201010101010001', $user->nik);
        $this->assertFalse($user->is_verified);
        $this->assertTrue(Hash::check('rahasia-anggota', $user->password));

        $this->withToken($response->json('token'))
            ->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('name', 'Anggota Uji')
            ->assertJsonPath('email', 'anggota@example.test')
            ->assertJsonPath('cluster_id', '32')
            ->assertJsonMissingPath('nik');
    }

    public function test_signed_verification_link_marks_the_member_as_verified(): void
    {
        $user = User::factory()->create([
            'role' => 'member',
            'is_verified' => false,
        ]);
        $url = URL::temporarySignedRoute(
            'auth.verify-email',
            now()->addHour(),
            [
                'id' => $user->getKey(),
                'hash' => sha1($user->email),
            ],
        );

        $this->getJson($url)
            ->assertOk()
            ->assertJsonPath('message', 'Alamat email berhasil diverifikasi.');

        $this->assertDatabaseHas('users', [
            'id' => $user->getKey(),
            'is_verified' => true,
        ]);
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_replaying_a_registration_request_does_not_create_another_user(): void
    {
        Mail::fake();

        $payload = [
            'request_id' => '7957ce94-9678-4bc8-ac0e-55f68e33fef7',
            'name' => 'Anggota Uji',
            'email' => 'anggota@example.test',
            'password' => 'rahasia-anggota',
            'cluster_id' => '32',
        ];

        $this->postJson('/api/auth/register', $payload)->assertCreated();
        $this->postJson('/api/auth/register', $payload)
            ->assertOk()
            ->assertJsonStructure(['token']);

        $this->assertDatabaseCount('users', 1);
    }

    public function test_reusing_a_registration_request_id_with_different_data_is_rejected(): void
    {
        Mail::fake();

        $this->postJson('/api/auth/register', [
            'request_id' => '7957ce94-9678-4bc8-ac0e-55f68e33fef7',
            'name' => 'Anggota Uji',
            'email' => 'anggota@example.test',
            'password' => 'rahasia-anggota',
            'cluster_id' => '32',
        ])->assertCreated();

        $this->postJson('/api/auth/register', [
            'request_id' => '7957ce94-9678-4bc8-ac0e-55f68e33fef7',
            'name' => 'Nama Berbeda',
            'email' => 'anggota@example.test',
            'password' => 'rahasia-anggota',
            'cluster_id' => '32',
        ])->assertConflict();

        $this->assertDatabaseCount('users', 1);
    }

    public function test_registration_requires_name_email_password_cluster_and_request_id(): void
    {
        $this->postJson('/api/auth/register')
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'name',
                'email',
                'password',
                'cluster_id',
                'request_id',
            ]);
    }

    public function test_nik_must_be_unique_when_provided(): void
    {
        User::factory()->create(['nik' => '3201010101010001']);

        $this->postJson('/api/auth/register', [
            'request_id' => '7957ce94-9678-4bc8-ac0e-55f68e33fef7',
            'name' => 'Anggota Uji',
            'email' => 'anggota@example.test',
            'password' => 'rahasia-anggota',
            'cluster_id' => '32',
            'nik' => '3201010101010001',
        ])->assertUnprocessable()->assertJsonValidationErrors(['nik']);
    }
}
