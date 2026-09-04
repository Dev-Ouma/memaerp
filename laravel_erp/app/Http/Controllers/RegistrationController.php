<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesCataloguePermission;
use App\Models\AdmissionApplication;
use App\Models\CourseEnrolment;
use App\Models\CpdEnrolment;
use App\Models\KuccpsPlacement;
use App\Models\LoginActivity;
use App\Models\MoodleSyncLog;
use App\Models\RegistrationPeriod;
use App\Models\RegistrationReminderCampaign;
use App\Models\Student;
use App\Models\StudentInfoUpdateRequest;
use App\Models\StudentPromotion;
use App\Models\User;
use App\Services\RegistrationFeesService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class RegistrationController extends Controller
{
    use AuthorizesCataloguePermission;

    public function applicationVerification(Request $request): View
    {
        $applications = $this->mapApplications();
        $stats = [
            'pendingVerification' => collect($applications)->filter(fn (array $row): bool => in_array($row['status'], ['SUBMITTED', 'UNDER_REVIEW', 'DRAFT'], true))->count(),
            'verifiedToday' => AdmissionApplication::query()->whereDate('updated_at', today())->whereIn('status', ['VERIFIED', 'SHORTLISTED'])->count(),
            'docAuthenticityRate' => $this->percent(collect($applications)->where('docs_status', '!=', '0 documents')->count(), max(1, count($applications))),
            'escalatedToKNEC' => collect($applications)->filter(fn (array $row): bool => str_contains(strtolower($row['status']), 'knec'))->count(),
        ];

        return view('registration.application-verification', compact('stats', 'applications'));
    }

    public function applicationApproval(Request $request): View
    {
        $approvals = $this->mapApplications(['SHORTLISTED', 'APPROVAL_PENDING', 'ADMITTED', 'ACCEPTED', 'READY_TO_ENROL', 'ENROLLED']);
        $stats = [
            'approvedThisIntake' => collect($approvals)->filter(fn (array $row): bool => in_array($row['status'], ['ADMITTED', 'ACCEPTED', 'ENROLLED'], true))->count(),
            'pendingDeanSignoff' => collect($approvals)->whereIn('status', ['SHORTLISTED', 'APPROVAL_PENDING'])->count(),
            'admissionLettersIssued' => collect($approvals)->whereIn('status', ['ADMITTED', 'ACCEPTED', 'ENROLLED'])->count(),
            'acceptanceFeePaid' => collect($approvals)->where('letter_status', 'Fee recorded')->count(),
        ];

        return view('registration.application-approval', compact('stats', 'approvals'));
    }

    public function rejectedList(Request $request): View
    {
        $rejected = $this->mapApplications(['REJECTED', 'WITHDRAWN']);
        $stats = [
            'totalRejected' => count($rejected),
            'clusterDeficit' => 0,
            'incompleteDocuments' => collect($rejected)->filter(fn (array $row): bool => str_contains(strtolower($row['rejection_reason'] ?? ''), 'document'))->count(),
            'appealsLodged' => 0,
        ];

        return view('registration.rejected-list', compact('stats', 'rejected'));
    }

    public function kuccpsRegistration(Request $request): View
    {
        $records = KuccpsPlacement::query()->latest()->get();
        $kuccpsRecords = $records->map(fn (KuccpsPlacement $row): array => [
            'kuccps_index' => $row->kuccps_index,
            'student_name' => $row->student_name,
            'placed_programme' => $row->placed_programme ?? '—',
            'gender' => $row->gender ?? '—',
            'county' => $row->county ?? '—',
            'cluster_points' => $row->cluster_points !== null ? (string) $row->cluster_points : '—',
            'mema_reg_no' => $row->mema_reg_no ?? '—',
            'reporting_status' => $row->reporting_status,
        ]);
        $reported = $records->filter(fn (KuccpsPlacement $row): bool => str_contains(strtolower($row->reporting_status), 'reported'))->count();
        $stats = [
            'totalKuccpsPlaced' => $records->count(),
            'reportedRegistered' => $reported,
            'placementReportingRate' => $this->percent($reported, max(1, $records->count())),
            'unclaimedSlots' => $records->filter(fn (KuccpsPlacement $row): bool => str_contains(strtolower($row->reporting_status), 'unclaimed'))->count(),
        ];

        return view('registration.kuccps-registration', compact('stats', 'kuccpsRecords'))->with('operationalCreate', [
            'title' => 'Add KUCCPS placement',
            'hint' => 'Persists to kuccps_placements.',
            'action' => route('registration.kuccps.store'),
            'fields' => [
                ['name' => 'kuccps_index', 'label' => 'KUCCPS index', 'required' => true],
                ['name' => 'student_name', 'label' => 'Student', 'required' => true],
                ['name' => 'placed_programme', 'label' => 'Placed programme'],
                ['name' => 'gender', 'label' => 'Gender'],
                ['name' => 'county', 'label' => 'County'],
                ['name' => 'cluster_points', 'label' => 'Cluster points', 'type' => 'number'],
                ['name' => 'mema_reg_no', 'label' => 'MEMA reg no'],
                ['name' => 'reporting_status', 'label' => 'Reporting status'],
            ],
        ]);
    }

    public function storeKuccps(Request $request): RedirectResponse
    {
        $this->authorizePermission($request, 'registration.manage');
        $data = $request->validate([
            'kuccps_index' => ['required', 'string', 'max:40', 'unique:kuccps_placements,kuccps_index'],
            'student_name' => ['required', 'string', 'max:190'],
            'placed_programme' => ['nullable', 'string', 'max:190'],
            'gender' => ['nullable', 'string', 'max:20'],
            'county' => ['nullable', 'string', 'max:80'],
            'cluster_points' => ['nullable', 'numeric', 'min:0'],
            'mema_reg_no' => ['nullable', 'string', 'max:40'],
            'reporting_status' => ['nullable', 'string', 'max:40'],
        ]);
        KuccpsPlacement::query()->create([
            ...$data,
            'reporting_status' => $data['reporting_status'] ?? 'Unclaimed',
            'student_id' => Student::query()->where('admission_number', $data['mema_reg_no'] ?? '')->value('id'),
        ]);

        return back()->with('success', 'KUCCPS placement saved.');
    }

    public function studentRegistrations(Request $request): View
    {
        $students = Student::query()->with(['user', 'course', 'academicSession'])->latest()->limit(300)->get()->map(function (Student $student): array {
            return [
                'id' => $student->id,
                'reg_no' => $student->admission_number,
                'student_name' => $student->user?->name ?? 'Unknown',
                'programme' => $student->course?->name ?? 'Unassigned',
                'cohort' => $student->academicSession?->start_date?->format('Y') ?? '—',
                'academic_year' => $student->academicSession
                    ? $student->academicSession->start_date->format('Y').'/'.$student->academicSession->end_date->format('Y')
                    : '—',
                'sponsor_type' => 'Institutional',
                'national_id' => '—',
                'registration_date' => $student->created_at?->format('d M Y') ?? '—',
                'status' => $student->user?->is_active ? 'ACTIVE' : 'INACTIVE',
            ];
        })->all();
        $stats = [
            'totalEnrolledScholars' => count($students),
            'newIntakeRegistered' => Student::query()->whereDate('created_at', '>=', now()->subDays(30))->count(),
            'biometricCompleted' => 0,
            'smartIdCardsIssued' => 0,
        ];

        return view('registration.student-registrations', compact('stats', 'students'));
    }

    public function courseRegistrationPeriods(Request $request): View
    {
        $records = RegistrationPeriod::query()->withCount('enrolments')->latest('starts_on')->get();
        $periods = $records->map(fn (RegistrationPeriod $period): array => [
            'session_code' => $period->code,
            'academic_session' => $period->title,
            'start_date' => $period->starts_on->format('d M Y'),
            'regular_deadline' => $period->regular_deadline->format('d M Y'),
            'late_registration_deadline' => $period->late_deadline?->format('d M Y') ?? '—',
            'min_max_units' => $period->min_units.' – '.$period->max_units.' units',
            'financial_gating' => $period->financial_gating ? 'Fee clearance required before confirm' : 'No financial gate',
            'status' => match ($period->status) {
                'OPEN' => 'Active / Open',
                'DRAFT' => 'Scheduled / Draft',
                'CLOSED' => 'Closed',
                default => $period->status,
            },
        ]);
        $open = $records->firstWhere('status', 'OPEN');
        $stats = [
            'activeRegistrationSession' => $open?->code ?? 'None open',
            'unitsRegisteredTotal' => CourseEnrolment::query()->where('status', 'REGISTERED')->count(),
            'addDropWindowCloses' => $open?->regular_deadline->format('d M Y') ?? '—',
            'lateRegistrationPenalty' => app(RegistrationFeesService::class)->money((float) ($open?->late_penalty_amount ?? 0)),
        ];

        $studentOptions = Student::query()->with('user')->orderBy('admission_number')->limit(200)->get()
            ->mapWithKeys(fn (Student $student): array => [
                $student->id => ($student->admission_number).' — '.($student->user?->name ?? 'Student'),
            ])->all();
        $periodOptions = $records->where('status', 'OPEN')->mapWithKeys(
            fn (RegistrationPeriod $period): array => [$period->id => $period->code.' — '.$period->title]
        )->all();

        return view('registration.course-registration-periods', compact('stats', 'periods'))->with([
            'operationalCreate' => [
                'title' => 'Open registration period',
                'hint' => 'Persists to registration_periods (domain table).',
                'action' => route('registration.periods.store'),
                'fields' => [
                    ['name' => 'code', 'label' => 'Period code', 'required' => true],
                    ['name' => 'title', 'label' => 'Title', 'required' => true],
                    ['name' => 'starts_on', 'label' => 'Start date', 'type' => 'date', 'required' => true],
                    ['name' => 'regular_deadline', 'label' => 'Regular deadline', 'type' => 'date', 'required' => true],
                    ['name' => 'late_deadline', 'label' => 'Late deadline', 'type' => 'date'],
                    ['name' => 'min_units', 'label' => 'Min units', 'type' => 'number'],
                    ['name' => 'max_units', 'label' => 'Max units', 'type' => 'number'],
                    ['name' => 'late_penalty_amount', 'label' => 'Late penalty (KES)', 'type' => 'number'],
                    ['name' => 'status', 'label' => 'Status (OPEN/DRAFT/CLOSED)'],
                ],
            ],
            'enrolmentCreate' => [
                'title' => 'Enrol student in open period',
                'hint' => 'Creates course_enrolments and auto-invoices when a matching fee structure exists.',
                'action' => route('registration.enrolments.store'),
                'fields' => [
                    ['name' => 'registration_period_id', 'label' => 'Open period', 'type' => 'select', 'required' => true, 'options' => $periodOptions],
                    ['name' => 'student_id', 'label' => 'Student', 'type' => 'select', 'required' => true, 'options' => $studentOptions],
                ],
            ],
        ]);
    }

    public function storePeriod(Request $request): RedirectResponse
    {
        $this->authorizePermission($request, 'registration.manage');
        $data = $request->validate([
            'code' => ['required', 'string', 'max:40', 'unique:registration_periods,code'],
            'title' => ['required', 'string', 'max:255'],
            'starts_on' => ['required', 'date'],
            'regular_deadline' => ['required', 'date', 'after_or_equal:starts_on'],
            'late_deadline' => ['nullable', 'date', 'after_or_equal:regular_deadline'],
            'min_units' => ['nullable', 'integer', 'min:0', 'max:30'],
            'max_units' => ['nullable', 'integer', 'min:1', 'max:40'],
            'late_penalty_amount' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'string', Rule::in(['DRAFT', 'OPEN', 'CLOSED', 'draft', 'open', 'closed'])],
        ]);
        app(RegistrationFeesService::class)->createPeriod($data);

        return back()->with('success', 'Registration period saved.');
    }

    public function storeEnrolment(Request $request): RedirectResponse
    {
        $this->authorizePermission($request, 'registration.manage');
        $data = $request->validate([
            'registration_period_id' => ['required', 'exists:registration_periods,id'],
            'student_id' => ['required', 'exists:students,id'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
        ]);
        $period = RegistrationPeriod::query()->findOrFail((int) $data['registration_period_id']);
        $student = Student::query()->findOrFail((int) $data['student_id']);
        app(RegistrationFeesService::class)->enrolStudent(
            $period,
            $student,
            isset($data['subject_id']) ? (int) $data['subject_id'] : null,
        );

        return back()->with('success', 'Student enrolled for the registration period.');
    }

    public function promotions(Request $request): View
    {
        $records = StudentPromotion::query()->latest()->get();
        $promotions = $records->map(fn (StudentPromotion $row): array => [
            'student_name' => $row->student_name,
            'reg_no' => $row->reg_no ?? '—',
            'programme' => $row->programme ?? '—',
            'from_stage' => $row->from_stage ?? '—',
            'to_stage' => $row->to_stage ?? '—',
            'cumulative_gpa' => $row->cumulative_gpa !== null ? (string) $row->cumulative_gpa : '—',
            'credits_passed' => $row->credits_passed !== null ? (string) $row->credits_passed : '—',
            'promotion_verdict' => $row->promotion_verdict,
            'senate_date' => $row->senate_date?->format('d M Y') ?? '—',
        ]);
        $stats = [
            'promotedToNextYear' => $records->filter(fn (StudentPromotion $r): bool => str_contains(strtolower($r->promotion_verdict), 'promoted'))->count(),
            'deansListHonours' => $records->filter(fn (StudentPromotion $r): bool => str_contains(strtolower($r->promotion_verdict), 'dean'))->count(),
            'academicWarning' => $records->filter(fn (StudentPromotion $r): bool => str_contains(strtolower($r->promotion_verdict), 'warning'))->count(),
            'repeatYearOrders' => $records->filter(fn (StudentPromotion $r): bool => str_contains(strtolower($r->promotion_verdict), 'repeat'))->count(),
        ];

        return view('registration.promotions', compact('stats', 'promotions'))->with('operationalCreate', [
            'title' => 'Record promotion decision',
            'hint' => 'Persists to student_promotions.',
            'action' => route('registration.promotions.store'),
            'fields' => [
                ['name' => 'student_name', 'label' => 'Student', 'required' => true],
                ['name' => 'reg_no', 'label' => 'Reg no'],
                ['name' => 'programme', 'label' => 'Programme'],
                ['name' => 'from_stage', 'label' => 'From stage'],
                ['name' => 'to_stage', 'label' => 'To stage'],
                ['name' => 'cumulative_gpa', 'label' => 'GPA', 'type' => 'number'],
                ['name' => 'credits_passed', 'label' => 'Credits passed', 'type' => 'number'],
                ['name' => 'promotion_verdict', 'label' => 'Verdict'],
                ['name' => 'senate_date', 'label' => 'Senate date', 'type' => 'date'],
            ],
        ]);
    }

    public function storePromotion(Request $request): RedirectResponse
    {
        $this->authorizePermission($request, 'registration.manage');
        $data = $request->validate([
            'student_name' => ['required', 'string', 'max:190'],
            'reg_no' => ['nullable', 'string', 'max:40'],
            'programme' => ['nullable', 'string', 'max:190'],
            'from_stage' => ['nullable', 'string', 'max:80'],
            'to_stage' => ['nullable', 'string', 'max:80'],
            'cumulative_gpa' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'credits_passed' => ['nullable', 'integer', 'min:0'],
            'promotion_verdict' => ['nullable', 'string', 'max:120'],
            'senate_date' => ['nullable', 'date'],
        ]);
        StudentPromotion::query()->create([
            ...$data,
            'promotion_verdict' => $data['promotion_verdict'] ?? 'Promoted',
            'student_id' => Student::query()->where('admission_number', $data['reg_no'] ?? '')->value('id'),
        ]);

        return back()->with('success', 'Promotion decision saved.');
    }

    public function professionalDevelopmentUsers(Request $request): View
    {
        $records = CpdEnrolment::query()->latest()->get();
        $cpdUsers = $records->map(fn (CpdEnrolment $row): array => [
            'participant_no' => $row->participant_no,
            'full_name' => $row->full_name,
            'organization' => $row->organization ?? '—',
            'course_enrolled' => $row->course_enrolled ?? '—',
            'completion_progress' => $row->completion_progress ?? '—',
            'cpd_points_awarded' => (string) $row->cpd_points_awarded,
            'certificate_ref' => $row->certificate_ref ?? '—',
            'status' => $row->status,
        ]);
        $stats = [
            'totalCPDEnrolled' => $records->count(),
            'activeShortCourses' => $records->filter(fn (CpdEnrolment $r): bool => str_contains(strtolower($r->status), 'active'))->count(),
            'certificatesAwarded' => $records->filter(fn (CpdEnrolment $r): bool => str_contains(strtolower($r->status), 'certified'))->count(),
            'corporateSponsors' => $records->filter(fn (CpdEnrolment $r): bool => str_contains(strtolower((string) $r->organization), 'ltd'))->count(),
        ];

        return view('registration.professional-development-users', compact('stats', 'cpdUsers'))->with('operationalCreate', [
            'title' => 'Enrol CPD participant',
            'hint' => 'Persists to cpd_enrolments.',
            'action' => route('registration.cpd.store'),
            'fields' => [
                ['name' => 'participant_no', 'label' => 'Participant no', 'required' => true],
                ['name' => 'full_name', 'label' => 'Name', 'required' => true],
                ['name' => 'organization', 'label' => 'Organization'],
                ['name' => 'course_enrolled', 'label' => 'Course'],
                ['name' => 'completion_progress', 'label' => 'Progress'],
                ['name' => 'cpd_points_awarded', 'label' => 'CPD points', 'type' => 'number'],
                ['name' => 'certificate_ref', 'label' => 'Certificate ref'],
                ['name' => 'status', 'label' => 'Status'],
            ],
        ]);
    }

    public function storeCpd(Request $request): RedirectResponse
    {
        $this->authorizePermission($request, 'registration.manage');
        $data = $request->validate([
            'participant_no' => ['required', 'string', 'max:40', 'unique:cpd_enrolments,participant_no'],
            'full_name' => ['required', 'string', 'max:190'],
            'organization' => ['nullable', 'string', 'max:190'],
            'course_enrolled' => ['nullable', 'string', 'max:190'],
            'completion_progress' => ['nullable', 'string', 'max:40'],
            'cpd_points_awarded' => ['nullable', 'numeric', 'min:0'],
            'certificate_ref' => ['nullable', 'string', 'max:80'],
            'status' => ['nullable', 'string', 'max:40'],
        ]);
        CpdEnrolment::query()->create([
            ...$data,
            'cpd_points_awarded' => (float) ($data['cpd_points_awarded'] ?? 0),
            'status' => $data['status'] ?? 'Active',
        ]);

        return back()->with('success', 'CPD enrolment saved.');
    }

    public function moodleSync(Request $request): View
    {
        $records = MoodleSyncLog::query()->latest()->get();
        $syncLogs = $records->map(fn (MoodleSyncLog $row): array => [
            'unit_code' => $row->unit_code,
            'unit_title' => $row->unit_title,
            'moodle_course_id' => $row->moodle_course_id ?? '—',
            'enrolled_students' => (string) $row->enrolled_students,
            'instructor_assigned' => $row->instructor_assigned ?? '—',
            'last_synced_at' => $row->synced_at?->format('d M Y H:i') ?? '—',
            'sync_status' => $row->sync_status,
        ]);
        $stats = [
            'syncedCourseUnits' => $records->count(),
            'syncedStudentEnrollments' => (int) $records->sum('enrolled_students'),
            'syncLatency' => $records->whereNotNull('synced_at')->count().' synced',
            'moodleApiStatus' => $records->filter(fn (MoodleSyncLog $r): bool => str_contains(strtoupper($r->sync_status), 'OK'))->isNotEmpty()
                ? 'Connected / OK'
                : 'Idle',
        ];

        return view('registration.moodle-sync', compact('stats', 'syncLogs'))->with('operationalCreate', [
            'title' => 'Log Moodle sync',
            'hint' => 'Persists to moodle_sync_logs.',
            'action' => route('registration.moodle.store'),
            'fields' => [
                ['name' => 'unit_code', 'label' => 'Unit code', 'required' => true],
                ['name' => 'unit_title', 'label' => 'Unit title', 'required' => true],
                ['name' => 'moodle_course_id', 'label' => 'Moodle course id'],
                ['name' => 'enrolled_students', 'label' => 'Enrolled students', 'type' => 'number'],
                ['name' => 'instructor_assigned', 'label' => 'Instructor'],
                ['name' => 'sync_status', 'label' => 'Sync status'],
            ],
        ]);
    }

    public function storeMoodle(Request $request): RedirectResponse
    {
        $this->authorizePermission($request, 'registration.manage');
        $data = $request->validate([
            'unit_code' => ['required', 'string', 'max:40'],
            'unit_title' => ['required', 'string', 'max:190'],
            'moodle_course_id' => ['nullable', 'string', 'max:80'],
            'enrolled_students' => ['nullable', 'integer', 'min:0'],
            'instructor_assigned' => ['nullable', 'string', 'max:190'],
            'sync_status' => ['nullable', 'string', 'max:40'],
        ]);
        MoodleSyncLog::query()->create([
            ...$data,
            'enrolled_students' => (int) ($data['enrolled_students'] ?? 0),
            'sync_status' => $data['sync_status'] ?? 'Pending',
            'synced_at' => str_contains(strtoupper((string) ($data['sync_status'] ?? '')), 'OK') ? now() : null,
        ]);

        return back()->with('success', 'Moodle sync log saved.');
    }

    public function studentInfoUpdate(Request $request): View
    {
        $records = StudentInfoUpdateRequest::query()->latest()->get();
        $updateRequests = $records->map(fn (StudentInfoUpdateRequest $row): array => [
            'request_no' => $row->request_no,
            'student_name' => $row->student_name,
            'reg_no' => $row->reg_no ?? '—',
            'update_type' => $row->update_type ?? '—',
            'current_details' => $row->current_details ?? '—',
            'requested_details' => $row->requested_details ?? '—',
            'supporting_doc' => 'On file',
            'verification_status' => $row->verification_status,
        ]);
        $stats = [
            'pendingUpdateRequests' => $records->filter(fn (StudentInfoUpdateRequest $r): bool => str_contains(strtolower($r->verification_status), 'pending'))->count(),
            'approvedThisTrimester' => $records->filter(fn (StudentInfoUpdateRequest $r): bool => str_contains(strtolower($r->verification_status), 'approved'))->count(),
            'gazetteNameAlterations' => $records->filter(fn (StudentInfoUpdateRequest $r): bool => str_contains(strtolower((string) $r->update_type), 'name'))->count(),
            'avgResolutionTime' => $records->count(),
        ];

        return view('registration.student-info-update', compact('stats', 'updateRequests'))->with('operationalCreate', [
            'title' => 'Submit info update request',
            'hint' => 'Persists to student_info_update_requests.',
            'action' => route('registration.info-updates.store'),
            'fields' => [
                ['name' => 'request_no', 'label' => 'Request no', 'required' => true],
                ['name' => 'student_name', 'label' => 'Student', 'required' => true],
                ['name' => 'reg_no', 'label' => 'Reg no'],
                ['name' => 'update_type', 'label' => 'Update type'],
                ['name' => 'current_details', 'label' => 'Current details', 'type' => 'textarea'],
                ['name' => 'requested_details', 'label' => 'Requested details', 'type' => 'textarea'],
                ['name' => 'verification_status', 'label' => 'Status'],
            ],
        ]);
    }

    public function storeInfoUpdate(Request $request): RedirectResponse
    {
        $this->authorizePermission($request, 'registration.manage');
        $data = $request->validate([
            'request_no' => ['required', 'string', 'max:40', 'unique:student_info_update_requests,request_no'],
            'student_name' => ['required', 'string', 'max:190'],
            'reg_no' => ['nullable', 'string', 'max:40'],
            'update_type' => ['nullable', 'string', 'max:80'],
            'current_details' => ['nullable', 'string'],
            'requested_details' => ['nullable', 'string'],
            'verification_status' => ['nullable', 'string', 'max:40'],
        ]);
        StudentInfoUpdateRequest::query()->create([
            ...$data,
            'verification_status' => $data['verification_status'] ?? 'Pending',
            'student_id' => Student::query()->where('admission_number', $data['reg_no'] ?? '')->value('id'),
        ]);

        return back()->with('success', 'Student info update request saved.');
    }

    public function reminders(Request $request): View
    {
        $records = RegistrationReminderCampaign::query()->latest()->get();
        $campaigns = $records->map(fn (RegistrationReminderCampaign $row): array => [
            'campaign_code' => $row->campaign_code,
            'title' => $row->title,
            'target_audience' => $row->target_audience ?? '—',
            'dispatch_channels' => $row->dispatch_channels ?? '—',
            'trigger_schedule' => $row->trigger_schedule ?? '—',
            'total_recipients' => (string) $row->total_recipients,
            'status' => $row->status,
        ]);
        $stats = [
            'activeAutomatedCampaigns' => $records->filter(fn (RegistrationReminderCampaign $r): bool => str_contains(strtolower($r->status), 'active'))->count(),
            'smsBroadcastDelivered' => $records->filter(fn (RegistrationReminderCampaign $r): bool => str_contains(strtoupper((string) $r->dispatch_channels), 'SMS'))->count(),
            'emailsDelivered' => $records->filter(fn (RegistrationReminderCampaign $r): bool => str_contains(strtolower((string) $r->dispatch_channels), 'email'))->count(),
            'deliverySuccessRate' => $this->percent(
                $records->filter(fn (RegistrationReminderCampaign $r): bool => str_contains(strtolower($r->status), 'sent'))->count(),
                max(1, $records->count()),
            ),
        ];

        return view('registration.reminders', compact('stats', 'campaigns'))->with('operationalCreate', [
            'title' => 'Create reminder campaign',
            'hint' => 'Persists to registration_reminder_campaigns.',
            'action' => route('registration.reminders.store'),
            'fields' => [
                ['name' => 'campaign_code', 'label' => 'Campaign code', 'required' => true],
                ['name' => 'title', 'label' => 'Title', 'required' => true],
                ['name' => 'target_audience', 'label' => 'Audience'],
                ['name' => 'dispatch_channels', 'label' => 'Channels'],
                ['name' => 'trigger_schedule', 'label' => 'Schedule'],
                ['name' => 'total_recipients', 'label' => 'Recipients', 'type' => 'number'],
                ['name' => 'status', 'label' => 'Status'],
            ],
        ]);
    }

    public function storeReminder(Request $request): RedirectResponse
    {
        $this->authorizePermission($request, 'registration.manage');
        $data = $request->validate([
            'campaign_code' => ['required', 'string', 'max:40', 'unique:registration_reminder_campaigns,campaign_code'],
            'title' => ['required', 'string', 'max:190'],
            'target_audience' => ['nullable', 'string', 'max:120'],
            'dispatch_channels' => ['nullable', 'string', 'max:120'],
            'trigger_schedule' => ['nullable', 'string', 'max:120'],
            'total_recipients' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'string', 'max:40'],
        ]);
        RegistrationReminderCampaign::query()->create([
            ...$data,
            'total_recipients' => (int) ($data['total_recipients'] ?? 0),
            'status' => $data['status'] ?? 'Draft',
        ]);

        return back()->with('success', 'Reminder campaign saved.');
    }

    public function userRegistration(Request $request): View
    {
        $users = User::query()->latest()->limit(300)->get()->map(function (User $user): array {
            return [
                'id' => $user->id,
                'user_code' => 'USR-'.$user->id,
                'full_name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'department' => $user->department ?? '—',
                'account_status' => $user->is_active ? 'Active' : 'Inactive',
                'last_login' => $user->last_successful_login_at?->format('d M Y H:i') ?? 'Never',
            ];
        })->all();
        $stats = [
            'totalRegisteredUsers' => User::query()->count(),
            'academicFacultyUsers' => User::query()->where('role', 'staff')->count(),
            'adminStaffUsers' => User::query()->where('role', 'admin')->count(),
            'activeMfaEnrolled' => 0,
        ];

        return view('registration.user-registration', compact('stats', 'users'));
    }

    public function studentPassword(Request $request): View
    {
        $passwordLogs = User::query()->where('role', 'student')->with(['student.course'])->latest()->limit(200)->get()->map(function (User $user): array {
            return [
                'id' => $user->id,
                'student_name' => $user->name,
                'reg_no' => $user->student?->admission_number ?? '—',
                'programme' => $user->student?->course?->name ?? '—',
                'password_status' => $user->is_active ? 'Active' : 'Locked',
                'last_changed' => $user->password_changed_at?->format('d M Y') ?? 'Never',
                'failed_attempts' => 0,
                'mfa_channel' => 'Email OTP',
                'actions' => 'Reset from account security',
            ];
        })->all();
        $stats = [
            'studentAccounts' => count($passwordLogs),
            'passwordsResetToday' => 0,
            'lockedAccounts' => collect($passwordLogs)->where('password_status', 'Locked')->count(),
            'selfServiceSuccess' => 0,
        ];

        return view('registration.student-password', compact('stats', 'passwordLogs'));
    }

    public function staffPassword(Request $request): View
    {
        $staffPasswords = User::query()->whereIn('role', ['staff', 'admin'])->with('staffProfile')->latest()->limit(200)->get()->map(function (User $user): array {
            return [
                'id' => $user->id,
                'staff_name' => $user->name,
                'staff_no' => $user->staffProfile?->staff_no ?? 'EMP-'.$user->id,
                'designation' => $user->title ?? $user->staffProfile?->designation ?? 'Staff',
                'email' => $user->email,
                'privilege_tier' => $user->role,
                'password_age_days' => $user->password_changed_at ? (int) $user->password_changed_at->diffInDays(now()) : 0,
                'mfa_type' => 'Password',
                'status' => $user->is_active ? 'Active' : 'Inactive',
            ];
        })->all();
        $stats = [
            'totalStaffAccounts' => count($staffPasswords),
            'mfaEnforcedRate' => '0%',
            'expiringIn30Days' => 0,
            'privilegedSecurityTiers' => collect($staffPasswords)->where('privilege_tier', 'admin')->count(),
        ];

        return view('registration.staff-password', compact('stats', 'staffPasswords'));
    }

    public function passwordReset(Request $request): View
    {
        $resetAudits = LoginActivity::query()->with('user')->latest('occurred_at')->limit(200)->get()->map(function (LoginActivity $activity): array {
            return [
                'id' => $activity->id,
                'audit_no' => 'AUD-'.$activity->id,
                'account_user' => $activity->email_or_username,
                'role_type' => $activity->user?->role ?? 'unknown',
                'reset_method' => $activity->status,
                'ip_address' => $activity->ip_address,
                'timestamp' => $activity->occurred_at?->format('d M Y H:i') ?? '—',
                'security_verdict' => $activity->status,
            ];
        })->all();
        $stats = [
            'totalResetRequests' => count($resetAudits),
            'selfServiceOtpResets' => collect($resetAudits)->filter(fn (array $row): bool => str_contains(strtolower((string) $row['reset_method']), 'success'))->count(),
            'helpdeskAssisted' => 0,
            'securityBreachAttempts' => collect($resetAudits)->filter(fn (array $row): bool => str_contains(strtolower((string) $row['security_verdict']), 'fail'))->count(),
        ];

        return view('registration.password-reset', compact('stats', 'resetAudits'));
    }

    /**
     * @param  list<string>|null  $statuses
     * @return list<array<string, mixed>>
     */
    private function mapApplications(?array $statuses = null): array
    {
        $query = AdmissionApplication::query()->with(['applicant.user', 'offering.course', 'offering.intake', 'documents', 'payments']);
        if ($statuses !== null) {
            $query->whereIn('status', $statuses);
        }

        return $query->latest()->limit(300)->get()->map(function (AdmissionApplication $application): array {
            $paid = $application->payments->where('status', 'PAID')->isNotEmpty();

            return [
                'id' => $application->id,
                'app_no' => $application->application_number,
                'applicant_name' => $application->applicant?->user?->name ?? 'Unknown applicant',
                'programme' => $application->offering?->course?->name ?? 'Unassigned',
                'school' => $application->offering?->campus ?? '—',
                'entry_qualifications' => 'Application file',
                'docs_status' => ($application->documents->count()).' documents',
                'verification_stage' => $application->status,
                'status' => $application->status,
                'academic_year' => $application->offering?->intake?->name ?? '—',
                'intake_session' => $application->offering?->study_mode ?? '—',
                'committee_verdict' => $application->status,
                'dean_signoff' => in_array($application->status, ['ADMITTED', 'ACCEPTED', 'ENROLLED'], true) ? 'Signed' : 'Pending',
                'letter_status' => $paid ? 'Fee recorded' : 'Awaiting fee',
                'rejection_reason' => $application->status,
                'alternative_offered' => '—',
                'rejection_date' => $application->decision_at?->format('d M Y') ?? '—',
                'appeal_status' => 'None',
            ];
        })->all();
    }

    private function percent(int $part, int $whole): string
    {
        return round(($part / max(1, $whole)) * 100, 1).'%';
    }
}
