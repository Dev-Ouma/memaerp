<?php

declare(strict_types=1);

namespace App\Modules\Graduation\Models;

use App\Modules\Institution\Models\Institution;
use App\Modules\Student\Models\Student;
use App\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Certificate extends BaseModel
{
    protected $table = 'graduation.certificates';

    protected $fillable = [
        'institution_id', 'student_id', 'graduation_application_id',
        'certificate_number', 'verification_token', 'issued_at', 'status',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'immutable_datetime',
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

    /** @return BelongsTo<GraduationApplication, $this> */
    public function application(): BelongsTo
    {
        return $this->belongsTo(GraduationApplication::class, 'graduation_application_id');
    }
}
