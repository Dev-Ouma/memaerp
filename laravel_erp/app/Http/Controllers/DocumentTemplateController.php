<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AdmissionApplication;
use App\Services\DocumentTemplateService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class DocumentTemplateController extends Controller
{
    public function __construct(private readonly DocumentTemplateService $templates) {}

    /**
     * Document Templates Hub & Live Interactive Preview.
     */
    public function index(Request $request): View
    {
        $catalogue = $this->templates->catalogue();
        $selectedKey = (string) $request->query('template', 'admission_letter');
        if (! array_key_exists($selectedKey, $catalogue)) {
            $selectedKey = 'admission_letter';
        }

        $activeTemplate = $catalogue[$selectedKey];
        $applicationId = $request->query('application_id');
        $application = $applicationId ? AdmissionApplication::with(['applicant.user', 'offering.course', 'offer'])->find($applicationId) : null;

        $payload = $this->templates->resolvePayload($application);
        $recentAdmitted = AdmissionApplication::with(['applicant.user', 'offering.course'])
            ->whereIn('status', ['ADMITTED', 'ACCEPTED', 'READY_TO_ENROL', 'ENROLLED'])
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.setups.document-templates', compact(
            'catalogue',
            'selectedKey',
            'activeTemplate',
            'payload',
            'application',
            'recentAdmitted'
        ));
    }

    /**
     * Standalone Clean Printable View of the Document.
     */
    public function preview(Request $request, string $templateKey): View
    {
        $catalogue = $this->templates->catalogue();
        abort_unless(array_key_exists($templateKey, $catalogue), 404, 'Template not found');

        $applicationId = $request->query('application_id');
        $application = $applicationId ? AdmissionApplication::with(['applicant.user', 'offering.course', 'offer'])->find($applicationId) : null;
        $payload = $this->templates->resolvePayload($application);

        return view('templates.documents.'.$templateKey, [
            'payload' => $payload,
            'template' => $catalogue[$templateKey],
            'standalone' => true,
        ]);
    }

    /**
     * Compile on-the-fly PDF using WeasyPrint.
     */
    public function pdf(Request $request, string $templateKey): Response|BinaryFileResponse
    {
        $catalogue = $this->templates->catalogue();
        abort_unless(array_key_exists($templateKey, $catalogue), 404, 'Template not found');

        $applicationId = $request->query('application_id');
        $application = $applicationId ? AdmissionApplication::with(['applicant.user', 'offering.course', 'offer'])->find($applicationId) : null;
        $payload = $this->templates->resolvePayload($application);

        $html = view('templates.documents.'.$templateKey, [
            'payload' => $payload,
            'template' => $catalogue[$templateKey],
            'standalone' => true,
        ])->render();

        $tempHtml = tempnam(sys_get_temp_dir(), 'mema_doc_').'.html';
        $tempPdf = tempnam(sys_get_temp_dir(), 'mema_doc_').'.pdf';
        file_put_contents($tempHtml, $html);

        $process = Process::run(['/opt/homebrew/bin/weasyprint', $tempHtml, $tempPdf]);

        if ($process->successful() && file_exists($tempPdf) && filesize($tempPdf) > 0) {
            $filename = Str::slug($catalogue[$templateKey]['name']).'_'.($payload['application']['admission_number'] ?? 'preview').'.pdf';
            $filename = str_replace('/', '_', $filename);

            return response()->download($tempPdf, $filename, [
                'Content-Type' => 'application/pdf',
            ])->deleteFileAfterSend(true);
        }

        // Fallback: return printable HTML if weasyprint cannot execute in current environment
        return response($html, 200, ['Content-Type' => 'text/html']);
    }
}
