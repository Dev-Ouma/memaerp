<?php

declare(strict_types=1);

namespace App\Modules\Examination\Models;

use App\Modules\Institution\Models\Institution;
use App\Modules\Institution\Models\Term;
use App\Modules\Student\Models\Student;
use App\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ExamCard extends BaseModel
{
    protected $table = 'examination.exam_cards';

    protected $fillable = [
        'institution_id', 'student_id', 'term_id', 'qr_token', 'status', 'issued_at', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'immutable_datetime',
            'expires_at' => 'immutable_datetime',
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
}
