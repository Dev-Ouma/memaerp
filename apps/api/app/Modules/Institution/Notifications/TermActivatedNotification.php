<?php

declare(strict_types=1);

namespace App\Modules\Institution\Notifications;

use App\Modules\Institution\Models\AcademicYear;
use App\Modules\Institution\Models\Term;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;
use LogicException;

final class TermActivatedNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    private readonly string $academicYearName;

    public function __construct(private readonly Term $term)
    {
        $year = $term->academicYear;
        if (! $year instanceof AcademicYear) {
            throw new LogicException('An activated term must belong to an academic year.');
        }
        $this->academicYearName = $year->name;
    }

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New semester activated')
            ->greeting('Academic calendar update')
            ->line("{$this->term->name} of {$this->academicYearName} is now officially open.")
            ->action('View the academic calendar', config('app.url').'/institution');
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'event' => 'institution.term.activated',
            'term_id' => $this->term->id,
            'term_code' => $this->term->code,
            'term_name' => $this->term->name,
            'academic_year' => $this->academicYearName,
            'message' => "{$this->term->name} of {$this->academicYearName} is now officially open.",
        ];
    }
}
