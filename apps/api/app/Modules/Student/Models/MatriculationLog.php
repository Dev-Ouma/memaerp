<?php

declare(strict_types=1);

namespace App\Modules\Student\Models;

use App\Modules\Admission\Models\Application;
use App\Modules\Iam\Models\User;
use App\Modules\Institution\Models\Institution;
use App\Platform\Concerns\Auditable;
use App\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class MatriculationLog extends BaseModel
{
    use Auditable;

    protected $table = 'student.matriculation_logs';

    protected $fillable = [
        'institution_id',
        'application_id',
        'student_id',
        'matriculated_by',
        'matriculated_at',
        'original_documents_verified',
        'pledge_signed',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'matriculated_at' => 'immutable_datetime',
            'original_documents_verified' => 'boolean',
            'pledge_signed' => 'boolean',
        ];
    }

    /** @return BelongsTo<Institution, $this> */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /** @return BelongsTo<Application, $this> */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /** @return BelongsTo<Student, $this> */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /** @return BelongsTo<User, $this> */
    public function matriculatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'matriculated_by');
    }
}
