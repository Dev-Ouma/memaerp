<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesAdmissionAccess;
use App\Models\Admission\StudentConversion;
use App\Models\AdmissionApplication;
use App\Models\AdmissionOffer;
use App\Models\ApplicationDocument;
use App\Models\ApplicationPaymentAttempt;
use App\Models\ApplicationReview;
use App\Models\AuditLog;
use App\Modules\Admission\Services\AdmissionPipeline;
use App\Modules\Admission\Workspaces\AdmissionRollWorkspace;
use App\Modules\Admission\Workspaces\ApprovalWorkspace;
use App\Modules\Admission\Workspaces\AuditWorkspace;
use App\Modules\Admission\Workspaces\DocumentVerificationWorkspace;
use App\Modules\Admission\Workspaces\OfferWorkspace;
use App\Modules\Admission\Workspaces\PaymentReconciliationWorkspace;
use App\Modules\Admission\Workspaces\PaymentWorkspace;
use App\Modules\Admission\Workspaces\ReviewWorkspace;
use App\Modules\Admission\Workspaces\ShortlistWorkspace;
use App\Modules\Admission\Workspaces\WaitlistWorkspace;
use App\Modules\Admission\Workspaces\WorkQueueWorkspace;
use App\Services\AdmissionWorkflow;
use App\Services\DocumentTemplateService;
use App\Services\StudentConversionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

final class AdmissionAdminController extends Controller
{
    use AuthorizesAdmissionAccess;

    public function index(Request $request): View
    {
        abort_unless(in_array($request->user()->role, ['admin', 'staff'], true), 403);
        $this->authorizeAdmission($request, 'admission.application.view', 'admission.application.view_any');
        $query = AdmissionApplication::with(['applicant.user', 'offering.course', 'payments', 'documents'])->latest();
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->input('payment') === 'paid') {
            $query->whereHas('payments', fn ($payment) => $payment->whereIn('status', ['PAID', 'WAIVED']));
        }
        if ($request->filled('q')) {
            $query->where(fn ($q) => $q->where('application_number', 'ilike', '%'.$request->q.'%')->orWhereHas('applicant.user', fn ($u) => $u->where('name', 'ilike', '%'.$request->q.'%')));
        }$applications = $query->paginate(20)->withQueryString();
        $funnel = AdmissionApplication::selectRaw('status,count(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('admissions.admin.index', compact('applications', 'funnel'));
    }

    /**
     * The conversion ledger: every application that has crossed into academic
     * records, plus the ones that tried and failed.
     */
    public function conversions(Request $request): View
    {
        $this->authorizeStaff($request);
        $query = StudentConversion::with(['application.applicant.user', 'application.offering.course', 'student', 'convertedBy'])->latest();
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        $conversions = $query->paginate(20)->withQueryString();
        $tally = StudentConversion::selectRaw('status,count(*) as total')->groupBy('status')->pluck('total', 'status');
        $awaiting = AdmissionApplication::where('status', 'READY_TO_ENROL')->count();

        return view('admissions.admin.conversions', compact('conversions', 'tally', 'awaiting'));
    }

    /**
     * Re-run a conversion that failed. Completed rows are left untouched by the
     * service, so a mistaken retry cannot mint a second student.
     */
    public function retryConversion(Request $request, StudentConversion $conversion, StudentConversionService $conversions): RedirectResponse
    {
        abort_unless(in_array($request->user()->role, ['admin', 'staff'], true), 403);
        $this->authorizeAdmission($request, 'admission.conversion.execute');
        abort_unless($conversion->status === 'FAILED', 409, 'Only a failed conversion can be retried.');

        $conversion = $conversions->convert($conversion->application, $request->user()->id);

        return back()->with('success', "Conversion completed. Student number {$conversion->student_number} issued.");
    }

    public function show(Request $request, AdmissionApplication $application): View
    {
        abort_unless(in_array($request->user()->role, ['admin', 'staff'], true), 403);
        $this->authorizeAdmission($request, 'admission.application.view', 'admission.application.view_any');
        $application->load(['applicant.user', 'offering.course', 'offering.intake', 'payments', 'documents', 'histories', 'reviews', 'offer']);

        return view('admissions.admin.show', compact('application'));
    }

