<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class EmailVerificationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Frontend page that will receive the signed backend URL.
     */
    private ?string $callbackUrl;

    /**
     * Create a new notification instance.
     */
    public function __construct(?string $callbackUrl = null)
    {
        $this->callbackUrl = $callbackUrl;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Build a temporary signed backend verification URL (valid 5 minutes).
     */
    protected function verificationUrl(object $notifiable): string
    {
        return URL::temporarySignedRoute(
            'verify.email',
            Carbon::now()->addMinutes(5),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }

    /**
     * Get the mail representation of the notification.
     *
     * Email button opens the SPA callback_url and passes the signed
     * backend URL as ?forwarded-url=... so the frontend can call it.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $signedUrl = $this->verificationUrl($notifiable);

        $actionUrl = $this->callbackUrl
            ? $this->callbackUrl.'?forwarded-url='.urlencode($signedUrl)
            : $signedUrl;

        return (new MailMessage)
            ->subject('Verify Email Address')
            ->line('Click the button below to verify your email address.')
            ->action('Verify Email', $actionUrl)
            ->line('If you did not create an account, no further action is required.')
            ->line('This verification link will expire in 5 minutes.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [];
    }
}
