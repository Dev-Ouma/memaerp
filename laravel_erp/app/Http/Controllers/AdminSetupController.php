<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Admission\AdminSetupDefinition;
use App\Models\Admission\AdminSetupVersion;
use App\Models\ModuleState;
use App\Modules\Admission\Setups\SetupManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class AdminSetupController extends Controller
{
    public function platformIndex(Request $request): View
    {
        $this->authorizeAdmin($request);

        $admissionsSummary = [
            'total' => AdminSetupDefinition::query()->count(),
            'active' => AdminSetupDefinition::query()->whereHas('versions', fn ($query) => $query->where('status', 'ACTIVE'))->count(),
        ];

        return view('admin.setups.index', compact('admissionsSummary'));
    }

    public function index(Request $request): View
    {
        $this->authorizeAdmin($request);
        $definitions = AdminSetupDefinition::query()->with(['versions' => fn ($q) => $q->latest('version')])
            ->when($request->q, fn ($q, $value) => $q->where(fn ($s) => $s->where('name', 'ilike', "%{$value}%")->orWhere('setup_key', 'ilike', "%{$value}%")))
            ->when($request->category, fn ($q, $value) => $q->where('category', $value))->orderBy('category')->orderBy('name')->paginate(15)->withQueryString();
        $categories = AdminSetupDefinition::query()->distinct()->orderBy('category')->pluck('category');
        $catalogue = AdminSetupDefinition::query()->with(['versions' => fn ($q) => $q->latest('version')])->get();
        $summary = [
            'total' => $catalogue->count(),
            'active' => $catalogue->filter(fn ($definition) => $definition->versions->contains('status', 'ACTIVE'))->count(),
            'draft' => $catalogue->filter(fn ($definition) => $definition->versions->contains('status', 'DRAFT'))->count(),
            'missing' => $catalogue->filter(fn ($definition) => $definition->versions->isEmpty())->count(),
        ];
        $categoryCounts = $catalogue->groupBy('category')->map->count()->sortDesc();

        return view('admissions.admin.setups.index', compact('definitions', 'categories', 'summary', 'categoryCounts'));
    }

    public function show(Request $request, AdminSetupDefinition $setup): View
    {
        $this->authorizeAdmin($request);
        $setup->load(['versions' => fn ($q) => $q->withCount('usages')->orderByDesc('version')]);

        return view('admissions.admin.setups.show', compact('setup'));
    }

    public function store(Request $request, AdminSetupDefinition $setup, SetupManager $manager): RedirectResponse
    {
        $this->authorizeAdmin($request);
        $data = $request->validate(['configuration' => ['required', 'json'], 'change_reason' => ['required', 'string', 'max:500']]);
        $manager->draft($setup, json_decode($data['configuration'], true, 512, JSON_THROW_ON_ERROR), $data['change_reason'], $request->user()->id);

        return back()->with('success', 'Draft setup version created.');
    }

    public function publish(Request $request, AdminSetupVersion $version, SetupManager $manager): RedirectResponse
    {
        $this->authorizeAdmin($request);
        $data = $request->validate(['effective_from' => ['required', 'date'], 'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from']]);
        $manager->publish($version, $data['effective_from'], $data['effective_to'] ?? null, $request->user()->id);

        return back()->with('success', 'Setup version published. New transactions will use it from its effective date.');
    }

    public function status(Request $request, AdminSetupVersion $version, SetupManager $manager): RedirectResponse
    {
        $this->authorizeAdmin($request);
        $data = $request->validate(['status' => ['required', 'in:INACTIVE,ARCHIVED']]);
        $manager->changeStatus($version, $data['status'], $request->user()->id);

        return back()->with('success', 'Setup status updated.');
    }

    /**
     * Accounting Admin Setup
     */
    public function accounting(Request $request): View
    {
        $this->authorizeAdmin($request);
        $stats = ['generalLedgers' => 48, 'fiscalYears' => 5, 'activeStatus' => 'Ledger Balanced'];

        return view('admin.setups.accounting', compact('stats'));
    }

    /**
     * Bank Admin Setup
     */
    public function bank(Request $request): View
    {
        $this->authorizeAdmin($request);
        $stats = ['linkedBanks' => 4, 'apiBridges' => 2, 'clearedFeeds' => 'Operational'];

        return view('admin.setups.bank', compact('stats'));
    }

    /**
     * Invoicing Admin Setup
     */
    public function invoicing(Request $request): View
    {
        $this->authorizeAdmin($request);
        $stats = ['billingCycles' => 3, 'taxSchemes' => 2, 'paymentRules' => 'Strict Check-in'];

        return view('admin.setups.invoicing', compact('stats'));
    }

    /**
     * Payment Admin Setup
     */
    public function payment(Request $request): View
    {
        $this->authorizeAdmin($request);
        $stats = ['payoutChannels' => 4, 'mpesaCredentials' => 'Daraja 2.0 Secure', 'auditTrailing' => 'Active'];

        return view('admin.setups.payment', compact('stats'));
    }

    /**
     * Module Manager (Active/Deactivate) Setup
     */
    public function moduleManager(Request $request): View
    {
        $this->authorizeAdmin($request);

        $modules = [
            [
                'key' => 'smhr',
                'name' => 'SMHR — Staff HR & Payroll',
                'icon' => 'users',
                'status' => 'ACTIVE',
                'dependencies' => 'Accounting Setups',
                'description' => 'Manage staff contracts, payroll disbursements, leave requests, appraisal cycles, and medical insurance coverage for all university staff.',
                'submodules' => ['Staff Directory', 'Payroll Runs', 'Leave Management', 'Appraisals', 'Medical Benefits'],
            ],
            [
                'key' => 'transfers',
                'name' => 'Student Transfers Registry',
                'icon' => 'arrow-left-right',
                'status' => 'ACTIVE',
                'dependencies' => 'Registration, Curriculum',
                'description' => 'Process cross-faculty and inter-institution transfers, manage deferrals, programme change requests, and carry-forward of prior study credits.',
                'submodules' => ['Transfer Dates', 'Transfer Applications', 'Rejected Transfers', 'Deferrals', 'Carryforwards'],
            ],
            [
                'key' => 'pg-research',
                'name' => 'PG Research & Graduate Studies',
                'icon' => 'book-marked',
                'status' => 'ACTIVE',
                'dependencies' => 'Registration, Examination',
                'description' => 'Manage postgraduate research enrolments, thesis proposal submissions, progress reports, panel meetings, and examination approvals.',
                'submodules' => ['Research Register', 'Supervisor Assignment', 'Thesis Submissions', 'Panel Reviews', 'Progress Reports'],
            ],
            [
                'key' => 'curriculum',
                'name' => 'Curriculum & Programmes Catalogue',
                'icon' => 'library',
                'status' => 'ACTIVE',
                'dependencies' => 'None',
                'description' => 'Define academic programmes, course unit structures, credit load configurations, prerequisite mappings, and faculty ownership bindings.',
                'submodules' => ['Programmes', 'Course Units', 'Credit Load', 'Prerequisites', 'Faculty Mapping'],
            ],
            [
                'key' => 'student-affairs',
                'name' => 'Student Affairs & Work Study',
                'icon' => 'heart-handshake',
                'status' => 'ACTIVE',
                'dependencies' => 'Registration',
                'description' => 'Administer work study allocations, student employment applications, timesheets, claim submissions, and payroll integration.',
                'submodules' => ['Work Study Applications', 'Work Study Allocations', 'Timesheets', 'Claims & Payroll'],
            ],
            [
                'key' => 'imprest',
                'name' => 'Imprest Requisitions & Surrenders',
                'icon' => 'wallet-cards',
                'status' => 'ACTIVE',
                'dependencies' => 'Accounting Setups',
                'description' => 'Manage petty cash imprest permissions, requisition workflows, surrender receipts, and an audited general ledger for all petty cash movements.',
                'submodules' => ['Permissions', 'Claim Approvals', 'Surrender Permissions', 'Requisitions', 'Surrenders', 'Audit Ledger'],
            ],
            [
                'key' => 'cohort',
                'name' => 'Cohort Setup & Academic Calendar',
                'icon' => 'calendar-range',
                'status' => 'ACTIVE',
                'dependencies' => 'Curriculum',
                'description' => 'Configure academic year calendars, cohort batch creation, programme-cohort mappings, and publish cohort finance structures to the fees module.',
                'submodules' => ['Academic Year', 'Cohort Creation', 'Programme Mapping', 'Publish Finance', 'Cohort Transfer'],
            ],
            [
                'key' => 'registration',
                'name' => 'Registration & Admissions Portal',
                'icon' => 'user-plus',
                'status' => 'ACTIVE',
                'dependencies' => 'Cohort, Curriculum, Fees',
                'description' => 'Manage student application verification, approvals, KUCCPS placements, course registration periods, nominal rolls, and user account provisioning.',
                'submodules' => ['App Verification', 'App Approval', 'KUCCPS', 'Course Registration', 'Nominal Roll', 'User Registration'],
            ],
            [
                'key' => 'lms',
                'name' => 'LMS Virtual Classrooms Portal',
                'icon' => 'book-open',
                'status' => 'ACTIVE',
                'dependencies' => 'Curriculum, Registration',
                'description' => 'Host virtual classrooms, course material uploads, faculty assignments, live lectures, continuous assessment, quizzes, and gradebook synchronisation.',
                'submodules' => ['Course Shells', 'Lecturer Assignments', 'Live Lectures', 'E-Resources', 'Assignments', 'Quizzes', 'Gradebook'],
            ],
            [
                'key' => 'examination',
                'name' => 'Examination & Grading Board',
                'icon' => 'file-pen',
                'status' => 'ACTIVE',
                'dependencies' => 'Registration, LMS',
                'description' => 'Configure exam centres, capture marks, run grade policy approvals, publish progression lists, generate provisional transcripts, and senate reports.',
                'submodules' => ['Exam Setup', 'Marks Capture', 'Marks Approval', 'Results', 'Transcripts', 'Senate Reports', 'Marksheets'],
            ],
            [
                'key' => 'fees',
                'name' => 'Fees Billing & M-Pesa Integration',
                'icon' => 'credit-card',
                'status' => 'ACTIVE',
                'dependencies' => 'Accounting Setups, Cohort',
                'description' => 'Automate student fee invoicing, manage M-Pesa Daraja 2.0 C2B payments, fee payables ledger, pending payment confirmations, and receipts printing.',
                'submodules' => ['Payment Accounts', 'Payment Types', 'Fee Setup', 'Fee Payables', 'Payments', 'Payment Receipt'],
            ],
            [
                'key' => 'graduation',
                'name' => 'Graduation & Alumni Registry',
                'icon' => 'award',
                'status' => 'ACTIVE',
                'dependencies' => 'Examination, Fees',
                'description' => 'Configure graduation criteria, run clearance checklists, compile and publish pass lists, manage ceremonies, gown allocations, and alumni database.',
                'submodules' => ['Criteria', 'Clearance', 'Generate List', 'Ceremony', 'Alumni List', 'Certification'],
            ],
            [
                'key' => 'task-management',
                'name' => 'Task Management & Role Bindings',
                'icon' => 'clipboard-check',
                'status' => 'ACTIVE',
                'dependencies' => 'None',
                'description' => 'Define administrative role hierarchies, bind specific ERP tasks to defined roles, and manage open action tickets across all system modules.',
                'submodules' => ['Roles', 'Task in Roles', 'Task Manager'],
            ],
            [
                'key' => 'reports',
                'name' => 'Reports & Analytics Intelligence',
                'icon' => 'bar-chart-3',
                'status' => 'ACTIVE',
                'dependencies' => 'All Modules',
                'description' => 'Access 29 operational submodule reports spanning admissions, fees, academic progression, and registry, plus an advanced analytics dashboard with charts.',
                'submodules' => ['Advanced Analytics', 'Admissions Reports', 'Fees Reports', 'Academic Reports', 'Audit Reports'],
            ],
            [
                'key' => 'service-providers',
                'name' => 'Service Providers & Procurement',
                'icon' => 'building-2',
                'status' => 'ACTIVE',
                'dependencies' => 'Accounting Setups',
                'description' => 'Manage supplier databases, provider group classifications, vendor vetting approvals, bills lifecycle, payment disbursements, and credit/debit notes.',
                'submodules' => ['Providers', 'Provider Groups', 'Bills', 'Payments', 'Debit Notes', 'Credit Notes', 'Taxes'],
            ],
            [
                'key' => 'budgeting',
                'name' => 'Budgeting & Capital Planning',
                'icon' => 'pie-chart',
                'status' => 'ACTIVE',
                'dependencies' => 'Accounting Setups',
                'description' => 'Compile trimester departmental budget proposals, track requested vs approved allocations, monitor variance deficits, and manage approval pipelines.',
                'submodules' => ['Budget Proposals', 'Permissions'],
            ],
            [
                'key' => 'recycle-bin',
                'name' => 'Recycle Bin & System Data Recovery',
                'icon' => 'trash-2',
                'status' => 'ACTIVE',
                'dependencies' => 'Database Soft-Deletes',
                'description' => 'System-wide trash recovery manager for restoring and permanently purging deleted academic schools, departments, programmes, syllabi, and calendars.',
                'submodules' => ['Trashed Entities', 'Restore Actions', 'Permanent Purge', 'Retention SLA Alarms'],
            ],
        ];

        // Load persisted states from DB and merge into each module card
        $states = ModuleState::allStates();
        foreach ($modules as &$mod) {
            $mod['is_active'] = $states[$mod['key']] ?? true;
        }
        unset($mod);

        return view('admin.setups.module-manager', compact('modules'));
    }

    /**
     * AJAX: Toggle a single module's active state.
     * Called by the toggle switch on the Module Manager page.
     */
    public function toggleModule(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        $validated = $request->validate([
            'module_key' => ['required', 'string', 'max:64'],
            'is_active' => ['required', 'boolean'],
        ]);

        $row = ModuleState::setActive(
            $validated['module_key'],
            (bool) $validated['is_active'],
            $request->user()->id,
        );

        return response()->json([
            'success' => true,
            'module_key' => $row->module_key,
            'is_active' => $row->is_active,
            'message' => $row->is_active
                ? 'Module activated successfully.'
                : 'Module deactivated successfully.',
        ]);
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()?->isAdmin(), 403);
    }
}
