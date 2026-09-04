<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesCataloguePermission;
use App\Models\GraduationAlumnus;
use App\Models\GraduationCeremony;
use App\Models\GraduationCeremonyReport;
use App\Models\GraduationCertificateTemplate;
use App\Models\GraduationClearanceNode;
use App\Models\GraduationCriterion;
use App\Models\GraduationFinanceClearance;
use App\Models\GraduationGradeEntry;
use App\Models\GraduationListBatch;
use App\Models\GraduationListReport;
use App\Models\GraduationListValidation;
use App\Models\GraduationPublication;
use App\Models\GraduationSummary;
use App\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class GraduationController extends Controller
{
    use AuthorizesCataloguePermission;

    public function criteria(Request $request): View
    {
        $criteria = GraduationCriterion::query()->latest()->get()->map(fn (GraduationCriterion $row): array => [
            'programme' => $row->programme,
            'min_credits' => $row->min_credits ?? '—',
            'min_cgpa' => $row->min_cgpa ?? '—',
            'thesis_required' => $row->thesis_required ?? '—',
            'clearance_nodes' => $row->clearance_nodes ?? '—',
            'status' => $row->status,
        ])->all();

        return view('graduation.criteria', compact('criteria'))->with(
            'operationalCreate',
            $this->createForm('Add graduation criteria', 'Persists to graduation_criteria.', 'graduation.criteria.store', [
                ['name' => 'programme', 'label' => 'Programme', 'required' => true],
                ['name' => 'min_credits', 'label' => 'Min credits'],
                ['name' => 'min_cgpa', 'label' => 'Min CGPA'],
                ['name' => 'thesis_required', 'label' => 'Thesis required'],
                ['name' => 'clearance_nodes', 'label' => 'Clearance nodes'],
                ['name' => 'status', 'label' => 'Status'],
            ]),
        );
    }

    public function storeCriteria(Request $request): RedirectResponse
    {
        return $this->store($request, GraduationCriterion::class, [
            'programme' => ['required', 'string', 'max:190'],
            'min_credits' => ['nullable', 'string', 'max:40'],
            'min_cgpa' => ['nullable', 'string', 'max:40'],
            'thesis_required' => ['nullable', 'string', 'max:40'],
            'clearance_nodes' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:40'],
        ], ['status' => 'Active'], 'Graduation criteria saved.');
    }

    public function clearanceChecklist(Request $request): View
    {
        $checklists = GraduationClearanceNode::query()->latest()->get()->map(fn (GraduationClearanceNode $row): array => [
            'node_name' => $row->node_name,
            'check_type' => $row->check_type ?? '—',
            'assigned_role' => $row->assigned_role ?? '—',
            'requires_approval' => $row->requires_approval ?? '—',
            'status' => $row->status,
        ])->all();

        return view('graduation.clearance-checklist', compact('checklists'))->with(
            'operationalCreate',
            $this->createForm('Add clearance node', 'Persists to graduation_clearance_nodes.', 'graduation.clearance-checklist.store', [
                ['name' => 'node_name', 'label' => 'Node name', 'required' => true],
                ['name' => 'check_type', 'label' => 'Check type'],
                ['name' => 'assigned_role', 'label' => 'Assigned role'],
                ['name' => 'requires_approval', 'label' => 'Approval rule'],
                ['name' => 'status', 'label' => 'Status'],
            ]),
        );
    }

    public function storeClearanceChecklist(Request $request): RedirectResponse
    {
        return $this->store($request, GraduationClearanceNode::class, [
            'node_name' => ['required', 'string', 'max:190'],
            'check_type' => ['nullable', 'string', 'max:80'],
            'assigned_role' => ['nullable', 'string', 'max:120'],
            'requires_approval' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'string', 'max:40'],
        ], ['status' => 'Active'], 'Clearance node saved.');
    }

    public function financeClearance(Request $request): View
    {
        $clearances = GraduationFinanceClearance::query()->latest()->get()->map(fn (GraduationFinanceClearance $row): array => [
            'student_name' => $row->student_name,
            'reg_no' => $row->reg_no ?? '—',
            'programme' => $row->programme ?? '—',
            'ledger_balance' => $row->ledger_balance ?? '—',
            'last_payment_date' => $row->last_payment_date ?? '—',
            'status' => $row->status,
        ])->all();

        return view('graduation.finance-clearance', compact('clearances'))->with(
            'operationalCreate',
            $this->createForm('Add finance clearance', 'Persists to graduation_finance_clearances.', 'graduation.finance-clearance.store', [
                ['name' => 'student_name', 'label' => 'Student', 'required' => true],
                ['name' => 'reg_no', 'label' => 'Registration number'],
                ['name' => 'programme', 'label' => 'Programme'],
                ['name' => 'ledger_balance', 'label' => 'Ledger balance'],
                ['name' => 'last_payment_date', 'label' => 'Last payment date'],
                ['name' => 'status', 'label' => 'Status'],
            ]),
        );
    }

    public function storeFinanceClearance(Request $request): RedirectResponse
    {
        return $this->store($request, GraduationFinanceClearance::class, [
            'student_name' => ['required', 'string', 'max:190'],
            'reg_no' => ['nullable', 'string', 'max:40'],
            'programme' => ['nullable', 'string', 'max:190'],
            'ledger_balance' => ['nullable', 'string', 'max:80'],
            'last_payment_date' => ['nullable', 'string', 'max:40'],
            'status' => ['nullable', 'string', 'max:40'],
        ], ['status' => 'Pending'], 'Finance clearance saved.', resolveStudent: true);
    }

    public function gradeList(Request $request): View
    {
        $grades = GraduationGradeEntry::query()->latest()->get()->map(fn (GraduationGradeEntry $row): array => [
            'student_name' => $row->student_name,
            'reg_no' => $row->reg_no ?? '—',
            'cgpa' => $row->cgpa ?? '—',
            'classification' => $row->classification ?? '—',
            'grades_distribution' => $row->grades_distribution ?? '—',
            'status' => $row->status,
        ])->all();

        return view('graduation.grade-list', compact('grades'))->with(
            'operationalCreate',
            $this->createForm('Add grade list entry', 'Persists to graduation_grade_entries.', 'graduation.grade-list.store', [
                ['name' => 'student_name', 'label' => 'Student', 'required' => true],
                ['name' => 'reg_no', 'label' => 'Registration number'],
                ['name' => 'cgpa', 'label' => 'CGPA'],
                ['name' => 'classification', 'label' => 'Class of degree'],
                ['name' => 'grades_distribution', 'label' => 'Grades distribution'],
                ['name' => 'status', 'label' => 'Status'],
            ]),
        );
    }

    public function storeGradeList(Request $request): RedirectResponse
    {
        return $this->store($request, GraduationGradeEntry::class, [
            'student_name' => ['required', 'string', 'max:190'],
            'reg_no' => ['nullable', 'string', 'max:40'],
            'cgpa' => ['nullable', 'string', 'max:40'],
            'classification' => ['nullable', 'string', 'max:80'],
            'grades_distribution' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:40'],
        ], ['status' => 'Eligible'], 'Grade list entry saved.', resolveStudent: true);
    }

    public function generateList(Request $request): View
    {
        $generators = GraduationListBatch::query()->latest()->get()->map(fn (GraduationListBatch $row): array => [
            'generation_run' => $row->generation_run,
            'school' => $row->school ?? '—',
            'cohort' => $row->cohort ?? '—',
            'run_date' => $row->run_date ?? '—',
            'total_qualified' => (string) $row->total_qualified,
            'status' => $row->status,
        ])->all();

        return view('graduation.generate-list', compact('generators'))->with(
            'operationalCreate',
            $this->createForm('Generate graduands batch', 'Persists to graduation_list_batches.', 'graduation.generate-list.store', [
                ['name' => 'generation_run', 'label' => 'Generation run', 'required' => true],
                ['name' => 'school', 'label' => 'School'],
                ['name' => 'cohort', 'label' => 'Cohort'],
                ['name' => 'run_date', 'label' => 'Run date'],
                ['name' => 'total_qualified', 'label' => 'Total qualified', 'type' => 'number'],
                ['name' => 'status', 'label' => 'Status'],
            ]),
        );
    }

    public function storeGenerateList(Request $request): RedirectResponse
    {
        return $this->store($request, GraduationListBatch::class, [
            'generation_run' => ['required', 'string', 'max:80', 'unique:graduation_list_batches,generation_run'],
            'school' => ['nullable', 'string', 'max:190'],
            'cohort' => ['nullable', 'string', 'max:80'],
            'run_date' => ['nullable', 'string', 'max:40'],
            'total_qualified' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'string', 'max:40'],
        ], ['status' => 'Pending', 'total_qualified' => 0], 'Graduation list batch saved.');
    }

    public function validateList(Request $request): View
    {
        $validations = GraduationListValidation::query()->latest()->get()->map(fn (GraduationListValidation $row): array => [
            'validation_code' => $row->validation_code,
            'school' => $row->school ?? '—',
            'total_candidates' => (string) $row->total_candidates,
            'dean_signoff' => $row->dean_signoff ?? '—',
            'registrar_audit' => $row->registrar_audit ?? '—',
            'status' => $row->status,
        ])->all();

        return view('graduation.validate-list', compact('validations'))->with(
            'operationalCreate',
            $this->createForm('Add list validation', 'Persists to graduation_list_validations.', 'graduation.validate-list.store', [
                ['name' => 'validation_code', 'label' => 'Validation code', 'required' => true],
                ['name' => 'school', 'label' => 'School'],
                ['name' => 'total_candidates', 'label' => 'Candidates', 'type' => 'number'],
                ['name' => 'dean_signoff', 'label' => 'Dean sign-off'],
                ['name' => 'registrar_audit', 'label' => 'Registrar audit'],
                ['name' => 'status', 'label' => 'Status'],
            ]),
        );
    }

    public function storeValidateList(Request $request): RedirectResponse
    {
        return $this->store($request, GraduationListValidation::class, [
            'validation_code' => ['required', 'string', 'max:80', 'unique:graduation_list_validations,validation_code'],
            'school' => ['nullable', 'string', 'max:190'],
            'total_candidates' => ['nullable', 'integer', 'min:0'],
            'dean_signoff' => ['nullable', 'string', 'max:120'],
            'registrar_audit' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'string', 'max:40'],
        ], ['status' => 'Pending', 'total_candidates' => 0], 'List validation saved.');
    }

    public function publishList(Request $request): View
    {
        $publications = GraduationPublication::query()->latest()->get()->map(fn (GraduationPublication $row): array => [
            'publication_code' => $row->publication_code,
            'list_title' => $row->list_title,
            'date_published' => $row->date_published ?? '—',
            'published_by' => $row->published_by ?? '—',
            'total_graduands' => (string) $row->total_graduands,
            'status' => $row->status,
        ])->all();

        return view('graduation.publish-list', compact('publications'))->with(
            'operationalCreate',
            $this->createForm('Publish graduation list', 'Persists to graduation_publications.', 'graduation.publish-list.store', [
                ['name' => 'publication_code', 'label' => 'Publication code', 'required' => true],
                ['name' => 'list_title', 'label' => 'List title', 'required' => true],
                ['name' => 'date_published', 'label' => 'Date published'],
                ['name' => 'published_by', 'label' => 'Published by'],
                ['name' => 'total_graduands', 'label' => 'Total graduands', 'type' => 'number'],
                ['name' => 'status', 'label' => 'Status'],
            ]),
        );
    }

    public function storePublishList(Request $request): RedirectResponse
    {
        return $this->store($request, GraduationPublication::class, [
            'publication_code' => ['required', 'string', 'max:80', 'unique:graduation_publications,publication_code'],
            'list_title' => ['required', 'string', 'max:190'],
            'date_published' => ['nullable', 'string', 'max:40'],
            'published_by' => ['nullable', 'string', 'max:120'],
            'total_graduands' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'string', 'max:40'],
        ], ['status' => 'Draft', 'total_graduands' => 0], 'Publication saved.');
    }

    public function listReport(Request $request): View
    {
        $reports = GraduationListReport::query()->latest()->get()->map(fn (GraduationListReport $row): array => [
            'report_ref' => $row->report_ref,
            'school' => $row->school ?? '—',
            'department' => $row->department ?? '—',
            'total_candidates' => (string) $row->total_candidates,
            'file_format' => $row->file_format ?? '—',
            'status' => $row->status,
        ])->all();

        return view('graduation.list-report', compact('reports'))->with(
            'operationalCreate',
            $this->createForm('Add list report', 'Persists to graduation_list_reports.', 'graduation.list-report.store', [
                ['name' => 'report_ref', 'label' => 'Report ref', 'required' => true],
                ['name' => 'school', 'label' => 'School'],
                ['name' => 'department', 'label' => 'Department'],
                ['name' => 'total_candidates', 'label' => 'Candidates', 'type' => 'number'],
                ['name' => 'file_format', 'label' => 'File format'],
                ['name' => 'status', 'label' => 'Status'],
            ]),
        );
    }

    public function storeListReport(Request $request): RedirectResponse
    {
        return $this->store($request, GraduationListReport::class, [
            'report_ref' => ['required', 'string', 'max:80', 'unique:graduation_list_reports,report_ref'],
            'school' => ['nullable', 'string', 'max:190'],
            'department' => ['nullable', 'string', 'max:190'],
            'total_candidates' => ['nullable', 'integer', 'min:0'],
            'file_format' => ['nullable', 'string', 'max:40'],
            'status' => ['nullable', 'string', 'max:40'],
        ], ['status' => 'Pending', 'total_candidates' => 0], 'List report saved.');
    }

    public function summaryList(Request $request): View
    {
        $summaries = GraduationSummary::query()->latest()->get()->map(fn (GraduationSummary $row): array => [
            'school' => $row->school,
            'degree_count' => (string) $row->degree_count,
            'diploma_count' => (string) $row->diploma_count,
            'masters_count' => (string) $row->masters_count,
            'phd_count' => (string) $row->phd_count,
            'total' => (string) $row->total,
            'status' => $row->status,
        ])->all();

        return view('graduation.summary-list', compact('summaries'))->with(
            'operationalCreate',
            $this->createForm('Add graduation summary', 'Persists to graduation_summaries.', 'graduation.summary-list.store', [
                ['name' => 'school', 'label' => 'School', 'required' => true],
                ['name' => 'degree_count', 'label' => 'Degree count', 'type' => 'number'],
                ['name' => 'diploma_count', 'label' => 'Diploma count', 'type' => 'number'],
                ['name' => 'masters_count', 'label' => 'Masters count', 'type' => 'number'],
                ['name' => 'phd_count', 'label' => 'PhD count', 'type' => 'number'],
                ['name' => 'total', 'label' => 'Total', 'type' => 'number'],
                ['name' => 'status', 'label' => 'Status'],
            ]),
        );
    }

    public function storeSummaryList(Request $request): RedirectResponse
    {
        return $this->store($request, GraduationSummary::class, [
            'school' => ['required', 'string', 'max:190'],
            'degree_count' => ['nullable', 'integer', 'min:0'],
            'diploma_count' => ['nullable', 'integer', 'min:0'],
            'masters_count' => ['nullable', 'integer', 'min:0'],
            'phd_count' => ['nullable', 'integer', 'min:0'],
            'total' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'string', 'max:40'],
        ], [
            'status' => 'Active',
            'degree_count' => 0,
            'diploma_count' => 0,
            'masters_count' => 0,
            'phd_count' => 0,
            'total' => 0,
        ], 'Summary saved.');
    }

    public function certificationSetup(Request $request): View
    {
        $templates = GraduationCertificateTemplate::query()->latest()->get()->map(fn (GraduationCertificateTemplate $row): array => [
            'template_code' => $row->template_code,
            'name' => $row->name,
            'dimensions' => $row->dimensions ?? '—',
            'security_features' => $row->security_features ?? '—',
            'signatories' => $row->signatories ?? '—',
            'status' => $row->status,
        ])->all();

        return view('graduation.certification-setup', compact('templates'))->with(
            'operationalCreate',
            $this->createForm('Add certificate template', 'Persists to graduation_certificate_templates.', 'graduation.certification-setup.store', [
                ['name' => 'template_code', 'label' => 'Template code', 'required' => true],
                ['name' => 'name', 'label' => 'Name', 'required' => true],
                ['name' => 'dimensions', 'label' => 'Dimensions'],
                ['name' => 'security_features', 'label' => 'Security features'],
                ['name' => 'signatories', 'label' => 'Signatories'],
                ['name' => 'status', 'label' => 'Status'],
            ]),
        );
    }

    public function storeCertificationSetup(Request $request): RedirectResponse
    {
        return $this->store($request, GraduationCertificateTemplate::class, [
            'template_code' => ['required', 'string', 'max:80', 'unique:graduation_certificate_templates,template_code'],
            'name' => ['required', 'string', 'max:190'],
            'dimensions' => ['nullable', 'string', 'max:80'],
            'security_features' => ['nullable', 'string', 'max:255'],
            'signatories' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:40'],
        ], ['status' => 'Active'], 'Certificate template saved.');
    }

    public function alumniList(Request $request): View
    {
        $alumni = GraduationAlumnus::query()->latest()->get()->map(fn (GraduationAlumnus $row): array => [
            'alumni_code' => $row->alumni_code,
            'student_name' => $row->student_name,
            'reg_no' => $row->reg_no ?? '—',
            'programme' => $row->programme ?? '—',
            'contact' => $row->contact ?? '—',
            'grad_year' => $row->grad_year ?? '—',
            'status' => $row->status,
        ])->all();

        return view('graduation.alumni-list', compact('alumni'))->with(
            'operationalCreate',
            $this->createForm('Add alumni record', 'Persists to graduation_alumni.', 'graduation.alumni-list.store', [
                ['name' => 'alumni_code', 'label' => 'Alumni code', 'required' => true],
                ['name' => 'student_name', 'label' => 'Alumni name', 'required' => true],
                ['name' => 'reg_no', 'label' => 'Former registration number'],
                ['name' => 'programme', 'label' => 'Programme'],
                ['name' => 'contact', 'label' => 'Contact'],
                ['name' => 'grad_year', 'label' => 'Graduation year'],
                ['name' => 'status', 'label' => 'Status'],
            ]),
        );
    }

    public function storeAlumniList(Request $request): RedirectResponse
    {
        return $this->store($request, GraduationAlumnus::class, [
            'alumni_code' => ['required', 'string', 'max:80', 'unique:graduation_alumni,alumni_code'],
            'student_name' => ['required', 'string', 'max:190'],
            'reg_no' => ['nullable', 'string', 'max:40'],
            'programme' => ['nullable', 'string', 'max:190'],
            'contact' => ['nullable', 'string', 'max:190'],
            'grad_year' => ['nullable', 'string', 'max:20'],
            'status' => ['nullable', 'string', 'max:40'],
        ], ['status' => 'Active'], 'Alumni record saved.');
    }

    public function ceremony(Request $request): View
    {
        $ceremonies = GraduationCeremony::query()->latest()->get()->map(fn (GraduationCeremony $row): array => [
            'congregation_number' => $row->congregation_number,
            'date' => $row->date ?? '—',
            'chief_guest' => $row->chief_guest ?? '—',
            'gown_return_deadline' => $row->gown_return_deadline ?? '—',
            'gown_fine_rate' => $row->gown_fine_rate ?? '—',
            'status' => $row->status,
        ])->all();

        return view('graduation.ceremony', compact('ceremonies'))->with(
            'operationalCreate',
            $this->createForm('Add ceremony', 'Persists to graduation_ceremonies.', 'graduation.ceremony.store', [
                ['name' => 'congregation_number', 'label' => 'Congregation number', 'required' => true],
                ['name' => 'date', 'label' => 'Date'],
                ['name' => 'chief_guest', 'label' => 'Chief guest'],
                ['name' => 'gown_return_deadline', 'label' => 'Gown return deadline'],
                ['name' => 'gown_fine_rate', 'label' => 'Gown fine rate'],
                ['name' => 'status', 'label' => 'Status'],
            ]),
        );
    }

    public function storeCeremony(Request $request): RedirectResponse
    {
        return $this->store($request, GraduationCeremony::class, [
            'congregation_number' => ['required', 'string', 'max:80', 'unique:graduation_ceremonies,congregation_number'],
            'date' => ['nullable', 'string', 'max:40'],
            'chief_guest' => ['nullable', 'string', 'max:190'],
            'gown_return_deadline' => ['nullable', 'string', 'max:40'],
            'gown_fine_rate' => ['nullable', 'string', 'max:80'],
            'status' => ['nullable', 'string', 'max:40'],
        ], ['status' => 'Upcoming'], 'Ceremony saved.');
    }

    public function ceremonyReport(Request $request): View
    {
        $reports = GraduationCeremonyReport::query()->latest()->get()->map(fn (GraduationCeremonyReport $row): array => [
            'report_ref' => $row->report_ref,
            'title' => $row->title,
            'audit_date' => $row->audit_date ?? '—',
            'compiled_by' => $row->compiled_by ?? '—',
            'senate_submission' => $row->senate_submission ?? '—',
            'status' => $row->status,
        ])->all();

        return view('graduation.ceremony-report', compact('reports'))->with(
            'operationalCreate',
            $this->createForm('Add ceremony report', 'Persists to graduation_ceremony_reports.', 'graduation.ceremony-report.store', [
                ['name' => 'report_ref', 'label' => 'Report ref', 'required' => true],
                ['name' => 'title', 'label' => 'Title', 'required' => true],
                ['name' => 'audit_date', 'label' => 'Audit date'],
                ['name' => 'compiled_by', 'label' => 'Compiled by'],
                ['name' => 'senate_submission', 'label' => 'Senate submission'],
                ['name' => 'status', 'label' => 'Status'],
            ]),
        );
    }

    public function storeCeremonyReport(Request $request): RedirectResponse
    {
        return $this->store($request, GraduationCeremonyReport::class, [
            'report_ref' => ['required', 'string', 'max:80', 'unique:graduation_ceremony_reports,report_ref'],
            'title' => ['required', 'string', 'max:190'],
            'audit_date' => ['nullable', 'string', 'max:40'],
            'compiled_by' => ['nullable', 'string', 'max:120'],
            'senate_submission' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'string', 'max:40'],
        ], ['status' => 'Draft'], 'Ceremony report saved.');
    }

    /**
     * @param  class-string<Model>  $model
     * @param  array<string, list<mixed>>  $rules
     * @param  array<string, mixed>  $defaults
     */
    private function store(
        Request $request,
        string $model,
        array $rules,
        array $defaults,
        string $message,
        bool $resolveStudent = false,
    ): RedirectResponse {
        $this->authorizePermission($request, 'graduation.manage');
        $data = $request->validate($rules);
        foreach ($defaults as $key => $value) {
            if (! array_key_exists($key, $data) || $data[$key] === null || $data[$key] === '') {
                $data[$key] = $value;
            }
        }
        if ($resolveStudent) {
            $data['student_id'] = Student::query()->where('admission_number', $data['reg_no'] ?? '')->value('id');
        }
        $model::query()->create($data);

        return back()->with('success', $message);
    }

    /**
     * @param  list<array{name: string, label: string, type?: string, required?: bool}>  $fields
     * @return array{title: string, hint: string, action: string, fields: list<array{name: string, label: string, type?: string, required?: bool}>}
     */
    private function createForm(string $title, string $hint, string $route, array $fields): array
    {
        return [
            'title' => $title,
            'hint' => $hint,
            'action' => route($route),
            'fields' => $fields,
        ];
    }
}