    public function analytics(Request $request): View
    {
        $this->authorizeStaff($request);
        $total = AdmissionApplication::count();
        $paid = AdmissionApplication::whereHas('payments', fn ($query) => $query->whereIn('status', ['PAID', 'WAIVED']))->count();
        $submitted = AdmissionApplication::whereNotIn('status', ['DRAFT', 'WITHDRAWN'])->count();
        $admitted = AdmissionApplication::whereIn('status', ['ADMITTED', 'ACCEPTED', 'READY_TO_ENROL', 'ENROLLED'])->count();
        $statusBreakdown = AdmissionApplication::selectRaw('status, count(*) as total')->groupBy('status')->orderByDesc('total')->get();
        $programmePerformance = AdmissionApplication::query()
            ->join('programme_offerings', 'programme_offerings.id', '=', 'admission_applications.programme_offering_id')
            ->join('courses', 'courses.id', '=', 'programme_offerings.course_id')
            ->selectRaw("courses.name, count(*) as applications, sum(case when admission_applications.status in ('ADMITTED','ACCEPTED','READY_TO_ENROL','ENROLLED') then 1 else 0 end) as admitted")
            ->groupBy('courses.id', 'courses.name')->orderByDesc('applications')->get();

        return view('admissions.admin.analytics', compact('total', 'paid', 'submitted', 'admitted', 'statusBreakdown', 'programmePerformance'));
    }

