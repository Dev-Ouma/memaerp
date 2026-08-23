<?php

declare(strict_types=1);

namespace App\Modules\Course\Notifications;

use App\Modules\Course\Models\Course;
use App\Modules\Course\Models\CourseOffering;
use App\Modules\Institution\Models\Term;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;
use LogicException;

final class LecturerAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    private readonly string $courseCode;

    private readonly string $courseTitle;

    private readonly string $termName;

    public function __construct(private readonly CourseOffering $offering)
    {
        $course = $offering->course;
        $term = $offering->term;
        if (! $course instanceof Course || ! $term instanceof Term) {
            throw new LogicException('A teaching allocation notification requires its course and term.');
        }
        $this->courseCode = $course->code;
        $this->courseTitle = $course->title;
        $this->termName = $term->name;
    }

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Teaching allocation: {$this->courseCode}")
            ->line("You have been allocated to teach {$this->courseCode} - {$this->courseTitle} (Section {$this->offering->section_code}) for {$this->termName}.");
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'event' => 'course.offering.lecturer-assigned',
            'course_offering_id' => $this->offering->id,
            'course_code' => $this->courseCode,
            'section_code' => $this->offering->section_code,
            'message' => "You have been allocated to teach {$this->courseCode} - {$this->courseTitle} (Section {$this->offering->section_code}) for {$this->termName}.",
        ];
    }
}
