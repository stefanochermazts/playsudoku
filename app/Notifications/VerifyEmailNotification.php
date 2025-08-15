<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Illuminate\Bus\Queueable;

class VerifyEmailNotification extends BaseVerifyEmail
{
    use Queueable;

    /**
     * Build the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject(__('mail.verify_email.subject', ['app' => config('app.name')]))
            ->greeting(__('mail.verify_email.greeting', ['name' => $notifiable->name]))
            ->line(__('mail.verify_email.intro'))
            ->action(__('mail.verify_email.action'), $verificationUrl)
            ->line(__('mail.verify_email.outro_1'))
            ->line(__('mail.verify_email.outro_2', ['url' => $verificationUrl]))
            ->line(__('mail.verify_email.outro_3'))
            ->salutation(__('mail.verify_email.salutation', ['app' => config('app.name')]));
    }

    /**
     * Get the verification URL for the given notifiable.
     */
    protected function verificationUrl($notifiable): string
    {
        $locale = app()->getLocale();
        
        // Use localized verification route if current locale is not the default
        if ($locale !== config('app.fallback_locale')) {
            return URL::temporarySignedRoute(
                'localized.verification.verify',
                Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
                [
                    'locale' => $locale,
                    'id' => $notifiable->getKey(),
                    'hash' => sha1($notifiable->getEmailForVerification()),
                ]
            );
        }

        // Use default verification route for fallback locale
        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }
}