    public function reports(Request $request): View
    {
        $this->authorizeStaff($request);

        $totalApps = AdmissionApplication::query()->count();
        $submittedApps = AdmissionApplication::query()->whereNotIn('status', ['DRAFT', 'WITHDRAWN'])->count();
        $verifiedApps = AdmissionApplication::query()->whereIn('status', ['VERIFIED', 'SHORTLISTED', 'APPROVAL_PENDING', 'ADMITTED', 'ACCEPTED', 'READY_TO_ENROL', 'ENROLLED'])->count();
        $shortlistedApps = AdmissionApplication::query()->whereIn('status', ['SHORTLISTED', 'APPROVAL_PENDING', 'ADMITTED', 'ACCEPTED', 'READY_TO_ENROL', 'ENROLLED'])->count();
        $offersCount = AdmissionOffer::query()->count();
        $acceptedCount = AdmissionApplication::query()->whereIn('status', ['ACCEPTED', 'READY_TO_ENROL', 'ENROLLED'])->count();
        $enrolledCount = AdmissionApplication::query()->where('status', 'ENROLLED')->count();
        $waitlistedCount = AdmissionApplication::query()->where('status', 'WAITLISTED')->count();
        $paymentRevenue = (float) ApplicationPaymentAttempt::query()->where('status', 'PAID')->sum('amount');

        $reportStats = [
            'applications' => $totalApps,
            'submitted' => $submittedApps,
            'verified' => $verifiedApps,
            'shortlisted' => $shortlistedApps,
            'offers' => $offersCount,
            'accepted' => $acceptedCount,
            'enrolled' => $enrolledCount,
            'revenue' => $paymentRevenue,
            'yieldRate' => round(($acceptedCount / max(1, $offersCount)) * 100, 1).'%',
            'conversionRate' => round(($enrolledCount / max(1, $totalApps)) * 100, 1).'%',
        ];

        $monthlyTrends = collect(range(5, 0))->map(function (int $monthsAgo): array {
            $start = now()->subMonths($monthsAgo)->startOfMonth();
            $end = (clone $start)->endOfMonth();
            $apps = AdmissionApplication::query()->whereBetween('created_at', [$start, $end])->count();
            $admissions = AdmissionApplication::query()
                ->whereIn('status', ['ADMITTED', 'ACCEPTED', 'READY_TO_ENROL', 'ENROLLED'])
                ->where(function ($query) use ($start, $end): void {
                    $query->whereBetween('decision_at', [$start, $end])
                        ->orWhere(function ($inner) use ($start, $end): void {
                            $inner->whereNull('decision_at')
                                ->whereBetween('updated_at', [$start, $end]);
                        });
                })
                ->count();
            $revenue = (float) ApplicationPaymentAttempt::query()
                ->where('status', 'PAID')
                ->whereBetween('paid_at', [$start, $end])
                ->sum('amount');

            return [
                'month' => $start->format('M'),
                'month_label' => $start->format('F Y'),
                'applications' => $apps,
                'admissions' => $admissions,
                'revenue' => $revenue,
            ];
        })->values()->all();

        $programmeQuotas = AdmissionApplication::query()
            ->with(['offering.course', 'offering.intake'])
            ->get()
            ->groupBy(fn (AdmissionApplication $app) => $app->programme_offering_id)
            ->map(function ($group) {
                /** @var AdmissionApplication $sample */
                $sample = $group->first();
                $course = $sample?->offering?->course;
                $capacity = (int) ($sample?->offering?->capacity ?? 0);
                $applied = $group->count();
                $admitted = $group->whereIn('status', ['ADMITTED', 'ACCEPTED', 'READY_TO_ENROL', 'ENROLLED'])->count();
                $fill = $capacity > 0 ? (int) round(($admitted / $capacity) * 100) : 0;

                return [
                    'name' => $course?->name ?? 'Unassigned programme',
                    'code' => $course?->code ?? '—',
                    'school' => '—',
                    'capacity' => $capacity,
                    'applied' => $applied,
                    'admitted' => $admitted,
                    'fill' => $fill,
                ];
            })
            ->sortByDesc('applied')
            ->values()
            ->take(20)
            ->all();

        $statusParts = [
            ['label' => 'Enrolled', 'count' => $enrolledCount, 'color' => '#1E8449'],
            ['label' => 'Admitted / Offer Accepted', 'count' => max(0, $acceptedCount - $enrolledCount), 'color' => '#0A3E50'],
            ['label' => 'Under Faculty Review', 'count' => max(0, $submittedApps - $verifiedApps), 'color' => '#2563eb'],
            ['label' => 'Shortlisted', 'count' => max(0, $shortlistedApps - max($offersCount, $acceptedCount)), 'color' => '#9333ea'],
            ['label' => 'Waitlisted', 'count' => $waitlistedCount, 'color' => '#d97706'],
            ['label' => 'Drafts / Incomplete', 'count' => max(0, $totalApps - $submittedApps), 'color' => '#64748b'],
        ];
        $statusTotal = max(1, array_sum(array_column($statusParts, 'count')));
        $statusBreakdown = array_map(static function (array $row) use ($statusTotal): array {
            $row['percent'] = (int) round(($row['count'] / $statusTotal) * 100);

            return $row;
        }, $statusParts);

        $pipelineReport = AdmissionApplication::query()
            ->with(['applicant.user', 'offering.course', 'offering.intake', 'payments'])
            ->latest()
            ->limit(50)
            ->get()
            ->map(static function (AdmissionApplication $app): array {
                return [
                    'id' => $app->id,
                    'ref' => $app->application_number,
                    'name' => $app->applicant?->user?->name ?? 'Unknown',
                    'email' => $app->applicant?->user?->email ?? '—',
                    'phone' => $app->applicant?->phone ?? '—',
                    'programme' => $app->offering?->course?->name ?? '—',
                    'intake' => $app->offering?->intake?->name ?? '—',
                    'campus' => $app->offering?->campus ?? '—',
                    'payment' => $app->isPaid() ? 'PAID' : 'PENDING',
                    'status' => $app->status,
                ];
            })->all();

        $documentAuditReport = ApplicationDocument::query()
            ->with(['application.applicant.user', 'application'])
            ->latest()
            ->limit(50)
            ->get()
            ->map(static function (ApplicationDocument $doc): array {
                $app = $doc->application;

                return [
                    'id' => $doc->id,
                    'ref' => $app?->application_number ?? '—',
                    'name' => $app?->applicant?->user?->name ?? 'Unknown',
                    'doc_type' => $doc->document_type,
                    'filename' => $doc->original_name,
                    'sha256' => $doc->sha256 ?: '—',
                    'status' => $doc->verification_status ?: 'PENDING',
                    'verified_by' => $doc->verified_by ?: '—',
                    'note' => '—',
                ];
            })->all();

        $offersReport = AdmissionOffer::query()
            ->with(['application.applicant.user', 'application.offering.course'])
            ->latest('issued_at')
            ->limit(50)
            ->get()
            ->map(static function (AdmissionOffer $offer): array {
                $app = $offer->application;

                return [
                    'id' => $offer->id,
                    'offer_ref' => $offer->offer_number,
                    'app_ref' => $app?->application_number ?? '—',
                    'name' => $app?->applicant?->user?->name ?? 'Unknown',
                    'programme' => $app?->offering?->course?->name ?? '—',
                    'issued_date' => optional($offer->issued_at)->format('d M Y') ?: '—',
                    'deadline' => optional($offer->expires_at)->format('d M Y') ?: '—',
                    'status' => $offer->status,
                ];
            })->all();

        $paymentBatchesReport = ApplicationPaymentAttempt::query()
            ->with(['application.applicant.user'])
            ->latest()
            ->limit(50)
            ->get()
            ->map(static function (ApplicationPaymentAttempt $payment): array {
                $app = $payment->application;

                return [
                    'id' => $payment->id,
                    'ref' => $app?->application_number ?? '—',
                    'name' => $app?->applicant?->user?->name ?? 'Unknown',
                    'channel' => $payment->channel ?: ($payment->provider ?: '—'),
                    'phone' => $payment->payer_msisdn_masked ?: '—',
                    'trans_id' => $payment->provider_request_ref ?: ($payment->reference ?: '—'),
                    'receipt' => $payment->receipt_number ?: '—',
                    'amount' => number_format((float) $payment->amount, 0).' '.($payment->currency ?: 'KES'),
                    'status' => $payment->status,
                    'date' => optional($payment->paid_at ?? $payment->created_at)->format('d M Y H:i') ?: '—',
                ];
            })->all();

        $meritCutoffsReport = AdmissionApplication::query()
            ->with(['applicant.user', 'offering.course'])
            ->whereNotNull('form_data')
            ->latest()
            ->limit(50)
            ->get()
            ->map(static function (AdmissionApplication $app): array {
                $form = is_array($app->form_data) ? $app->form_data : [];
                $mean = $form['mean_grade'] ?? $form['kcse_mean_grade'] ?? '—';
                $cluster = $form['cluster_points'] ?? $form['cluster'] ?? '—';
                $cutoff = $form['cutoff'] ?? '—';

                return [
                    'id' => $app->id,
                    'name' => $app->applicant?->user?->name ?? 'Unknown',
                    'mean_grade' => is_scalar($mean) ? (string) $mean : '—',
                    'cluster' => is_scalar($cluster) ? (string) $cluster : '—',
                    'cutoff' => is_scalar($cutoff) ? (string) $cutoff : '—',
                    'variance' => '—',
                    'programme' => $app->offering?->course?->name ?? '—',
                    'outcome' => $app->status,
                ];
            })->all();

        $conversionsReport = StudentConversion::query()
            ->with(['application.applicant.user', 'application.offering.course', 'student'])
            ->latest()
            ->limit(50)
            ->get()
            ->map(static function (StudentConversion $conversion): array {
                $app = $conversion->application;

                return [
                    'id' => $conversion->id,
                    'student_no' => $conversion->student_number
                        ?: ($conversion->student?->admission_number ?? '—'),
                    'app_ref' => $app?->application_number ?? '—',
                    'name' => $app?->applicant?->user?->name ?? 'Unknown',
                    'programme' => $app?->offering?->course?->name ?? '—',
                    'school' => '—',
                    'enrol_date' => optional($conversion->converted_at ?? $conversion->created_at)->format('d M Y') ?: '—',
                    'status' => $conversion->status ?? 'CONVERTED',
                ];
            })->all();

        if ($conversionsReport === []) {
            $conversionsReport = AdmissionApplication::query()
                ->with(['applicant.user', 'offering.course'])
                ->where('status', 'ENROLLED')
                ->latest()
                ->limit(50)
                ->get()
                ->map(static function (AdmissionApplication $app): array {
                    return [
                        'id' => $app->id,
                        'student_no' => '—',
                        'app_ref' => $app->application_number,
                        'name' => $app->applicant?->user?->name ?? 'Unknown',
                        'programme' => $app->offering?->course?->name ?? '—',
                        'school' => '—',
                        'enrol_date' => optional($app->decision_at ?? $app->updated_at)->format('d M Y') ?: '—',
                        'status' => 'ENROLLED',
                    ];
                })->all();
        }

        $statutoryReturnsReport = collect($programmeQuotas)->map(static function (array $row): array {
            return [
                'id' => $row['code'],
                'programme' => $row['name'],
                'male' => 0,
                'female' => 0,
                'special_needs' => 0,
                'counties' => 0,
                'total' => $row['admitted'],
                'accreditation' => 'From programme register',
            ];
        })->all();

        $monthlyChartData = [];
        foreach ($monthlyTrends as $index => $row) {
            $prev = $monthlyTrends[$index - 1]['applications'] ?? 0;
            $delta = $prev > 0 ? round((($row['applications'] - $prev) / $prev) * 100, 1) : 0.0;
            $sign = $delta >= 0 ? '+' : '';
            $monthlyChartData[] = [
                'month' => $row['month_label'] ?? $row['month'],
                'apps' => $row['applications'],
                'adm' => $row['admissions'],
                'vel' => $sign.$delta.'%',
                'rev' => 'KES '.number_format((float) $row['revenue'], 0),
            ];
        }

        $recentDecisions = AdmissionApplication::with(['applicant.user', 'offering.course'])
            ->latest()->limit(8)->get();

        return view('admissions.admin.reports', compact(
            'reportStats',
            'monthlyTrends',
            'monthlyChartData',
            'programmeQuotas',
            'statusBreakdown',
            'pipelineReport',
            'documentAuditReport',
            'offersReport',
            'paymentBatchesReport',
            'meritCutoffsReport',
            'conversionsReport',
            'statutoryReturnsReport',
            'recentDecisions'
        ));
    }

