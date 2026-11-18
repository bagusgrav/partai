<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class MemberVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly User $user) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Verifikasi email anggota PARTAI',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.member-verification',
            with: [
                'verificationUrl' => URL::temporarySignedRoute(
                    'auth.verify-email',
                    now()->addHour(),
                    [
                        'id' => $this->user->getKey(),
                        'hash' => sha1($this->user->email),
                    ],
                ),
            ],
        );
    }
}
