<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AdmissionApplication;
use App\Models\ApplicationDocument;
use App\Models\ApplicationPaymentAttempt;
use App\Models\AuditLog;
use App\Models\ProgrammeOffering;
use App\Modules\Admission\Services\PaymentConfirmationService;
use App\Modules\Admission\Services\PaymentInitiationService;
use App\Services\AdmissionWorkflow;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ApplicantAdmissionController extends Controller
{
    public function portal(Request $request): View
    {
        $user = $request->user();
        $isAdminOrStaff = $user !== null && in_array($user->role, ['admin', 'staff'], true);

        $requestedAppId = $request->query('application_id') ?? $request->route('applicationId');

        $myApplications = $user?->applicantProfile?->applications()
            ->with(['offering.course', 'offering.intake', 'payments', 'documents', 'histories', 'offer'])
            ->latest()
            ->get() ?? collect();

        $application = null;

        if ($requestedAppId) {
            $applicationQuery = AdmissionApplication::query()->with([
                'applicant.user',
                'offering.course',
                'offering.intake',
                'payments',
                'documents',
                'histories',
                'offer',
            ]);

            if (! $isAdminOrStaff && $user?->applicantProfile) {
                $applicationQuery->where('applicant_profile_id', $user->applicantProfile->id);
            }

            $application = $applicationQuery->where(function ($query) use ($requestedAppId): void {
                $query->where('id', $requestedAppId)
                    ->orWhere('application_number', $requestedAppId);
            })
                ->first();
        }

        if (! $application) {
            $application = $myApplications->first();
        }

        // If admin/staff and no own application, auto-select latest admitted or created application for preview
        if (! $application && $isAdminOrStaff) {
            $application = AdmissionApplication::query()
                ->with(['applicant.user', 'offering.course', 'offering.intake', 'payments', 'documents', 'histories', 'offer'])
                ->latest()
                ->first();
        }

        // Applicants only see their own dossiers (MOD-01-05 self-scope). Staff/admin
        // may preview a short cross-application list for support; never leak PII to applicants.
        $allApplications = $isAdminOrStaff
            ? AdmissionApplication::query()
                ->with(['applicant.user', 'offering.course', 'offering.intake', 'offer'])
                ->latest()
                ->take(30)
                ->get()
            : $myApplications;

        $openOfferings = ProgrammeOffering::query()
            ->with(['course', 'intake'])
            ->where('is_published', true)
            ->take(8)
            ->get();

        return view('admissions.portal', compact(
            'application',
            'myApplications',
            'allApplications',
            'openOfferings',
            'isAdminOrStaff'
        ));
    }

    public function update(Request $request, AdmissionApplication $application): RedirectResponse|JsonResponse
    {
        $this->own($request, $application);
        abort_unless(in_array($application->status, ['DRAFT', 'RETURNED_FOR_CORRECTION', 'INFO_REQUESTED'], true), 409);
        $data = $request->validate([
            'date_of_birth' => ['required', 'date', 'before:today'],
            'nationality' => ['required', 'string', 'max:80'],
            'county' => ['nullable', 'string', 'max:80'],
            'identity_type' => ['required', 'in:national_id,birth_certificate,passport'],
            'identity_number' => ['required', 'string', 'max:80'],
            'gender' => ['required', 'in:M,F,N'],
            'source_channel' => ['required', 'string', 'max:60'],
            'education' => ['required', 'string', 'max:2000'],
            'has_support_need' => ['nullable', 'boolean'],
            'support_details' => ['nullable', 'string', 'max:2000'],
            'declarations_accepted' => ['accepted'],
            'lock_version' => ['required', 'integer'],
        ]);
        abort_if((int) $data['lock_version'] !== $application->lock_version, 409, 'This application changed in another session. Refresh and try again.');
        $profile = $application->applicant;
        $profile->update($request->only(['date_of_birth', 'nationality', 'county', 'identity_type', 'identity_number', 'source_channel', 'support_details']) + ['has_support_need' => $request->boolean('has_support_need')]);
        $before = $application->toArray();
        $application->update([
            'form_data' => ['gender' => $data['gender'], 'education' => $data['education']],
            'declarations_accepted' => true,
            'lock_version' => $application->lock_version + 1,
        ]);
        // Readiness is recomputed from what the dossier now holds. Stamping a
        // literal here is how a form save after an upload used to knock a
        // complete application back below the submission threshold.
        $application->refresh()->refreshCompletion();
        AuditLog::record('admission.draft_saved', $application, $before, $application->fresh()->toArray());

        if ($request->expectsJson()) {
            return response()->json([
                'savedAt' => $application->updated_at->toISOString(),
                'lockVersion' => $application->lock_version,
                'completionPercent' => $application->fresh()->completion_percent,
            ]);
        }

        return back()->with('success', 'Draft saved. Upload evidence to complete your application.');
    }

    public function upload(Request $request, AdmissionApplication $application): RedirectResponse
    {
        $this->own($request, $application);
        abort_unless(in_array($application->status, ['DRAFT', 'RETURNED_FOR_CORRECTION', 'INFO_REQUESTED'], true), 409);
        $data = $request->validate([
            'document_type' => ['required', 'in:identity,certificate,transcript,photo'],
            'document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);
        $file = $data['document'];
        $path = $file->store("admissions/{$application->id}", 'local');
        $document = ApplicationDocument::create([
            'admission_application_id' => $application->id,
            'document_type' => $data['document_type'],
            'original_name' => $file->getClientOriginalName(),
            'storage_path' => $path,
            'mime_type' => $file->getMimeType(),
            'size_bytes' => $file->getSize(),
            'sha256' => hash_file('sha256', $file->getRealPath()),
            'verification_status' => 'PENDING',
        ]);
        $application->refresh()->refreshCompletion();
        AuditLog::record('admission.document_uploaded', $document, null, $document->toArray());

        return back()->with('success', 'Document stored privately and queued for verification.');
    }

    public function payment(Request $request, AdmissionApplication $application, PaymentInitiationService $payments): RedirectResponse
    {
        $this->own($request, $application);
        abort_unless(in_array($application->status, ['DRAFT', 'RETURNED_FOR_CORRECTION', 'INFO_REQUESTED'], true), 409);
        $data = $request->validate([
            'channel' => ['required', 'in:mpesa,card,bank,cashier,pochi,paybill,till,stripe'],
            'phone' => ['nullable', 'string', 'regex:/^(\+?254|0)[17]\d{8}$/'],
        ]);
        $payment = $payments->initiate($application, $data['channel'], $data['phone'] ?? null, $request->user()->id);

        return back()->with('success', $payment->status === 'PAID'
            ? 'Test payment confirmed. Your receipt number is '.$payment->receipt_number.'.'
            : 'Payment initiated. It remains pending until the provider or Finance confirms it. Reference: '.$payment->reference.'.');
    }

    /**
     * Declare the M-Pesa or bank transaction code for an off-line payment. The
     * code is a claim that puts the attempt on the Finance queue; it settles
     * nothing on its own.
     */
    public function declarePayment(
        Request $request,
        AdmissionApplication $application,
        ApplicationPaymentAttempt $attempt,
        PaymentConfirmationService $payments,
    ): RedirectResponse {
        $this->own($request, $application);
        abort_unless($attempt->admission_application_id === $application->id, 404);
        $data = $request->validate([
            'transaction_code' => ['required', 'string', 'min:6', 'max:40', 'regex:/^[A-Za-z0-9-]+$/'],
            'payer_name' => ['nullable', 'string', 'max:120'],
        ], [
            'transaction_code.regex' => 'Enter the code exactly as it appears in your payment message.',
        ]);

        $payments->declareTransactionCode($attempt, $data['transaction_code'], $data['payer_name'] ?? null, $request->user()->id);

        return back()->with('success', 'Transaction code recorded. Finance will confirm it and your receipt will appear here once verified.');
    }

    public function submit(Request $request, AdmissionApplication $application, AdmissionWorkflow $workflow): RedirectResponse
    {
        $this->own($request, $application);
        $workflow->submit($application);

        return back()->with('success', 'Application submitted and frozen for review.');
    }

    public function respond(Request $request, AdmissionApplication $application, AdmissionWorkflow $workflow): RedirectResponse
    {
        $this->own($request, $application);
        $data = $request->validate([
            'response' => ['required', 'in:ACCEPTED,DECLINED'],
            'offer_declaration' => ['required_if:response,ACCEPTED', 'accepted'],
        ]);
        $workflow->respondToOffer($application, $data['response'], [
            'declaration_version' => $data['response'] === 'ACCEPTED' ? 'offer-acceptance-v1' : null,
            'terms_version' => $data['response'] === 'ACCEPTED' ? 'admissions-terms-v1' : null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        if ($data['response'] === 'DECLINED') {
            return back()->with('success', 'Your response has been recorded. The offer is now declined.');
        }

        return back()->with('success', 'Offer accepted. Complete your enrolment below to receive your student number.');
    }

    public function enrol(Request $request, AdmissionApplication $application, AdmissionWorkflow $workflow): RedirectResponse
    {
        $this->own($request, $application);
        abort_unless($application->status === 'READY_TO_ENROL', 409, 'Enrolment opens once your accepted offer has been cleared.');
        $request->validate(['enrolment_declaration' => ['accepted']]);

        $application = $workflow->enrol($application);
        $studentNumber = $application->conversion?->student_number;

        return back()->with('success', 'Enrolment complete. Your student number is '.$studentNumber.'.');
    }

    private function own(Request $request, AdmissionApplication $application): void
    {
        abort_unless($application->applicant->user_id === $request->user()->id, 403);
    }
}
