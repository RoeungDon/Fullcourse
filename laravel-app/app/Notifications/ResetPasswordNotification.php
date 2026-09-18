<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    /**
     * Password reset token from Password::sendResetLink.
     */
    private string $token;

    /**
     * Frontend page that will receive the backend reset URL
     * (e.g. http://localhost:5173/set-new-password).
     */
    private ?string $callbackUrl;

    public function __construct(string $token, ?string $callbackUrl = null)
    {
        $this->token = $token;
        $this->callbackUrl = $callbackUrl;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Backend URL the SPA will POST the new password to.
     * email + token are query params because Password::reset needs them.
     *
     * Uses named route set.new-password:
     *   POST /api/set/new-password
     */
    protected function resetUrl(object $notifiable): string
    {
        return URL::route('set.new-password', [
            'email' => $notifiable->getEmailForPasswordReset(),
            'token' => $this->token,
        ]);
    }

    /**
     * Mail button → SPA, with backend URL in ?forwarded-url=
     * (same idea as EmailVerificationNotification)
     */
    public function toMail(object $notifiable): MailMessage
    {
        $backendUrl = $this->resetUrl($notifiable);

        $actionUrl = $this->callbackUrl
            ? $this->callbackUrl.'?forwarded-url='.urlencode($backendUrl)
            : $backendUrl;

        return (new MailMessage)
            ->subject('Reset Password Notification')
            ->line('You are receiving this email because we received a password reset request for your account.')
            ->action('Reset Password', $actionUrl)
            ->line('If you did not request a password reset, no further action is required.');
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}