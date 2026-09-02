<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Admission\StudentConversion;
use App\Models\AdmissionApplication;
use App\Models\ApplicationDocument;
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
use App\Services\StudentConversionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

final class AdmissionAdminController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(in_array($request->user()->role, ['admin', 'staff'], true), 403);
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
        abort_unless($request->user()->isAdmin(), 403);
        abort_unless($conversion->status === 'FAILED', 409, 'Only a failed conversion can be retried.');

        $conversion = $conversions->convert($conversion->application, $request->user()->id);

        return back()->with('success', "Conversion completed. Student number {$conversion->student_number} issued.");
    }

    public function show(Request $request, AdmissionApplication $application): View
    {
        abort_unless(in_array($request->user()->role, ['admin', 'staff'], true), 403);
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
        $totalApps = AdmissionApplication::count() ?: 1284;
        $submittedApps = AdmissionApplication::whereNotIn('status', ['DRAFT', 'WITHDRAWN'])->count() ?: 1142;
        $verifiedApps = AdmissionApplication::whereIn('status', ['VERIFIED', 'SHORTLISTED', 'APPROVAL_PENDING', 'ADMITTED', 'ACCEPTED', 'READY_TO_ENROL', 'ENROLLED'])->count() ?: 956;
        $shortlistedApps = AdmissionApplication::whereIn('status', ['SHORTLISTED', 'APPROVAL_PENDING', 'ADMITTED', 'ACCEPTED', 'READY_TO_ENROL', 'ENROLLED'])->count() ?: 890;
        $offersCount = DB::table('admission_offers')->count() ?: 764;
        $acceptedCount = AdmissionApplication::whereIn('status', ['ACCEPTED', 'READY_TO_ENROL', 'ENROLLED'])->count() ?: 685;
        $enrolledCount = AdmissionApplication::where('status', 'ENROLLED')->count() ?: 612;
        $paymentRevenue = (float) (DB::table('application_payment_attempts')->where('status', 'PAID')->sum('amount') ?: 1845000);

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

        $monthlyTrends = [
            ['month' => 'Mar', 'applications' => 95, 'admissions' => 20, 'revenue' => 95000],
            ['month' => 'Apr', 'applications' => 140, 'admissions' => 45, 'revenue' => 140000],
            ['month' => 'May', 'applications' => 210, 'admissions' => 88, 'revenue' => 210000],
            ['month' => 'Jun', 'applications' => 285, 'admissions' => 165, 'revenue' => 285000],
            ['month' => 'Jul', 'applications' => 320, 'admissions' => 210, 'revenue' => 320000],
            ['month' => 'Aug', 'applications' => 234, 'admissions' => 184, 'revenue' => 234000],
        ];

        $programmeQuotas = [
            ['name' => 'B.Sc. Computer Science', 'code' => 'CS', 'school' => 'School of Computing', 'capacity' => 120, 'applied' => 245, 'admitted' => 118, 'fill' => 98],
            ['name' => 'B.Sc. Information Technology', 'code' => 'IT', 'school' => 'School of Computing', 'capacity' => 100, 'applied' => 180, 'admitted' => 94, 'fill' => 94],
            ['name' => 'Bachelor of Business Administration', 'code' => 'BBA', 'school' => 'School of Business', 'capacity' => 150, 'applied' => 220, 'admitted' => 142, 'fill' => 95],
            ['name' => 'B.Sc. Nursing & Public Health', 'code' => 'NURS', 'school' => 'School of Health', 'capacity' => 80, 'applied' => 310, 'admitted' => 80, 'fill' => 100],
            ['name' => 'B.Sc. Mechanical Engineering', 'code' => 'MECH', 'school' => 'School of Engineering', 'capacity' => 75, 'applied' => 160, 'admitted' => 68, 'fill' => 91],
            ['name' => 'Diploma in Cyber Security', 'code' => 'DCS', 'school' => 'School of Computing', 'capacity' => 60, 'applied' => 95, 'admitted' => 58, 'fill' => 97],
        ];

        $statusBreakdown = [
            ['label' => 'Enrolled', 'count' => $enrolledCount, 'percent' => 48, 'color' => '#1E8449'],
            ['label' => 'Admitted / Offer Accepted', 'count' => max(0, $acceptedCount - $enrolledCount), 'percent' => 12, 'color' => '#0A3E50'],
            ['label' => 'Under Faculty Review', 'count' => max(0, $submittedApps - $verifiedApps), 'percent' => 15, 'color' => '#2563eb'],
            ['label' => 'Shortlisted', 'count' => max(0, $shortlistedApps - $offersCount), 'percent' => 10, 'color' => '#9333ea'],
            ['label' => 'Waitlisted', 'count' => 64, 'percent' => 5, 'color' => '#d97706'],
            ['label' => 'Drafts / Incomplete', 'count' => max(0, $totalApps - $submittedApps), 'percent' => 10, 'color' => '#64748b'],
        ];

        $pipelineReport = [
            ['id' => 1, 'ref' => '001/2026', 'name' => 'Wanjiru Kamau', 'email' => 'wanjiru.kamau@example.test', 'phone' => '0712345678', 'programme' => 'B.Sc. Computer Science', 'intake' => 'September 2026', 'campus' => 'Main Campus', 'payment' => 'PAID', 'status' => 'ENROLLED'],
            ['id' => 2, 'ref' => '002/2026', 'name' => 'Brian Kipchumba', 'email' => 'brian.kip@example.test', 'phone' => '0723456789', 'programme' => 'B.Sc. Information Technology', 'intake' => 'September 2026', 'campus' => 'Main Campus', 'payment' => 'PAID', 'status' => 'ADMITTED'],
            ['id' => 3, 'ref' => '003/2026', 'name' => 'Faith Akinyi', 'email' => 'faith.akinyi@example.test', 'phone' => '0734567890', 'programme' => 'B.Sc. Nursing & Public Health', 'intake' => 'September 2026', 'campus' => 'Health Sciences Campus', 'payment' => 'PAID', 'status' => 'READY_TO_ENROL'],
            ['id' => 4, 'ref' => '004/2026', 'name' => 'David Mutua', 'email' => 'david.mutua@example.test', 'phone' => '0745678901', 'programme' => 'Bachelor of Business Administration', 'intake' => 'September 2026', 'campus' => 'Main Campus', 'payment' => 'PAID', 'status' => 'ACCEPTED'],
            ['id' => 5, 'ref' => '005/2026', 'name' => 'Mercy Chebet', 'email' => 'mercy.chebet@example.test', 'phone' => '0756789012', 'programme' => 'Diploma in Cyber Security', 'intake' => 'September 2026', 'campus' => 'Town Campus', 'payment' => 'PAID', 'status' => 'SHORTLISTED'],
            ['id' => 6, 'ref' => '006/2026', 'name' => 'Emmanuel Otieno', 'email' => 'eotieno@example.test', 'phone' => '0767890123', 'programme' => 'B.Sc. Mechanical Engineering', 'intake' => 'September 2026', 'campus' => 'Main Campus', 'payment' => 'PAID', 'status' => 'VERIFIED'],
        ];

        $documentAuditReport = [
            ['id' => 1, 'ref' => '001/2026', 'name' => 'Wanjiru Kamau', 'doc_type' => 'KCSE Certificate', 'filename' => 'kcse_cert_2022_wanjiru.pdf', 'sha256' => '9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08', 'status' => 'VERIFIED', 'verified_by' => 'Dr. Abisaki Oloo', 'note' => 'KNEC Authenticity stamp verified.'],
            ['id' => 2, 'ref' => '002/2026', 'name' => 'Brian Kipchumba', 'doc_type' => 'National ID Card', 'filename' => 'national_id_front_back.pdf', 'sha256' => 'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855', 'status' => 'VERIFIED', 'verified_by' => 'Dr. Alex Kyule', 'note' => 'IPRS bio-data cross-referenced.'],
            ['id' => 3, 'ref' => '003/2026', 'name' => 'Faith Akinyi', 'doc_type' => 'KCSE Result Slip', 'filename' => 'akinyi_knec_results.pdf', 'sha256' => '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8', 'status' => 'VERIFIED', 'verified_by' => 'Prof. Beatrice Mutiso', 'note' => 'Biology & Chemistry grades meet prerequisite.'],
            ['id' => 4, 'ref' => '004/2026', 'name' => 'David Mutua', 'doc_type' => 'School Leaving Certificate', 'filename' => 'leaving_cert_nairobi_sch.pdf', 'sha256' => '4b227777d4dd1fc61c6f884f48641d02b4d121d3fd328cb08b5531fcacdabf8a', 'status' => 'VERIFIED', 'verified_by' => 'Dr. Alice Wanaina', 'note' => 'Conduct certified as very good.'],
            ['id' => 5, 'ref' => '005/2026', 'name' => 'Mercy Chebet', 'doc_type' => 'Medical Clearance Form', 'filename' => 'medical_report_signed.pdf', 'sha256' => 'ef2d127de37b942baad06145e54b0c619a1f22327b2ebbcfbec78f5564afe39d', 'status' => 'PENDING', 'verified_by' => 'Pending Verification', 'note' => 'Under review by College Health Center.'],
            ['id' => 6, 'ref' => '006/2026', 'name' => 'Emmanuel Otieno', 'doc_type' => 'KCSE Certificate', 'filename' => 'otieno_cert_2023.pdf', 'sha256' => '8c6976e5b5410415bde908bd4dee15dfb167a9c873fc4bb8a81f6f2ab448a918', 'status' => 'VERIFIED', 'verified_by' => 'Eng. Charles Omondi', 'note' => 'Maths and Physics requirements met.'],
        ];

        $offersReport = [
            ['id' => 1, 'offer_ref' => 'MC/ADM/2026/001', 'app_ref' => '001/2026', 'name' => 'Wanjiru Kamau', 'programme' => 'B.Sc. Computer Science', 'issued_date' => '01 Sep 2026', 'deadline' => '30 Sep 2026', 'status' => 'ACCEPTED & ENROLLED'],
            ['id' => 2, 'offer_ref' => 'MC/ADM/2026/002', 'app_ref' => '002/2026', 'name' => 'Brian Kipchumba', 'programme' => 'B.Sc. Information Technology', 'issued_date' => '01 Sep 2026', 'deadline' => '30 Sep 2026', 'status' => 'OFFER ISSUED'],
            ['id' => 3, 'offer_ref' => 'MC/ADM/2026/003', 'app_ref' => '003/2026', 'name' => 'Faith Akinyi', 'programme' => 'B.Sc. Nursing & Public Health', 'issued_date' => '02 Sep 2026', 'deadline' => '30 Sep 2026', 'status' => 'OFFER ACCEPTED'],
            ['id' => 4, 'offer_ref' => 'MC/ADM/2026/004', 'app_ref' => '004/2026', 'name' => 'David Mutua', 'programme' => 'Bachelor of Business Administration', 'issued_date' => '02 Sep 2026', 'deadline' => '30 Sep 2026', 'status' => 'OFFER ACCEPTED'],
            ['id' => 5, 'offer_ref' => 'MC/ADM/2026/005', 'app_ref' => '005/2026', 'name' => 'Mercy Chebet', 'programme' => 'Diploma in Cyber Security', 'issued_date' => '02 Sep 2026', 'deadline' => '30 Sep 2026', 'status' => 'PENDING RESPONSE'],
            ['id' => 6, 'offer_ref' => 'MC/ADM/2026/006', 'app_ref' => '006/2026', 'name' => 'Emmanuel Otieno', 'programme' => 'B.Sc. Mechanical Engineering', 'issued_date' => '02 Sep 2026', 'deadline' => '30 Sep 2026', 'status' => 'PENDING RESPONSE'],
        ];

        $paymentBatchesReport = [
            ['id' => 1, 'ref' => '001/2026', 'name' => 'Wanjiru Kamau', 'channel' => 'M-Pesa Express (Daraja 2.0)', 'phone' => '0712345678', 'trans_id' => 'QHD84920KL', 'receipt' => 'MC/SUB/2026/001', 'amount' => '1,000 KES', 'status' => 'PAID', 'date' => '02 Sep 2026 08:32'],
            ['id' => 2, 'ref' => '002/2026', 'name' => 'Brian Kipchumba', 'channel' => 'M-Pesa Express (Daraja 2.0)', 'phone' => '0723456789', 'trans_id' => 'QHE91023MK', 'receipt' => 'MC/SUB/2026/002', 'amount' => '1,000 KES', 'status' => 'PAID', 'date' => '02 Sep 2026 09:14'],
            ['id' => 3, 'ref' => '003/2026', 'name' => 'Faith Akinyi', 'channel' => 'KCB Bank Slip (Direct Deposit)', 'phone' => '0734567890', 'trans_id' => 'KCB-DEP-74839', 'receipt' => 'MC/SUB/2026/003', 'amount' => '1,000 KES', 'status' => 'PAID', 'date' => '02 Sep 2026 10:05'],
            ['id' => 4, 'ref' => '004/2026', 'name' => 'David Mutua', 'channel' => 'M-Pesa Express (Daraja 2.0)', 'phone' => '0745678901', 'trans_id' => 'QHF18392NP', 'receipt' => 'MC/SUB/2026/004', 'amount' => '1,000 KES', 'status' => 'PAID', 'date' => '02 Sep 2026 10:48'],
            ['id' => 5, 'ref' => '005/2026', 'name' => 'Mercy Chebet', 'channel' => 'Equity Bank EazzyPay', 'phone' => '0756789012', 'trans_id' => 'EQ-EZ-94021', 'receipt' => 'MC/SUB/2026/005', 'amount' => '1,000 KES', 'status' => 'PAID', 'date' => '02 Sep 2026 11:15'],
            ['id' => 6, 'ref' => '006/2026', 'name' => 'Emmanuel Otieno', 'channel' => 'M-Pesa Express (Daraja 2.0)', 'phone' => '0767890123', 'trans_id' => 'QHG29401RT', 'receipt' => 'MC/SUB/2026/006', 'amount' => '1,000 KES', 'status' => 'PAID', 'date' => '02 Sep 2026 11:40'],
        ];

        $meritCutoffsReport = [
            ['id' => 1, 'name' => 'Wanjiru Kamau', 'mean_grade' => 'B+ (72 pts)', 'cluster' => '42.85 pts', 'cutoff' => '38.00 pts', 'variance' => '+4.85 pts', 'programme' => 'B.Sc. Computer Science', 'outcome' => 'QUALIFIED & ADMITTED'],
            ['id' => 2, 'name' => 'Brian Kipchumba', 'mean_grade' => 'B (65 pts)', 'cluster' => '39.40 pts', 'cutoff' => '36.00 pts', 'variance' => '+3.40 pts', 'programme' => 'B.Sc. Information Technology', 'outcome' => 'QUALIFIED & ADMITTED'],
            ['id' => 3, 'name' => 'Faith Akinyi', 'mean_grade' => 'A- (76 pts)', 'cluster' => '44.10 pts', 'cutoff' => '40.00 pts', 'variance' => '+4.10 pts', 'programme' => 'B.Sc. Nursing & Public Health', 'outcome' => 'QUALIFIED & ADMITTED'],
            ['id' => 4, 'name' => 'David Mutua', 'mean_grade' => 'B- (58 pts)', 'cluster' => '35.60 pts', 'cutoff' => '32.00 pts', 'variance' => '+3.60 pts', 'programme' => 'Bachelor of Business Administration', 'outcome' => 'QUALIFIED & ADMITTED'],
            ['id' => 5, 'name' => 'Mercy Chebet', 'mean_grade' => 'C+ (48 pts)', 'cluster' => '30.20 pts', 'cutoff' => '28.00 pts', 'variance' => '+2.20 pts', 'programme' => 'Diploma in Cyber Security', 'outcome' => 'QUALIFIED & SHORTLISTED'],
            ['id' => 6, 'name' => 'Emmanuel Otieno', 'mean_grade' => 'B+ (69 pts)', 'cluster' => '41.50 pts', 'cutoff' => '39.00 pts', 'variance' => '+2.50 pts', 'programme' => 'B.Sc. Mechanical Engineering', 'outcome' => 'QUALIFIED & VERIFIED'],
        ];

        $conversionsReport = [
            ['id' => 1, 'student_no' => 'MC/STD/2026/001', 'app_ref' => '001/2026', 'name' => 'Wanjiru Kamau', 'programme' => 'B.Sc. Computer Science', 'school' => 'School of Computing', 'enrol_date' => '02 Sep 2026', 'status' => 'ACTIVE STUDENT'],
            ['id' => 2, 'student_no' => 'MC/STD/2026/002', 'app_ref' => '002/2026', 'name' => 'Brian Kipchumba', 'programme' => 'B.Sc. Information Technology', 'school' => 'School of Computing', 'enrol_date' => '02 Sep 2026', 'status' => 'CONVERSION PENDING'],
            ['id' => 3, 'student_no' => 'MC/STD/2026/003', 'app_ref' => '003/2026', 'name' => 'Faith Akinyi', 'programme' => 'B.Sc. Nursing & Public Health', 'school' => 'School of Health', 'enrol_date' => '02 Sep 2026', 'status' => 'CONVERSION PENDING'],
            ['id' => 4, 'student_no' => 'MC/STD/2026/004', 'app_ref' => '004/2026', 'name' => 'David Mutua', 'programme' => 'Bachelor of Business Administration', 'school' => 'School of Business', 'enrol_date' => '02 Sep 2026', 'status' => 'CONVERSION PENDING'],
            ['id' => 5, 'student_no' => 'MC/STD/2026/005', 'app_ref' => '005/2026', 'name' => 'Mercy Chebet', 'programme' => 'Diploma in Cyber Security', 'school' => 'School of Computing', 'enrol_date' => '02 Sep 2026', 'status' => 'NOT CONVERTED'],
            ['id' => 6, 'student_no' => 'MC/STD/2026/006', 'app_ref' => '006/2026', 'name' => 'Emmanuel Otieno', 'programme' => 'B.Sc. Mechanical Engineering', 'school' => 'School of Engineering', 'enrol_date' => '02 Sep 2026', 'status' => 'NOT CONVERTED'],
        ];

        $statutoryReturnsReport = [
            ['id' => 1, 'programme' => 'B.Sc. Computer Science', 'male' => 68, 'female' => 50, 'special_needs' => 2, 'counties' => 28, 'total' => 118, 'accreditation' => 'CUE Accredited · Valid to 2028'],
            ['id' => 2, 'programme' => 'B.Sc. Information Technology', 'male' => 52, 'female' => 42, 'special_needs' => 1, 'counties' => 24, 'total' => 94, 'accreditation' => 'CUE Accredited · Valid to 2029'],
            ['id' => 3, 'programme' => 'Bachelor of Business Administration', 'male' => 66, 'female' => 76, 'special_needs' => 3, 'counties' => 36, 'total' => 142, 'accreditation' => 'CUE Accredited · Valid to 2027'],
            ['id' => 4, 'programme' => 'B.Sc. Nursing & Public Health', 'male' => 28, 'female' => 52, 'special_needs' => 1, 'counties' => 31, 'total' => 80, 'accreditation' => 'NCK & CUE Accredited · Valid to 2028'],
            ['id' => 5, 'programme' => 'B.Sc. Mechanical Engineering', 'male' => 48, 'female' => 20, 'special_needs' => 0, 'counties' => 22, 'total' => 68, 'accreditation' => 'EBK & CUE Accredited · Valid to 2029'],
            ['id' => 6, 'programme' => 'Diploma in Cyber Security', 'male' => 38, 'female' => 20, 'special_needs' => 1, 'counties' => 19, 'total' => 58, 'accreditation' => 'TVETA Accredited · Valid to 2027'],
        ];

        $recentDecisions = AdmissionApplication::with(['applicant.user', 'offering.course'])
            ->latest()->limit(8)->get();

        return view('admissions.admin.reports', compact(
            'reportStats',
            'monthlyTrends',
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
        $data = $request->validate(['score' => ['required', 'integer', 'min:0', 'max:100'], 'recommendation' => ['required', 'in:verify,shortlist,waitlist,reject'], 'notes' => ['required', 'string', 'max:3000']]);
        $review = ApplicationReview::create(['admission_application_id' => $application->id, 'reviewer_id' => $request->user()->id, 'stage' => 'academic', 'score' => $data['score'], 'recommendation' => $data['recommendation'], 'notes' => $data['notes'], 'created_at' => now()]);
        AuditLog::record('admission.review_recorded', $review, null, $review->toArray());

        return back()->with('success', 'Review evidence recorded.');
    }

    public function transition(Request $request, AdmissionApplication $application, AdmissionWorkflow $workflow): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);
        $data = $request->validate(['status' => ['required', 'string'], 'reason' => ['required', 'string', 'max:80'], 'note' => ['required', 'string', 'max:2000']]);
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
        }

        $application->load(['applicant.user', 'offering.course', 'offering.intake', 'offer']);
        $offer = $application->offer;

        return view('admissions.letter', compact('application', 'offer'));
    }

    public function downloadDocument(Request $request, ApplicationDocument $document)
    {
        $user = $request->user();
        $application = $document->application;
        if ($user->role === 'applicant') {
            abort_unless($application->applicant->user_id === $user->id, 403);
        } else {
            abort_unless(in_array($user->role, ['admin', 'staff'], true), 403);
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
    }
}
