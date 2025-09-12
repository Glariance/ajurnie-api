<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue; // optional but recommended
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $url // full SPA link with token & email
    ) {}

    // If you prefer the old build() style, you can remove envelope()/content() and keep build().
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reset your password'
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.auth.reset', 
            with: [ 
                'user'    => $this->user,
                'url'     => $this->url,
                'appName' => config('app.name', 'AJURNIE'),
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
