<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DailyDigestNotification extends Notification
{
    use Queueable;

    /**
     * @param  array{
     *     quiz: array{id: string, question: string, domain: string, level: string},
     *     news: array{title: string, content: string, source_url: ?string, published_at: ?string},
     *     welfare: array{region_name: string, indicator: string, value: float, unit: string, year: int, source: string}
     * }  $digest
     */
    public function __construct(
        public readonly array $digest,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return $this->toArray($notifiable);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'daily_digest',
            'title' => 'Ringkasan harian PARTAI',
            ...$this->digest,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Ringkasan harian PARTAI')
            ->greeting('Halo, '.$notifiable->name)
            ->line('Soal hari ini: '.$this->digest['quiz']['question'])
            ->line('Berita pengawasan: '.$this->digest['news']['title'])
            ->line($this->digest['news']['content'])
            ->line(sprintf(
                'Data kesejahteraan: %s di %s sebesar %s %s (%d).',
                $this->digest['welfare']['indicator'],
                $this->digest['welfare']['region_name'],
                $this->digest['welfare']['value'],
                $this->digest['welfare']['unit'],
                $this->digest['welfare']['year'],
            ))
            ->action('Kerjakan Soal', rtrim((string) config('app.frontend_url'), '/').'/belajar');
    }
}
