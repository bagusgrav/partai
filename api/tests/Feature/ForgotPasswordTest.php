<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ForgotPasswordTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_registered_email_receives_a_reset_link_to_the_frontend(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'anggota@example.test']);

        $this->postJson('/api/auth/forgot-password', [
            'email' => $user->email,
        ])->assertOk()
            ->assertJson([
                'message' => 'Jika alamat email terdaftar, tautan untuk mengatur ulang kata sandi akan dikirim.',
            ]);

        $sentNotification = null;
        Notification::assertSentTo(
            $user,
            ResetPasswordNotification::class,
            function (ResetPasswordNotification $notification) use (&$sentNotification): bool {
                $sentNotification = $notification;

                return true;
            },
        );

        $this->assertNotNull($sentNotification);
        $this->assertStringStartsWith(
            rtrim((string) config('app.frontend_url'), '/').'/reset-password?token=',
            $sentNotification->toMail($user)->actionUrl,
        );
        $this->assertStringContainsString('email=anggota%40example.test', $sentNotification->toMail($user)->actionUrl);
    }

    public function test_unknown_email_gets_the_same_generic_success_response(): void
    {
        Notification::fake();

        $this->postJson('/api/auth/forgot-password', [
            'email' => 'tidak-terdaftar@example.test',
        ])->assertOk()
            ->assertJson([
                'message' => 'Jika alamat email terdaftar, tautan untuk mengatur ulang kata sandi akan dikirim.',
            ]);

        Notification::assertNothingSent();
    }

    public function test_email_is_required_and_must_be_valid(): void
    {
        $this->postJson('/api/auth/forgot-password', [
            'email' => 'bukan-email',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }
}
