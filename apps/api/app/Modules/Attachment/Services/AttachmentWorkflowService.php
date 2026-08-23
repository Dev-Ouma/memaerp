<?php

declare(strict_types=1);

namespace App\Modules\Attachment\Services;

use App\Modules\Attachment\Models\AttachmentApplication;
use App\Modules\Attachment\Models\AttachmentPlacement;
use App\Modules\Attachment\Models\HostOrganization;
use App\Modules\Iam\Models\User;
use App\Modules\Institution\Models\Term;
use App\Modules\Student\Models\Student;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

final class AttachmentWorkflowService
{
    /** @return Collection<int, HostOrganization> */
    public function listOrganizations(string $institutionId, bool $activeOnly = true): Collection
    {
        $query = HostOrganization::query()
            ->where('institution_id', $institutionId)
            ->orderBy('name');

        if ($activeOnly) {
            $query->where('is_active', true);
        }

        return $query->get();
    }

    /** @param array<string, mixed> $payload */
    public function createOrganization(User $actor, array $payload): HostOrganization
    {
        return HostOrganization::query()->create([
            'institution_id' => $actor->institution_id,
            'name' => $payload['name'],
            'industry' => $payload['industry'] ?? null,
            'contact_name' => $payload['contact_name'] ?? null,
            'contact_email' => $payload['contact_email'] ?? null,
            'contact_phone' => $payload['contact_phone'] ?? null,
            'address' => $payload['address'] ?? null,
            'capacity_per_intake' => (int) ($payload['capacity_per_intake'] ?? 5),
            'mou_valid_until' => $payload['mou_valid_until'] ?? null,
            'is_active' => $payload['is_active'] ?? true,
            'quality_rating' => $payload['quality_rating'] ?? null,
            'notes' => $payload['notes'] ?? null,
        ]);
    }

    /** @return array<string, mixed> */
    public function myAttachmentStatus(User $studentUser): array
    {
        $student = Student::query()->where('person_id', $studentUser->person_id)->firstOrFail();

        $application = AttachmentApplication::query()
            ->where('student_id', $student->id)
            ->with(['term', 'placement.hostOrganization', 'placement.universitySupervisor.person'])
            ->orderByDesc('created_at')
            ->first();

        $activePlacement = AttachmentPlacement::query()
            ->where('student_id', $student->id)
            ->whereIn('status', ['PENDING_HOST', 'ACTIVE'])
            ->with(['hostOrganization', 'universitySupervisor.person', 'logbookEntries', 'assessments'])
            ->first();

        return [
            'student_id' => $student->id,
            'student_number' => $student->student_number,
            'eligible' => $this->isEligible($student),
            'application' => $application,
            'active_placement' => $activePlacement,
        ];
    }

    /** @return Collection<int, AttachmentApplication> */
    public function listApplications(string $institutionId, ?string $status = null): Collection
    {
        $query = AttachmentApplication::query()
            ->where('institution_id', $institutionId)
            ->with(['student.person', 'student.programme', 'term', 'placement.hostOrganization'])
            ->orderByDesc('submitted_at');

        if ($status !== null) {
            $query->where('status', $status);
        }

        return $query->get();
    }

    /** @param array<string, mixed> $payload */
    public function submitApplication(User $studentUser, array $payload): AttachmentApplication
    {
        $student = Student::query()->where('person_id', $studentUser->person_id)->firstOrFail();

        if (! $this->isEligible($student)) {
            throw ValidationException::withMessages([
                'student' => 'You are not eligible for industrial attachment at this time.',
            ]);
        }

        $hasPending = AttachmentApplication::query()
            ->where('student_id', $student->id)
            ->whereIn('status', ['SUBMITTED', 'UNDER_REVIEW', 'APPROVED'])
            ->exists();

        if ($hasPending) {
            throw ValidationException::withMessages([
                'application' => 'You already have a pending attachment application.',
            ]);
        }

        $term = isset($payload['term_id'])
            ? Term::query()->where('institution_id', $student->institution_id)->findOrFail($payload['term_id'])
            : Term::query()->whereHas('academicYear', fn ($q) => $q->where('is_current', true))->first();

        return AttachmentApplication::query()->create([
            'institution_id' => $student->institution_id,
            'student_id' => $student->id,
            'term_id' => $term?->id,
            'preferred_organization_ids' => $payload['preferred_organization_ids'] ?? [],
            'motivation' => $payload['motivation'] ?? null,
            'status' => 'SUBMITTED',
            'submitted_at' => Carbon::now(),
        ])->load(['student.person', 'term']);
    }

