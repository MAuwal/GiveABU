<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetLinkMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $resetUrl;
    public string $username;

    public function __construct(string $resetUrl, string $username)
    {
        $this->resetUrl = $resetUrl;
        $this->username = $username;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reset your ABU Endowment password',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.password-reset-link',
            with: [
                'resetUrl' => $this->resetUrl,
                'username' => $this->username,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
