<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class ResetPasswordTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_valid_token_resets_password_and_can_only_be_used_once(): void
    {
        $user = User::factory()->create([
            'email' => 'anggota@example.test',
            'password' => 'password-lama',
        ]);
        $token = Password::createToken($user);

        $this->postJson('/api/auth/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'password-baru',
            'password_confirmation' => 'password-baru',
        ])->assertOk()
            ->assertJson(['message' => 'Kata sandi berhasil diubah.']);

        $this->assertTrue(Hash::check('password-baru', $user->fresh()->password));

        $this->postJson('/api/auth/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'password-lain',
            'password_confirmation' => 'password-lain',
        ])->assertUnprocessable();
    }

    public function test_invalid_token_is_rejected_without_changing_password(): void
    {
        $user = User::factory()->create([
            'email' => 'anggota@example.test',
            'password' => 'password-lama',
        ]);

        $this->postJson('/api/auth/reset-password', [
            'token' => 'token-tidak-valid',
            'email' => $user->email,
            'password' => 'password-baru',
            'password_confirmation' => 'password-baru',
        ])->assertUnprocessable()
            ->assertJson([
                'message' => 'Tautan reset kata sandi tidak valid atau sudah kedaluwarsa.',
            ]);

        $this->assertTrue(Hash::check('password-lama', $user->fresh()->password));
    }

    public function test_password_confirmation_is_required_and_must_match(): void
    {
        $this->postJson('/api/auth/reset-password', [
            'token' => 'token',
            'email' => 'anggota@example.test',
            'password' => 'password-baru',
            'password_confirmation' => 'password-berbeda',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }
}
