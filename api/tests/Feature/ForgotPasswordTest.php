<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
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
            'request_id' => (string) Str::uuid(),
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
            'request_id' => (string) Str::uuid(),
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
            'request_id' => (string) Str::uuid(),
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_retrying_the_same_forgot_password_request_sends_only_one_notification(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'anggota@example.test']);
        $payload = [
            'email' => $user->email,
            'request_id' => (string) Str::uuid(),
        ];

        $this->postJson('/api/auth/forgot-password', $payload)->assertOk();
        $this->postJson('/api/auth/forgot-password', $payload)->assertOk();
        Notification::assertSentTo($user, ResetPasswordNotification::class, 1);
    }

    public function test_forgot_password_request_id_cannot_be_reused_for_another_payload(): void
    {
        Notification::fake();
        $firstUser = User::factory()->create(['email' => 'anggota@example.test']);
        $secondUser = User::factory()->create(['email' => 'lain@example.test']);
        $requestId = (string) Str::uuid();

        $this->postJson('/api/auth/forgot-password', [
            'email' => $firstUser->email,
            'request_id' => $requestId,
        ])->assertOk();
        $this->postJson('/api/auth/forgot-password', [
            'email' => $secondUser->email,
            'request_id' => $requestId,
        ])->assertConflict();

        Notification::assertSentTo($firstUser, ResetPasswordNotification::class);
        Notification::assertNotSentTo($secondUser, ResetPasswordNotification::class);
    }
}
