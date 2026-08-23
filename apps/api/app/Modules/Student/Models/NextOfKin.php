<?php

declare(strict_types=1);

namespace App\Modules\Student\Models;

use App\Modules\Institution\Models\Institution;
use App\Platform\Concerns\Auditable;
use App\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class NextOfKin extends BaseModel
{
    use Auditable;

    protected $table = 'student.next_of_kin';

    protected $fillable = [
        'institution_id',
        'student_id',
        'full_name',
        'relationship',
        'phone',
        'email',
        'address',
        'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'address' => 'array',
            'is_primary' => 'boolean',
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
}
