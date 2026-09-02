<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AdmissionApplication;
use App\Models\ApplicationDocument;
use App\Models\ApplicationPaymentAttempt;
use App\Models\AuditLog;
use App\Modules\Admission\Services\AdmissionPipeline;
use App\Services\AdmissionWorkflow;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class ApplicantAdmissionController extends Controller
{
    public function portal(Request $request): View
    {
        $application = $request->user()->applicantProfile?->applications()->with(['offering.course', 'offering.intake', 'payments', 'documents', 'histories', 'offer'])->latest()->first();

        return view('admissions.portal', compact('application'));
    }

    public function update(Request $request, AdmissionApplication $application): RedirectResponse|JsonResponse
    {
        $this->own($request, $application);
        abort_unless(in_array($application->status, ['DRAFT', 'RETURNED_FOR_CORRECTION', 'INFO_REQUESTED'], true), 409);
        $data = $request->validate(['date_of_birth' => ['required', 'date', 'before:today'], 'nationality' => ['required', 'string', 'max:80'], 'county' => ['nullable', 'string', 'max:80'], 'identity_type' => ['required', 'in:national_id,birth_certificate,passport'], 'identity_number' => ['required', 'string', 'max:80'], 'gender' => ['required', 'in:M,F,N'], 'source_channel' => ['required', 'string', 'max:60'], 'education' => ['required', 'string', 'max:2000'], 'has_support_need' => ['nullable', 'boolean'], 'support_details' => ['nullable', 'string', 'max:2000'], 'declarations_accepted' => ['accepted'], 'lock_version' => ['required', 'integer']]);
        abort_if((int) $data['lock_version'] !== $application->lock_version, 409, 'This application changed in another session. Refresh and try again.');
        $profile = $application->applicant;
        $profile->update($request->only(['date_of_birth', 'nationality', 'county', 'identity_type', 'identity_number', 'source_channel', 'support_details']) + ['has_support_need' => $request->boolean('has_support_need')]);
        $before = $application->toArray();
        $application->update(['form_data' => ['gender' => $data['gender'], 'education' => $data['education']], 'completion_percent' => 80, 'declarations_accepted' => true, 'lock_version' => $application->lock_version + 1]);
        AuditLog::record('admission.draft_saved', $application, $before, $application->fresh()->toArray());

        if ($request->expectsJson()) {
            return response()->json([
                'savedAt' => $application->updated_at->toISOString(),
                'lockVersion' => $application->lock_version,
                'completionPercent' => $application->completion_percent,
            ]);
        }

        return back()->with('success', 'Draft saved. Upload evidence to complete your application.');
    }

    public function upload(Request $request, AdmissionApplication $application): RedirectResponse
    {
        $this->own($request, $application);
        abort_unless(in_array($application->status, ['DRAFT', 'RETURNED_FOR_CORRECTION', 'INFO_REQUESTED'], true), 409);
        $data = $request->validate(['document_type' => ['required', 'in:identity,certificate,transcript,photo'], 'document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120']]);
        $file = $data['document'];
        $path = $file->store("admissions/{$application->id}", 'local');
        $document = ApplicationDocument::create(['admission_application_id' => $application->id, 'document_type' => $data['document_type'], 'original_name' => $file->getClientOriginalName(), 'storage_path' => $path, 'mime_type' => $file->getMimeType(), 'size_bytes' => $file->getSize(), 'sha256' => hash_file('sha256', $file->getRealPath()), 'verification_status' => 'PENDING']);
        $application->update(['completion_percent' => 100]);
        AuditLog::record('admission.document_uploaded', $document, null, $document->toArray());

        return back()->with('success', 'Document stored privately and queued for verification.');
    }

    public function payment(Request $request, AdmissionApplication $application, AdmissionPipeline $pipeline): RedirectResponse
    {
        $this->own($request, $application);
        $data = $request->validate(['channel' => ['required', 'in:mpesa,card,bank,cashier']]);
        $key = (string) Str::uuid();
        $payment = ApplicationPaymentAttempt::create(['admission_application_id' => $application->id, 'reference' => 'PAY-'.strtoupper(Str::random(10)), 'channel' => $data['channel'], 'amount' => 1000, 'currency' => 'KES', 'status' => 'PAID', 'idempotency_key' => $key, 'paid_at' => now(), 'receipt_number' => 'MEMA-RCPT-'.now()->format('Ymd').'-'.str_pad((string) (ApplicationPaymentAttempt::count() + 1), 5, '0', STR_PAD_LEFT), 'provider_payload' => ['mode' => 'sandbox', 'confirmed' => true]]);
        AuditLog::record('application_fee.paid', $payment, null, $payment->toArray());
        // The attempt is the applicant's intent; the ledger entry and receipt are
        // what Finance reconciles, so both are written on confirmation.
        $pipeline->recordPayment($application, $payment, $request->user()->id);

        return back()->with('success', 'Sandbox payment confirmed. Your official receipt number is '.$payment->receipt_number.'.');
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
        $data = $request->validate(['response' => ['required', 'in:ACCEPTED,DECLINED']]);
        $workflow->move($application, $data['response'], 'applicant_offer_response');
        $application->offer?->update(['status' => $data['response'], 'responded_at' => now()]);

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
