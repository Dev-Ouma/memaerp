<?php

declare(strict_types=1);

namespace App\Modules\Examination\Models;

use App\Modules\Institution\Models\Institution;
use App\Modules\Institution\Models\Term;
use App\Modules\Student\Models\Student;
use App\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class TermGpa extends BaseModel
{
    use HasFactory;

    protected $table = 'examination.term_gpas';

    protected $fillable = [
        'institution_id',
        'student_id',
        'term_id',
        'credits_attempted',
        'credits_earned',
        'gpa',
        'cgpa',
        'academic_standing',
    ];

    protected function casts(): array
    {
        return [
            'credits_attempted' => 'integer',
            'credits_earned' => 'integer',
            'gpa' => 'decimal:2',
            'cgpa' => 'decimal:2',
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
