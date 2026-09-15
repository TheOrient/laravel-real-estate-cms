<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;

class CustomResetPassword extends BaseResetPassword
{
    use Queueable;

    /**
     * Build the mail representation
     */
    public function toMail($notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject(__('mail.reset_password_subject'))
            ->view('emails.auth.reset-password', [
                'user' => $notifiable,
                'url' => $url,
                'count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire'),
            ]);
    }
}
