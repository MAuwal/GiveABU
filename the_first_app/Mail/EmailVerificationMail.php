<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $verificationUrl;
    public string $recipientName;

    public function __construct(string $verificationUrl, string $recipientName = '')
    {
        $this->verificationUrl = $verificationUrl;
        $this->recipientName = $recipientName;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Verify Your Email Address - GIVE ABU',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.email-verification',
        );
    }
}
