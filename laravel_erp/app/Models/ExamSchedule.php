<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['exam_session_id', 'subject_id', 'exam_center_id', 'chief_invigilator_id', 'exam_date', 'slot', 'candidate_count', 'status'])]
final class ExamSchedule extends Model
{
    protected function casts(): array
    {
        return ['exam_date' => 'date'];
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function center(): BelongsTo
    {
        return $this->belongsTo(ExamCenter::class, 'exam_center_id');
    }

    public function invigilator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'chief_invigilator_id');
    }
}