    public function reviewApplication(
        User $reviewer,
        string $applicationId,
        string $decision,
        ?string $notes = null,
        ?string $rejectionReason = null,
    ): AttachmentApplication {
        $application = AttachmentApplication::query()
            ->where('institution_id', $reviewer->institution_id)
            ->findOrFail($applicationId);

        if (! in_array($application->status, ['SUBMITTED', 'UNDER_REVIEW'], true)) {
            throw ValidationException::withMessages(['status' => 'Application is not awaiting review.']);
        }

        $application->update([
            'status' => $decision === 'APPROVE' ? 'APPROVED' : 'REJECTED',
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => Carbon::now(),
            'review_notes' => $notes,
            'rejection_reason' => $decision === 'REJECT' ? $rejectionReason : null,
        ]);

        return $application->fresh(['student.person', 'term']);
    }

    /** @param array<string, mixed> $payload */
    public function createPlacement(User $coordinator, string $applicationId, array $payload): AttachmentPlacement
    {
        $application = AttachmentApplication::query()
            ->where('institution_id', $coordinator->institution_id)
            ->findOrFail($applicationId);

        if ($application->status !== 'APPROVED') {
            throw ValidationException::withMessages(['application' => 'Application must be approved before placement.']);
        }

        if ($application->placement !== null) {
            throw ValidationException::withMessages(['placement' => 'Placement already exists for this application.']);
        }

        $host = HostOrganization::query()
            ->where('institution_id', $coordinator->institution_id)
            ->where('is_active', true)
            ->findOrFail($payload['host_organization_id']);

        if ($host->mou_valid_until !== null && Carbon::parse($host->mou_valid_until)->isPast()) {
            throw ValidationException::withMessages(['host_organization_id' => 'Host organisation MOU has expired.']);
        }

        $placement = AttachmentPlacement::query()->create([
            'institution_id' => $application->institution_id,
            'application_id' => $application->id,
            'student_id' => $application->student_id,
            'host_organization_id' => $host->id,
            'university_supervisor_id' => $payload['university_supervisor_id'] ?? null,
            'field_supervisor_name' => $payload['field_supervisor_name'] ?? $host->contact_name,
            'field_supervisor_email' => $payload['field_supervisor_email'] ?? $host->contact_email,
            'field_supervisor_phone' => $payload['field_supervisor_phone'] ?? $host->contact_phone,
            'starts_on' => $payload['starts_on'],
            'ends_on' => $payload['ends_on'],
            'status' => 'PENDING_HOST',
        ]);

        $application->update(['status' => 'PLACED']);

        return $placement->load(['hostOrganization', 'student.person', 'universitySupervisor.person']);
    }

    public function confirmHostAcceptance(User $actor, string $placementId): AttachmentPlacement
    {
        $placement = $this->findInstitutionPlacement($actor, $placementId);

        if ($placement->status !== 'PENDING_HOST') {
            throw ValidationException::withMessages(['status' => 'Placement is not awaiting host confirmation.']);
        }

        $placement->update([
            'status' => 'ACTIVE',
            'host_accepted_at' => Carbon::now(),
            'activated_at' => Carbon::now(),
        ]);

        return $placement->fresh(['hostOrganization', 'student.person']);
    }

    /** @return Collection<int, AttachmentPlacement> */
    public function supervisedPlacements(User $supervisor): Collection
    {
        return AttachmentPlacement::query()
            ->where('institution_id', $supervisor->institution_id)
            ->where('university_supervisor_id', $supervisor->id)
            ->with(['student.person', 'hostOrganization', 'logbookEntries', 'assessments'])
            ->orderByDesc('starts_on')
            ->get();
    }

    /** @return Collection<int, AttachmentPlacement> */
    public function allPlacements(string $institutionId): Collection
    {
        return AttachmentPlacement::query()
            ->where('institution_id', $institutionId)
            ->with(['student.person', 'hostOrganization', 'universitySupervisor.person'])
            ->orderByDesc('starts_on')
            ->get();
    }

    public function findInstitutionPlacement(User $actor, string $placementId): AttachmentPlacement
    {
        return AttachmentPlacement::query()
            ->where('institution_id', $actor->institution_id)
            ->with(['hostOrganization', 'student.person', 'logbookEntries', 'assessments'])
            ->findOrFail($placementId);
    }

    public function findStudentPlacement(User $studentUser, string $placementId): AttachmentPlacement
    {
        $student = Student::query()->where('person_id', $studentUser->person_id)->firstOrFail();

        return AttachmentPlacement::query()
            ->where('student_id', $student->id)
            ->with(['hostOrganization', 'logbookEntries', 'assessments'])
            ->findOrFail($placementId);
    }

    private function isEligible(Student $student): bool
    {
        if ($student->status !== 'ACTIVE') {
            return false;
        }

        $activePlacement = AttachmentPlacement::query()
            ->where('student_id', $student->id)
            ->whereIn('status', ['PENDING_HOST', 'ACTIVE'])
            ->exists();

        return ! $activePlacement;
    }
}
