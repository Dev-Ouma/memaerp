<?php

declare(strict_types=1);

namespace App\Modules\Admission\Notifications;

use App\Modules\Admission\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class OfferIssuedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Application $application) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $programme = $this->application->programme?->name ?? 'your programme';

        return (new MailMessage)
            ->subject('Admission Offer — '.$this->application->application_number)
            ->greeting('Congratulations!')
            ->line("You have been offered admission to {$programme}.")
            ->line('Offer reference: '.$this->application->offer_letter_ref)
            ->line('Please log in to the applicant portal to download your letter and accept within 30 days.')
            ->action('View application status', url('/apply/status'));
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'application_id' => $this->application->id,
            'application_number' => $this->application->application_number,
            'offer_letter_ref' => $this->application->offer_letter_ref,
            'status' => $this->application->status,
        ];
    }
}