    public function exportApplications(Request $request): Response
    {
        $this->authorizeStaff($request);
        $rows = AdmissionApplication::with(['applicant.user', 'offering.course', 'payments'])->orderBy('application_number')->get();
        $stream = fopen('php://temp', 'r+');
        fputcsv($stream, ['Application number', 'Applicant', 'Email', 'Programme', 'Status', 'Payment', 'Submitted at']);
        foreach ($rows as $application) {
            fputcsv($stream, [$application->application_number, $application->applicant->user->name, $application->applicant->user->email, $application->offering->course->name, $application->status, $application->isPaid() ? 'PAID' : 'PENDING', $application->submitted_at?->toIso8601String()]);
        }
        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);

        return response($csv, 200, ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="mema-admissions-'.now()->format('Y-m-d').'.csv"']);
    }

    public function review(Request $request, AdmissionApplication $application): RedirectResponse
    {
        abort_unless(in_array($request->user()->role, ['admin', 'staff'], true), 403);
        $this->authorizeAdmission($request, 'admission.review.perform');
        $data = $request->validate(['score' => ['required', 'integer', 'min:0', 'max:100'], 'recommendation' => ['required', 'in:verify,shortlist,waitlist,reject'], 'notes' => ['required', 'string', 'max:3000']]);
        $review = ApplicationReview::create(['admission_application_id' => $application->id, 'reviewer_id' => $request->user()->id, 'stage' => 'academic', 'score' => $data['score'], 'recommendation' => $data['recommendation'], 'notes' => $data['notes'], 'created_at' => now()]);
        AuditLog::record('admission.review_recorded', $review, null, $review->toArray());

        return back()->with('success', 'Review evidence recorded.');
    }

