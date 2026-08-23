<?php

declare(strict_types=1);

namespace App\Modules\Attachment\Models;

use App\Modules\Iam\Models\User;
use App\Modules\Institution\Models\Institution;
use App\Modules\Student\Models\Student;
use App\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class AttachmentPlacement extends BaseModel
{
    protected $table = 'attachment.attachment_placements';

    protected $fillable = [
        'institution_id', 'application_id', 'student_id', 'host_organization_id',
        'university_supervisor_id', 'field_supervisor_name', 'field_supervisor_email',
        'field_supervisor_phone', 'starts_on', 'ends_on', 'status',
        'host_accepted_at', 'activated_at', 'completed_at', 'final_grade', 'grade_letter',
    ];

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'host_accepted_at' => 'immutable_datetime',
            'activated_at' => 'immutable_datetime',
            'completed_at' => 'immutable_datetime',
            'final_grade' => 'float',
        ];
    }

    /** @return BelongsTo<Institution, $this> */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /** @return BelongsTo<AttachmentApplication, $this> */
    public function application(): BelongsTo
    {
        return $this->belongsTo(AttachmentApplication::class, 'application_id');
    }

    /** @return BelongsTo<Student, $this> */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /** @return BelongsTo<HostOrganization, $this> */
    public function hostOrganization(): BelongsTo
    {
        return $this->belongsTo(HostOrganization::class, 'host_organization_id');
    }

    /** @return BelongsTo<User, $this> */
    public function universitySupervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'university_supervisor_id');
    }

    /** @return HasMany<LogbookEntry, $this> */
    public function logbookEntries(): HasMany
    {
        return $this->hasMany(LogbookEntry::class, 'placement_id');
    }

    /** @return HasMany<AttachmentAssessment, $this> */
    public function assessments(): HasMany
    {
        return $this->hasMany(AttachmentAssessment::class, 'placement_id');
    }
}
