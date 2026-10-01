<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $code;
    public $name;

    public function __construct(string $code, string $name = 'User')
    {
        $this->code = $code;
        $this->name = $name;
    }

    public function build()
    {
        return $this->subject('ABU Endowment - Password Reset Code')
            ->view('emails.password-reset-code')
            ->with([
                'code' => $this->code,
                'name' => $this->name,
            ]);
    }
}