    public function transition(Request $request, AdmissionApplication $application, AdmissionWorkflow $workflow): RedirectResponse
    {
        abort_unless(in_array($request->user()->role, ['admin', 'staff'], true), 403);
        $data = $request->validate(['status' => ['required', 'string'], 'reason' => ['required', 'string', 'max:80'], 'note' => ['required', 'string', 'max:2000']]);

        $permission = match ($data['status']) {
            'ADMITTED', 'ADMITTED_CONDITIONAL', 'REJECTED' => 'admission.decision.final',
            'APPROVAL_PENDING' => 'admission.decision.approve',
            'SHORTLISTED', 'WAITLISTED' => 'admission.shortlist.manage',
            'VERIFIED' => 'admission.document.verify',
            'UNDER_REVIEW' => 'admission.review.perform',
            default => 'admission.decision.recommend',
        };
        $this->authorizeAdmission($request, $permission, 'admission.decision.final');

        $workflow->move($application, $data['status'], $data['reason'], $data['note']);

        return back()->with('success', 'Application moved to '.str_replace('_', ' ', $data['status']).'.');
    }

    public function admissionLetter(Request $request, AdmissionApplication $application): View
    {
        $user = $request->user();
        if ($user->role === 'applicant') {
            abort_unless($application->applicant->user_id === $user->id, 403);
        } else {
            abort_unless(in_array($user->role, ['admin', 'staff'], true), 403);
            $this->authorizeAdmission($request, 'admission.letter.generate', 'admission.offer.view');
        }

        $application->load(['applicant.user', 'offering.course', 'offering.intake', 'offer']);
        $offer = $application->offer;
        abort_unless($offer !== null, 404, 'No admission offer has been issued for this application yet.');
        abort_unless(in_array($application->status, ['ADMITTED', 'ACCEPTED', 'READY_TO_ENROL', 'ENROLLED'], true), 409, 'The admission letter is available only after the applicant is admitted.');

        $payload = app(DocumentTemplateService::class)->resolvePayload($application);

        return view('templates.documents.admission_letter', [
            'payload' => $payload,
            'application' => $application,
            'offer' => $offer,
            'standalone' => true,
        ]);
    }

