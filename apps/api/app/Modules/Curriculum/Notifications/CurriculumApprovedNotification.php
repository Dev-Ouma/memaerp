<?php

declare(strict_types=1);

namespace App\Modules\Curriculum\Notifications;

use App\Modules\Curriculum\Models\CurriculumVersion;
use App\Modules\Curriculum\Models\Programme;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;
use LogicException;

final class CurriculumApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    private readonly string $programmeName;

    public function __construct(private readonly CurriculumVersion $version)
    {
        $programme = $version->programme;
        if (! $programme instanceof Programme) {
            throw new LogicException('A curriculum approval notification requires its programme relation.');
        }
        $this->programmeName = $programme->name;
    }

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Curriculum approved by Senate')
            ->line("Curriculum version {$this->version->version_code} for {$this->programmeName} has been approved by Senate.")
            ->action('View curricula', config('app.url').'/programmes');
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'event' => 'curriculum.version.approved',
            'curriculum_version_id' => $this->version->id,
            'version_code' => $this->version->version_code,
            'programme_name' => $this->programmeName,
            'message' => "Curriculum {$this->version->version_code} has been approved by Senate.",
        ];
    }
}
