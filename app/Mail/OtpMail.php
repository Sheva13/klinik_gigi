<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pin;
    public $expiresMinutes;

    public function __construct(string $pin, int $expiresMinutes = 5)
    {
        $this->pin = $pin;
        $this->expiresMinutes = $expiresMinutes;
    }

    public function build()
    {
        return $this->subject('Your verification code')
            ->view('emails.otp') // create view resources/views/emails/otp.blade.php
            ->with([
                'pin' => $this->pin,
                'minutes' => $this->expiresMinutes,
            ]);
    }
}