    public function downloadDocument(Request $request, ApplicationDocument $document)
    {
        $user = $request->user();
        $application = $document->application;
        if ($user->role === 'applicant') {
            abort_unless($application->applicant->user_id === $user->id, 403);
        } else {
            abort_unless(in_array($user->role, ['admin', 'staff'], true), 403);
            $this->authorizeAdmission($request, 'admission.document.download', 'admission.document.view');
        }

        if (Storage::disk('local')->exists($document->storage_path)) {
            return Storage::disk('local')->download($document->storage_path, $document->original_name);
        }

        return response("Document content for {$document->original_name}", 200, [
            'Content-Type' => $document->mime_type ?? 'text/plain',
            'Content-Disposition' => "inline; filename=\"{$document->original_name}\"",
        ]);
    }

    public function verifyDocument(Request $request, ApplicationDocument $document, AdmissionPipeline $pipeline): RedirectResponse
    {
        abort_unless(in_array($request->user()->role, ['admin', 'staff'], true), 403);
        $this->authorizeAdmission($request, 'admission.document.verify');
        $data = $request->validate([
            'status' => ['required', 'in:VERIFIED,REJECTED,PENDING'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        // PENDING is a reset, not a verifier's verdict, so it leaves no history row.
        $data['status'] === 'PENDING'
            ? $document->update(['verification_status' => 'PENDING', 'verified_by' => null])
            : $pipeline->verifyDocument($document, $data['status'], $request->user()->id, $data['note'] ?? null);

        AuditLog::record('admission.document_verified', $document, null, [
            'status' => $data['status'],
            'verified_by' => $request->user()->id,
            'note' => $data['note'] ?? null,
        ]);

        return back()->with('success', "Document status updated to {$data['status']}.");
    }

    public function convertToStudent(Request $request, AdmissionApplication $application, StudentConversionService $conversions): RedirectResponse
    {
        abort_unless(in_array($request->user()->role, ['admin', 'staff'], true), 403);
        $this->authorizeAdmission($request, 'admission.conversion.execute');
        abort_unless(in_array($application->status, ['ADMITTED', 'ACCEPTED', 'READY_TO_ENROL', 'ENROLLED'], true), 400, 'Application must be admitted or accepted before converting to student.');

        $conversion = $conversions->convert($application, $request->user()->id);

        return back()->with('success', "Student record created successfully. Assigned registration number: {$conversion->student_number}.");
    }

    /**
     * 1. Admissions Command Dashboard Workspace
     */
    public function dashboard(Request $request): View
    {
        $this->authorizeStaff($request);

        $totalApps = AdmissionApplication::count();
        $verifiedCount = AdmissionApplication::where('status', 'VERIFIED')->count();
        $offersCount = DB::table('admission_offers')->count();
        $enrolledCount = AdmissionApplication::whereIn('status', ['READY_TO_ENROL', 'ENROLLED'])->count();
        $totalRevenue = DB::table('application_payment_attempts')->where('status', 'PAID')->sum('amount');
        $slaTracked = AdmissionApplication::whereNotNull('sla_due_at')->count();
        $slaOverdue = AdmissionApplication::whereNotNull('sla_due_at')
            ->where('sla_due_at', '<', now())
            ->whereNotIn('status', ['ADMITTED', 'ACCEPTED', 'READY_TO_ENROL', 'ENROLLED', 'REJECTED', 'DECLINED', 'WITHDRAWN'])
            ->count();

        $stats = [
            'totalApplications' => $totalApps,
            'verifiedApplicants' => $verifiedCount,
            'offersIssued' => $offersCount,
            'matriculatedEnrolled' => $enrolledCount,
            'revenueCollected' => $totalRevenue,
            'conversionRate' => $totalApps > 0 ? round(($enrolledCount / $totalApps) * 100, 1) : 0.0,
            'acceptanceRate' => $offersCount > 0 ? round(($enrolledCount / $offersCount) * 100, 1) : 0.0,
            'slaCompliance' => ($slaTracked > 0 ? round((($slaTracked - $slaOverdue) / $slaTracked) * 100, 1) : 0.0).'%',
        ];

        $funnel = [
            'DRAFT' => AdmissionApplication::where('status', 'DRAFT')->count(),
            'SUBMITTED' => AdmissionApplication::where('status', 'SUBMITTED')->count(),
            'UNDER_REVIEW' => AdmissionApplication::where('status', 'UNDER_REVIEW')->count(),
            'VERIFIED' => AdmissionApplication::where('status', 'VERIFIED')->count(),
            'SHORTLISTED' => AdmissionApplication::where('status', 'SHORTLISTED')->count(),
            'APPROVAL_PENDING' => AdmissionApplication::where('status', 'APPROVAL_PENDING')->count(),
            'ADMITTED' => AdmissionApplication::where('status', 'ADMITTED')->count(),
            'ACCEPTED' => AdmissionApplication::where('status', 'ACCEPTED')->count(),
            'READY_TO_ENROL' => AdmissionApplication::where('status', 'READY_TO_ENROL')->count(),
            'ENROLLED' => AdmissionApplication::where('status', 'ENROLLED')->count(),
            'REJECTED' => AdmissionApplication::where('status', 'REJECTED')->count(),
        ];

        $recentApplications = AdmissionApplication::with(['applicant.user', 'offering.course', 'payments'])
            ->latest()
            ->limit(8)
            ->get();

        $offerings = DB::table('courses')
            ->leftJoin('programme_offerings', 'courses.id', '=', 'programme_offerings.course_id')
            ->leftJoin('admission_applications', 'programme_offerings.id', '=', 'admission_applications.programme_offering_id')
            ->selectRaw('courses.name, courses.code, programme_offerings.capacity, programme_offerings.application_fee, count(admission_applications.id) as applications_count')
            ->groupBy('courses.id', 'courses.name', 'courses.code', 'programme_offerings.id', 'programme_offerings.capacity', 'programme_offerings.application_fee')
            ->limit(6)
            ->get();

        return view('admissions.admin.workspaces.dashboard', compact('stats', 'funnel', 'recentApplications', 'offerings'));
    }

    /**
     * 2. Admissions Work Queues Workspace
     */
    public function workQueues(Request $request, WorkQueueWorkspace $workspace): View
    {
        $this->authorizeStaff($request);
        $filters = $this->filters($request, ['queue', 'priority', 'q']);

        return view('admissions.admin.workspaces.work-queues', [
            'stats' => $workspace->stats(),
            'queues' => $workspace->rows($filters),
            'stages' => $workspace->stages(),
            'filters' => $filters,
        ]);
    }

    /**
     * 3. Document Verification Workspace
     */
    public function documentVerification(Request $request, DocumentVerificationWorkspace $workspace): View
    {
        $this->authorizeStaff($request);
        $filters = $this->filters($request, ['status', 'q']);

        return view('admissions.admin.workspaces.document-verification', [
            'stats' => $workspace->stats(),
            'verifications' => $workspace->rows($filters),
            'filters' => $filters,
        ]);
    }

    /**
     * 4. Academic Review & Scoring Workspace
     */
    public function reviews(Request $request, ReviewWorkspace $workspace): View
    {
        $this->authorizeStaff($request);
        $filters = $this->filters($request, ['recommendation', 'stage', 'q']);

        return view('admissions.admin.workspaces.reviews', [
            'stats' => $workspace->stats(),
            'reviewsList' => $workspace->rows($filters),
            'filters' => $filters,
        ]);
    }

    /**
     * 5. Merit Shortlisting Workspace
     */
    public function shortlists(Request $request, ShortlistWorkspace $workspace): View
    {
        $this->authorizeStaff($request);
        $filters = $this->filters($request, ['offering', 'q']);

        return view('admissions.admin.workspaces.shortlists', [
            'stats' => $workspace->stats(),
            'shortlists' => $workspace->rows($filters),
            'offerings' => $workspace->offerings(),
            'filters' => $filters,
        ]);
    }

    /**
     * 6. Approvals & Senate Sign-off Workspace
     */
    public function approvals(Request $request, ApprovalWorkspace $workspace): View
    {
        $this->authorizeStaff($request);
        $filters = $this->filters($request, ['stage', 'q']);

        return view('admissions.admin.workspaces.approvals', [
            'stats' => $workspace->stats(),
            'approvalsList' => $workspace->rows($filters),
            'filters' => $filters,
        ]);
    }

    /**
     * 7. Offer Register Workspace
     */
    public function offers(Request $request, OfferWorkspace $workspace): View
    {
        $this->authorizeStaff($request);
        $filters = $this->filters($request, ['status', 'q']);

        return view('admissions.admin.workspaces.offers', [
            'stats' => $workspace->stats(),
            'offersList' => $workspace->rows($filters),
            'statuses' => $workspace->statuses(),
            'filters' => $filters,
        ]);
    }

    /**
     * 8. Waitlist Management Workspace
     */
    public function waitlists(Request $request, WaitlistWorkspace $workspace): View
    {
        $this->authorizeStaff($request);
        $filters = $this->filters($request, ['offering', 'q']);

        return view('admissions.admin.workspaces.waitlists', [
            'stats' => $workspace->stats(),
            'waitlists' => $workspace->rows($filters),
            'offerings' => $workspace->offerings(),
            'filters' => $filters,
        ]);
    }

    /**
     * 9. Admission Roll & Matriculation Workspace
     */
    public function admissionRolls(Request $request, AdmissionRollWorkspace $workspace): View
    {
        $this->authorizeStaff($request);
        $filters = $this->filters($request, ['cohort', 'status', 'q']);

        return view('admissions.admin.workspaces.admission-rolls', [
            'stats' => $workspace->stats(),
            'rolls' => $workspace->rows($filters),
            'cohorts' => $workspace->cohorts(),
            'filters' => $filters,
        ]);
    }

    /**
     * 10. Application Fee Ledger Workspace
     */
    public function payments(Request $request, PaymentWorkspace $workspace): View
    {
        $this->authorizeStaff($request);
        $filters = $this->filters($request, ['status', 'channel', 'q']);

        return view('admissions.admin.workspaces.payments', [
            'stats' => $workspace->stats(),
            'paymentRecords' => $workspace->rows($filters),
            'channels' => $workspace->channels(),
            'filters' => $filters,
        ]);
    }

    /**
     * 11. Payment Reconciliation Workspace
     */
    public function paymentReconciliation(Request $request, PaymentReconciliationWorkspace $workspace): View
    {
        $this->authorizeStaff($request);
        $filters = $this->filters($request, ['provider', 'status', 'q']);

        return view('admissions.admin.workspaces.payment-reconciliation', [
            'stats' => $workspace->stats(),
            'reconciliations' => $workspace->rows($filters),
            'providers' => $workspace->providers(),
            'filters' => $filters,
        ]);
    }

    /**
     * 12. Admissions Audit Trail Workspace
     */
    public function audit(Request $request, AuditWorkspace $workspace): View
    {
        $this->authorizeStaff($request);
        $filters = $this->filters($request, ['action', 'severity', 'q']);

        return view('admissions.admin.workspaces.audit', [
            'stats' => $workspace->stats(),
            'auditLogs' => $workspace->rows($filters),
            'actions' => $workspace->actions(),
            'filters' => $filters,
        ]);
    }

    /**
     * Normalise the querystring into the filter array a workspace expects, so a
     * blank select round-trips as "no filter" rather than an empty match.
     *
     * @param  list<string>  $keys
     * @return array<string, string>
     */
    private function filters(Request $request, array $keys): array
    {
        return array_filter(
            $request->only($keys),
            static fn (mixed $value): bool => is_string($value) && trim($value) !== '',
        );
    }

    private function authorizeStaff(Request $request): void
    {
        abort_unless(in_array($request->user()->role, ['admin', 'staff'], true), 403);
        $this->authorizeAdmission($request, 'admission.application.view', 'admission.application.view_any');
    }
}
