<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;

class CustomVerifyEmail extends BaseVerifyEmail
{
    use Queueable;

    /**
     * Build the mail representation (NOT queued - sent immediately)
     */
    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject(__('mail.verify_email_subject'))
            ->view('emails.auth.verify-email', [
                'user' => $notifiable,
                'url' => $verificationUrl,
            ]);
    }
}
