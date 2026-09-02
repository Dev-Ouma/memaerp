<?php

declare(strict_types=1);

namespace App\Models\PgResearch;

use App\Models\AcademicProgramme;
use App\Models\Student;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'student_id', 'programme_id', 'reg_no', 'candidate_name', 'degree_level', 'programme_title',
    'academic_year', 'coursework_units_total', 'coursework_units_passed', 'gpa', 'fee_balance',
    'registration_status', 'eligibility_status', 'stage', 'thesis_title', 'commenced_on', 'expected_completion',
])]
final class PgResearchCandidate extends Model
{
    public const ELIGIBILITY = ['PENDING', 'ELIGIBLE', 'PROVISIONAL', 'BLOCKED'];

    public const STAGES = ['REGISTERED', 'PROPOSAL', 'FIELDWORK', 'WRITING', 'DEFENCE', 'EXAMINATION', 'COMPLETE', 'WITHDRAWN'];

    protected function casts(): array
    {
        return [
            'gpa' => 'decimal:2',
            'fee_balance' => 'decimal:2',
            'commenced_on' => 'date',
            'expected_completion' => 'date',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function programme(): BelongsTo
    {
        return $this->belongsTo(AcademicProgramme::class, 'programme_id');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(PgSupervisorAllocation::class, 'candidate_id');
    }

    public function waivers(): HasMany
    {
        return $this->hasMany(PgEligibilityWaiver::class, 'candidate_id');
    }

    public function proposals(): HasMany
    {
        return $this->hasMany(PgProposal::class, 'candidate_id');
    }

    public function seminars(): HasMany
    {
        return $this->hasMany(PgSeminar::class, 'candidate_id');
    }

    public function progressReports(): HasMany
    {
        return $this->hasMany(PgProgressReport::class, 'candidate_id');
    }

    public function scans(): HasMany
    {
        return $this->hasMany(PgPlagiarismScan::class, 'candidate_id');
    }

    public function defenceRequests(): HasMany
    {
        return $this->hasMany(PgDefenceRequest::class, 'candidate_id');
    }

    public function examiners(): HasMany
    {
        return $this->hasMany(PgExaminer::class, 'candidate_id');
    }

    public function viva(): HasOne
    {
        return $this->hasOne(PgVivaExamination::class, 'candidate_id')->latestOfMany();
    }

    public function marks(): HasOne
    {
        return $this->hasOne(PgThesisMark::class, 'candidate_id');
    }

    public function resubmissions(): HasMany
    {
        return $this->hasMany(PgThesisResubmission::class, 'candidate_id');
    }

    public function publications(): HasMany
    {
        return $this->hasMany(PgPublication::class, 'candidate_id');
    }

    public function appeals(): HasMany
    {
        return $this->hasMany(PgAppeal::class, 'candidate_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(PgResearchEvent::class, 'candidate_id');
    }

    public function leadSupervisor(): ?PgSupervisor
    {
        return $this->allocations->firstWhere(fn ($a) => $a->role === 'LEAD' && $a->status === 'ACTIVE')?->supervisor;
    }

    public function courseworkComplete(): bool
    {
        return $this->coursework_units_total > 0
            && $this->coursework_units_passed >= $this->coursework_units_total;
    }

    public function feesCleared(): bool
    {
        return (float) $this->fee_balance <= 0.0;
    }

    public function hasActiveWaiver(): bool
    {
        return $this->waivers
            ->where('status', 'APPROVED')
            ->filter(fn ($w) => $w->expires_on === null || $w->expires_on->isFuture())
            ->isNotEmpty();
    }
}
