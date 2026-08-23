<?php

declare(strict_types=1);

namespace App\Modules\Attachment\Models;

use App\Modules\Iam\Models\User;
use App\Modules\Institution\Models\Institution;
use App\Modules\Institution\Models\Term;
use App\Modules\Student\Models\Student;
use App\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class AttachmentApplication extends BaseModel
{
    protected $table = 'attachment.attachment_applications';

    protected $fillable = [
        'institution_id', 'student_id', 'term_id', 'preferred_organization_ids',
        'motivation', 'status', 'submitted_at', 'reviewed_by', 'reviewed_at',
        'review_notes', 'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'preferred_organization_ids' => 'array',
            'submitted_at' => 'immutable_datetime',
            'reviewed_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<Institution, $this> */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /** @return BelongsTo<Student, $this> */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /** @return BelongsTo<Term, $this> */
    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /** @return HasOne<AttachmentPlacement, $this> */
    public function placement(): HasOne
    {
        return $this->hasOne(AttachmentPlacement::class, 'application_id');
    }
}
