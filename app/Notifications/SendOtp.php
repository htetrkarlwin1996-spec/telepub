<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendOtp extends Notification
{
    use Queueable;

    public function __construct(
        public string $otp,
        public string $type
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->type === 'registration'
            ? 'Verify Your Email - TeleMusic'
            : 'Reset Your Password - TeleMusic';

        $greeting = $this->type === 'registration'
            ? 'Welcome to TeleMusic!'
            : 'Password Reset Request';

        $intro = $this->type === 'registration'
            ? 'Thank you for registering. Please use the OTP code below to verify your email address.'
            : 'We received a request to reset your password. Use the OTP code below to proceed.';

        return (new MailMessage)->subject($subject)->view('emails.otp', [
            'title' => $greeting,
            'intro' => $intro,
            'otp' => $this->otp,
        ]);
    }
}
