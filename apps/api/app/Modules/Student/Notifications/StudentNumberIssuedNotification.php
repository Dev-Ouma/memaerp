<?php

declare(strict_types=1);

namespace App\Modules\Student\Notifications;

use App\Modules\Student\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class StudentNumberIssuedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Student $student) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $programme = $this->student->programme?->name ?? 'your programme';

        return (new MailMessage)
            ->subject('Welcome — Your Student Number is '.$this->student->student_number)
            ->greeting('Welcome to Mema University!')
            ->line('Your official student number is **'.$this->student->student_number.'**.')
            ->line("You have been matriculated into {$programme}.")
            ->line('Log in to the student portal to view your profile and download your digital student ID.')
            ->action('Open student portal', url('/student'));
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'student_id' => $this->student->id,
            'student_number' => $this->student->student_number,
            'programme' => $this->student->programme?->code,
            'status' => $this->student->status,
        ];
    }
}
