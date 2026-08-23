<?php

declare(strict_types=1);

namespace App\Modules\Student\Models;

use App\Modules\Iam\Models\User;
use App\Modules\Institution\Models\Institution;
use App\Platform\Concerns\Auditable;
use App\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class StudentDocument extends BaseModel
{
    use Auditable;

    protected $table = 'student.student_documents';

    protected $fillable = [
        'institution_id',
        'student_id',
        'document_type',
        'original_name',
        'disk_path',
        'mime_type',
        'byte_size',
        'verification_status',
        'verified_by',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'byte_size' => 'integer',
            'verified_at' => 'immutable_datetime',
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

    /** @return BelongsTo<User, $this> */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
