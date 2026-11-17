<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(#[\SensitiveParameter] public string $token) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $query = http_build_query([
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], '', '&', PHP_QUERY_RFC3986);
        $url = rtrim((string) config('app.frontend_url'), '/').'/reset-password?'.$query;
        $expiry = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        return (new MailMessage)
            ->subject('Atur ulang kata sandi PARTAI')
            ->greeting('Halo!')
            ->line('Kami menerima permintaan untuk mengatur ulang kata sandi akun PARTAI Anda.')
            ->action('Atur Ulang Kata Sandi', $url)
            ->line("Tautan ini hanya berlaku selama {$expiry} menit.")
            ->line('Jika Anda tidak meminta pengaturan ulang kata sandi, abaikan email ini.');
    }
}
