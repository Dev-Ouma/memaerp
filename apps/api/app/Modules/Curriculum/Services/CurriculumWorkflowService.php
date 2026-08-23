<?php

declare(strict_types=1);

namespace App\Modules\Curriculum\Services;

use App\Modules\Course\Models\Course;
use App\Modules\Course\Models\CoursePrerequisite;
use App\Modules\Curriculum\Models\ApprovalLedger;
use App\Modules\Curriculum\Models\CurriculumCourse;
use App\Modules\Curriculum\Models\CurriculumVersion;
use App\Modules\Curriculum\Models\ElectiveGroup;
use App\Modules\Curriculum\Models\ReviewStep;
use App\Modules\Iam\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use LogicException;

final class CurriculumWorkflowService
{
    /** @var array<string, int> */
    public const STAGES = ['HOD' => 1, 'DEAN' => 2, 'ACADEMIC_BOARD' => 3, 'SENATE' => 4];

    public function submit(CurriculumVersion $version): CurriculumVersion
    {
        $this->ensureMutable($version);
        if ($version->status !== 'DRAFT' || $version->curriculumCourses()->count() === 0) {
            throw ValidationException::withMessages(['status' => ['A draft curriculum with at least one course is required.']]);
        }

        return DB::transaction(function () use ($version): CurriculumVersion {
            foreach (self::STAGES as $stage => $sequence) {
                ReviewStep::query()->firstOrCreate([
                    'curriculum_version_id' => $version->id,
                    'stage' => $stage,
                ], [
                    'institution_id' => $version->institution_id,
                    'sequence' => $sequence,
                    'status' => 'PENDING',
                ]);
            }
            $version->auditReason('Curriculum submitted for multi-tier academic review')
                ->forceFill(['status' => 'UNDER_REVIEW', 'submitted_at' => now()])->save();

            return $version->fresh($this->relations()) ?? $version;
        });
    }

    public function approveNext(CurriculumVersion $version, User $actor, string $stage, string $reference, ?string $comments): CurriculumVersion
    {
        $this->ensureMutable($version);
        if ($version->status !== 'UNDER_REVIEW') {
            throw ValidationException::withMessages(['status' => ['The curriculum must be under review.']]);
        }

        return DB::transaction(function () use ($version, $actor, $stage, $reference, $comments): CurriculumVersion {
            $next = ReviewStep::query()->where('curriculum_version_id', $version->id)
                ->where('status', 'PENDING')->orderBy('sequence')->lockForUpdate()->firstOrFail();
            if ($next->stage !== $stage) {
                throw ValidationException::withMessages(['stage' => ["{$next->stage} approval is required next."]]);
            }
            $next->auditReason("{$stage} curriculum review completed")->update([
                'status' => 'APPROVED', 'reviewed_by' => $actor->id, 'reference' => $reference,
                'comments' => $comments, 'reviewed_at' => now(),
            ]);

            if ($stage === 'SENATE') {
                $this->finalizeApproval($version, $reference);
            }

            return $version->fresh($this->relations()) ?? $version;
        });
    }

    private function finalizeApproval(CurriculumVersion $version, string $senateReference): void
    {
        $version->loadMissing(['curriculumCourses.course', 'electiveGroups']);
        $coreCredits = $version->curriculumCourses->where('course_type', 'CORE')->sum(fn (CurriculumCourse $item): int => $this->creditsFor($item));
        $minimumElectiveCredits = $version->electiveGroups->sum('minimum_credits');
        $required = (int) $version->graduation_credits_required;
        if ($coreCredits + $minimumElectiveCredits !== $required) {
            throw ValidationException::withMessages([
                'graduation_credits_required' => ["Core credits ({$coreCredits}) plus minimum elective credits ({$minimumElectiveCredits}) must equal {$required}."],
            ]);
        }

        foreach ($version->electiveGroups as $group) {
            $courses = $version->curriculumCourses->where('elective_group_id', $group->id);
            $availableCredits = $courses->sum(fn (CurriculumCourse $item): int => $this->creditsFor($item));
            if ($courses->count() < $group->minimum_courses || $availableCredits < $group->minimum_credits) {
                throw ValidationException::withMessages(['elective_groups' => ["Elective group {$group->code} cannot satisfy its minimum selection rule."]]);
            }
        }

        $hash = $this->structureHash($version);
        CurriculumVersion::query()->where('programme_id', $version->programme_id)
            ->whereKeyNot($version->id)->where('status', 'APPROVED')->update(['status' => 'SUPERSEDED']);
        $version->auditReason('Curriculum locked under Senate resolution '.$senateReference)->forceFill([
            'status' => 'APPROVED', 'is_approved' => true, 'senate_approval_ref' => $senateReference,
            'approved_at' => now(), 'locked_at' => now(), 'structure_hash' => $hash,
        ])->save();

        $previousHash = ApprovalLedger::query()->where('institution_id', $version->institution_id)->latest('created_at')->value('entry_hash');
        $payload = ['curriculum_version_id' => $version->id, 'programme_id' => $version->programme_id, 'version_code' => $version->version_code, 'senate_reference' => $senateReference, 'structure_hash' => $hash, 'approved_at' => now()->toIso8601String()];
        $payloadJson = json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        ApprovalLedger::query()->create([
            'institution_id' => $version->institution_id,
            'curriculum_version_id' => $version->id,
            'previous_hash' => $previousHash,
            'entry_hash' => hash('sha256', ($previousHash ?? '').$payloadJson),
            'payload' => $payload,
        ]);
    }

    private function structureHash(CurriculumVersion $version): string
    {
        $courses = $version->curriculumCourses()->with('course:id,code,credits')->orderBy('year_level')->orderBy('semester')->orderBy('course_id')->get()->toArray();
        $groups = ElectiveGroup::query()->where('curriculum_version_id', $version->id)->orderBy('code')->get()->toArray();
        $requirements = CoursePrerequisite::query()->where('curriculum_version_id', $version->id)->orderBy('course_id')->orderBy('prerequisite_course_id')->get()->toArray();

        return hash('sha256', json_encode(['courses' => $courses, 'groups' => $groups, 'requirements' => $requirements], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));
    }

    private function ensureMutable(CurriculumVersion $version): void
    {
        if ($version->isLocked()) {
            throw new HttpResponseException(response()->json([
                'error' => ['code' => 'ERR-CUR-002', 'message' => 'Approved curriculum versions are read-only. Create a new version.'],
            ], 409));
        }
    }

    private function creditsFor(CurriculumCourse $item): int
    {
        $course = $item->course;
        if (! $course instanceof Course) {
            throw new LogicException('Every curriculum grid entry must reference an existing course.');
        }

        return (int) $course->credits;
    }

    /** @return list<string> */
    private function relations(): array
    {
        return ['programme.department.faculty', 'effectiveYear', 'curriculumCourses.course', 'electiveGroups', 'reviewSteps.reviewer'];
    }
}
