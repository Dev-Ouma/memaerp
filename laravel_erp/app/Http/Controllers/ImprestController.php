<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesCataloguePermission;
use App\Models\ImprestAuditLedger;
use App\Models\ImprestClaimMatrix;
use App\Models\ImprestPermission;
use App\Models\ImprestRequisition;
use App\Models\ImprestSurrender;
use App\Models\ImprestSurrenderRule;
use App\Support\SoftStatsBag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ImprestController extends Controller
{
    use AuthorizesCataloguePermission;

    public function permissions(Request $request): View
    {
        $records = ImprestPermission::query()->latest()->get();
        $permissions = $records->map(fn (ImprestPermission $row): array => [
            'role_title' => $row->role_title,
            'authority_level' => $row->authority_level ?? '—',
            'min_limit' => $row->min_limit ?? '—',
            'max_limit' => $row->max_limit ?? '—',
            'allowed_categories' => $row->allowed_categories ?? '—',
            'mandate_rule' => $row->mandate_rule ?? '—',
            'status' => $row->status,
        ])->all();
        $stats = new SoftStatsBag([
            'activeAuthorityTiers' => $records->count(),
            'maxApprovalLimit' => $records->pluck('max_limit')->filter()->first() ?? '—',
            'authorizedApprovers' => $records->filter(fn (ImprestPermission $r): bool => str_contains(strtolower($r->status), 'active'))->count(),
            'activeVoteHeads' => $records->pluck('allowed_categories')->filter()->unique()->count(),
        ]);

        return view('imprest.permissions', compact('permissions', 'stats'))->with(
            'operationalCreate',
            $this->form('Add imprest permission', 'Persists to imprest_permissions.', 'imprest.permissions.store', [
                ['name' => 'role_title', 'label' => 'Role title', 'required' => true],
                ['name' => 'authority_level', 'label' => 'Authority level'],
                ['name' => 'min_limit', 'label' => 'Min limit'],
                ['name' => 'max_limit', 'label' => 'Max limit'],
                ['name' => 'allowed_categories', 'label' => 'Allowed categories'],
                ['name' => 'mandate_rule', 'label' => 'Mandate rule'],
                ['name' => 'status', 'label' => 'Status'],
            ]),
        );
    }

    public function storePermissions(Request $request): RedirectResponse
    {
        return $this->store($request, ImprestPermission::class, [
            'role_title' => ['required', 'string', 'max:190'],
            'authority_level' => ['nullable', 'string', 'max:80'],
            'min_limit' => ['nullable', 'string', 'max:80'],
            'max_limit' => ['nullable', 'string', 'max:80'],
            'allowed_categories' => ['nullable', 'string', 'max:255'],
            'mandate_rule' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:40'],
        ], ['status' => 'Active'], 'Imprest permission saved.');
    }

    public function claimApprovals(Request $request): View
    {
        $records = ImprestClaimMatrix::query()->latest()->get();
        $approvalMatrices = $records->map(fn (ImprestClaimMatrix $row): array => [
            'workflow_code' => $row->workflow_code,
            'claim_category' => $row->claim_category ?? '—',
            'originating_unit' => $row->originating_unit ?? '—',
            'workflow_sequence' => $row->workflow_sequence ?? '—',
            'auto_escalation_hours' => (string) $row->auto_escalation_hours,
            'delegate_allowed' => $row->delegate_allowed ?? '—',
            'status' => $row->status,
        ])->all();
        $stats = new SoftStatsBag([
            'pendingClaimVetting' => $records->filter(fn (ImprestClaimMatrix $r): bool => str_contains(strtolower($r->status), 'pending'))->count(),
            'approvedThisMonth' => $records->filter(fn (ImprestClaimMatrix $r): bool => str_contains(strtolower($r->status), 'active') || str_contains(strtolower($r->status), 'approv'))->count(),
            'avgProcessingSLA' => ($records->avg('auto_escalation_hours') ?: 0).'h',
            'escalationRulesActive' => $records->filter(fn (ImprestClaimMatrix $r): bool => (int) $r->auto_escalation_hours > 0)->count(),
        ]);

        return view('imprest.claim-approvals', compact('approvalMatrices', 'stats'))->with(
            'operationalCreate',
            $this->form('Add claim approval matrix', 'Persists to imprest_claim_matrices.', 'imprest.claim-approvals.store', [
                ['name' => 'workflow_code', 'label' => 'Workflow code', 'required' => true],
                ['name' => 'claim_category', 'label' => 'Claim category'],
                ['name' => 'originating_unit', 'label' => 'Originating unit'],
                ['name' => 'workflow_sequence', 'label' => 'Workflow sequence'],
                ['name' => 'auto_escalation_hours', 'label' => 'Escalation hours', 'type' => 'number'],
                ['name' => 'delegate_allowed', 'label' => 'Delegate allowed'],
                ['name' => 'status', 'label' => 'Status'],
            ]),
        );
    }

    public function storeClaimApprovals(Request $request): RedirectResponse
    {
        return $this->store($request, ImprestClaimMatrix::class, [
            'workflow_code' => ['required', 'string', 'max:80', 'unique:imprest_claim_matrices,workflow_code'],
            'claim_category' => ['nullable', 'string', 'max:120'],
            'originating_unit' => ['nullable', 'string', 'max:190'],
            'workflow_sequence' => ['nullable', 'string', 'max:255'],
            'auto_escalation_hours' => ['nullable', 'integer', 'min:0'],
            'delegate_allowed' => ['nullable', 'string', 'max:40'],
            'status' => ['nullable', 'string', 'max:40'],
        ], ['status' => 'Active', 'auto_escalation_hours' => 0], 'Claim approval matrix saved.');
    }

    public function surrenderPermissions(Request $request): View
    {
        $records = ImprestSurrenderRule::query()->latest()->get();
        $surrenderRules = $records->map(fn (ImprestSurrenderRule $row): array => [
            'policy_code' => $row->policy_code,
            'title' => $row->title,
            'timeline' => $row->timeline ?? '—',
            'document_requirements' => $row->document_requirements ?? '—',
            'non_compliance_action' => $row->non_compliance_action ?? '—',
            'waiver_authority' => $row->waiver_authority ?? '—',
        ])->all();
        $stats = new SoftStatsBag([
            'surrenderGracePeriod' => $records->pluck('timeline')->filter()->first() ?? '—',
            'complianceRate' => $records->count() ? '100%' : '0%',
            'activeRecoveryTriggers' => $records->filter(fn (ImprestSurrenderRule $r): bool => filled($r->non_compliance_action))->count(),
            'outstandingOverdue' => '—',
        ]);

        return view('imprest.surrender-permissions', compact('surrenderRules', 'stats'))->with(
            'operationalCreate',
            $this->form('Add surrender rule', 'Persists to imprest_surrender_rules.', 'imprest.surrender-permissions.store', [
                ['name' => 'policy_code', 'label' => 'Policy code', 'required' => true],
                ['name' => 'title', 'label' => 'Title', 'required' => true],
                ['name' => 'timeline', 'label' => 'Timeline'],
                ['name' => 'document_requirements', 'label' => 'Document requirements'],
                ['name' => 'non_compliance_action', 'label' => 'Non-compliance action'],
                ['name' => 'waiver_authority', 'label' => 'Waiver authority'],
            ]),
        );
    }

    public function storeSurrenderPermissions(Request $request): RedirectResponse
    {
        return $this->store($request, ImprestSurrenderRule::class, [
            'policy_code' => ['required', 'string', 'max:80', 'unique:imprest_surrender_rules,policy_code'],
            'title' => ['required', 'string', 'max:190'],
            'timeline' => ['nullable', 'string', 'max:120'],
            'document_requirements' => ['nullable', 'string', 'max:255'],
            'non_compliance_action' => ['nullable', 'string', 'max:255'],
            'waiver_authority' => ['nullable', 'string', 'max:120'],
        ], [], 'Surrender rule saved.');
    }

    public function requisitions(Request $request): View
    {
        $records = ImprestRequisition::query()->latest()->get();
        $requisitions = $records->map(fn (ImprestRequisition $row): array => [
            'requisition_no' => $row->requisition_no,
            'applicant_name' => $row->applicant_name,
            'department' => $row->department ?? '—',
            'vote_head' => $row->vote_head ?? '—',
            'amount_requested' => $row->amount_requested ?? '—',
            'purpose' => $row->purpose ?? '—',
            'disbursement_mode' => $row->disbursement_mode ?? '—',
            'surrender_due_date' => $row->surrender_due_date ?? '—',
            'status' => $row->status,
        ])->all();
        $stats = new SoftStatsBag([
            'totalRequisitions' => $records->count(),
            'approvedDisbursed' => $records->filter(fn (ImprestRequisition $r): bool => str_contains(strtolower($r->status), 'approv') || str_contains(strtolower($r->status), 'disburs'))->count(),
            'pendingApprovals' => $records->filter(fn (ImprestRequisition $r): bool => str_contains(strtolower($r->status), 'pending'))->count(),
            'disbursedViaMpesa' => $records->filter(fn (ImprestRequisition $r): bool => str_contains(strtolower((string) $r->disbursement_mode), 'pesa'))->count(),
        ]);

        return view('imprest.requisitions', compact('requisitions', 'stats'))->with(
            'operationalCreate',
            $this->form('Add requisition', 'Persists to imprest_requisitions.', 'imprest.requisitions.store', [
                ['name' => 'requisition_no', 'label' => 'Requisition number', 'required' => true],
                ['name' => 'applicant_name', 'label' => 'Applicant', 'required' => true],
                ['name' => 'department', 'label' => 'Department'],
                ['name' => 'vote_head', 'label' => 'Vote head'],
                ['name' => 'amount_requested', 'label' => 'Amount requested'],
                ['name' => 'purpose', 'label' => 'Purpose'],
                ['name' => 'disbursement_mode', 'label' => 'Disbursement mode'],
                ['name' => 'surrender_due_date', 'label' => 'Surrender due date'],
                ['name' => 'status', 'label' => 'Status'],
            ]),
        );
    }

    public function storeRequisitions(Request $request): RedirectResponse
    {
        return $this->store($request, ImprestRequisition::class, [
            'requisition_no' => ['required', 'string', 'max:80', 'unique:imprest_requisitions,requisition_no'],
            'applicant_name' => ['required', 'string', 'max:190'],
            'department' => ['nullable', 'string', 'max:190'],
            'vote_head' => ['nullable', 'string', 'max:120'],
            'amount_requested' => ['nullable', 'string', 'max:80'],
            'purpose' => ['nullable', 'string', 'max:255'],
            'disbursement_mode' => ['nullable', 'string', 'max:80'],
            'surrender_due_date' => ['nullable', 'string', 'max:40'],
            'status' => ['nullable', 'string', 'max:40'],
        ], ['status' => 'Pending'], 'Requisition saved.');
    }

    public function surrenders(Request $request): View
    {
        $records = ImprestSurrender::query()->latest()->get();
        $surrenders = $records->map(fn (ImprestSurrender $row): array => [
            'surrender_no' => $row->surrender_no,
            'requisition_ref' => $row->requisition_ref ?? '—',
            'staff_name' => $row->staff_name,
            'department' => $row->department ?? '—',
            'imprest_amount' => $row->imprest_amount ?? '—',
            'actual_expenditure' => $row->actual_expenditure ?? '—',
            'unspent_refund' => $row->unspent_refund ?? '—',
            'supplementary_claim' => $row->supplementary_claim ?? '—',
            'etims_compliance' => $row->etims_compliance ?? '—',
            'audit_verdict' => $row->audit_verdict ?? '—',
            'surrender_status' => $row->surrender_status,
        ])->all();
        $stats = new SoftStatsBag([
            'surrenderedThisMonth' => $records->count(),
            'fullyReconciled' => $records->filter(fn (ImprestSurrender $r): bool => str_contains(strtolower($r->surrender_status), 'cleared') || str_contains(strtolower($r->surrender_status), 'reconcil'))->count(),
            'pendingAuditVerification' => $records->filter(fn (ImprestSurrender $r): bool => str_contains(strtolower($r->surrender_status), 'pending'))->count(),
            'refundsRecovered' => $records->filter(fn (ImprestSurrender $r): bool => filled($r->unspent_refund))->count(),
        ]);

        return view('imprest.surrenders', compact('surrenders', 'stats'))->with(
            'operationalCreate',
            $this->form('Add surrender', 'Persists to imprest_surrenders.', 'imprest.surrenders.store', [
                ['name' => 'surrender_no', 'label' => 'Surrender number', 'required' => true],
                ['name' => 'requisition_ref', 'label' => 'Requisition ref'],
                ['name' => 'staff_name', 'label' => 'Staff', 'required' => true],
                ['name' => 'department', 'label' => 'Department'],
                ['name' => 'imprest_amount', 'label' => 'Imprest amount'],
                ['name' => 'actual_expenditure', 'label' => 'Actual expenditure'],
                ['name' => 'unspent_refund', 'label' => 'Unspent refund'],
                ['name' => 'supplementary_claim', 'label' => 'Supplementary claim'],
                ['name' => 'etims_compliance', 'label' => 'eTIMS compliance'],
                ['name' => 'audit_verdict', 'label' => 'Audit verdict'],
                ['name' => 'surrender_status', 'label' => 'Status'],
            ]),
        );
    }

    public function storeSurrenders(Request $request): RedirectResponse
    {
        return $this->store($request, ImprestSurrender::class, [
            'surrender_no' => ['required', 'string', 'max:80', 'unique:imprest_surrenders,surrender_no'],
            'requisition_ref' => ['nullable', 'string', 'max:80'],
            'staff_name' => ['required', 'string', 'max:190'],
            'department' => ['nullable', 'string', 'max:190'],
            'imprest_amount' => ['nullable', 'string', 'max:80'],
            'actual_expenditure' => ['nullable', 'string', 'max:80'],
            'unspent_refund' => ['nullable', 'string', 'max:80'],
            'supplementary_claim' => ['nullable', 'string', 'max:80'],
            'etims_compliance' => ['nullable', 'string', 'max:80'],
            'audit_verdict' => ['nullable', 'string', 'max:80'],
            'surrender_status' => ['nullable', 'string', 'max:40'],
        ], ['surrender_status' => 'Pending'], 'Surrender saved.');
    }

    public function auditLedger(Request $request): View
    {
        $records = ImprestAuditLedger::query()->latest()->get();
        $agingRecords = $records->map(fn (ImprestAuditLedger $row): array => [
            'imprest_ref' => $row->imprest_ref,
            'staff_name' => $row->staff_name,
            'staff_no' => $row->staff_no ?? '—',
            'department' => $row->department ?? '—',
            'amount_due' => $row->amount_due ?? '—',
            'issue_date' => $row->issue_date ?? '—',
            'due_date' => $row->due_date ?? '—',
            'days_overdue' => (string) $row->days_overdue,
            'risk_category' => $row->risk_category ?? '—',
            'recovery_status' => $row->recovery_status,
        ])->all();
        $stats = new SoftStatsBag([
            'totalActiveImprest' => $records->count(),
            'currentNotDue' => $records->filter(fn (ImprestAuditLedger $r): bool => (int) $r->days_overdue === 0)->count(),
            'overdue1to14Days' => $records->filter(fn (ImprestAuditLedger $r): bool => (int) $r->days_overdue > 0 && (int) $r->days_overdue <= 14)->count(),
            'criticalOverdueSalaryRecovery' => $records->filter(fn (ImprestAuditLedger $r): bool => (int) $r->days_overdue > 14 || str_contains(strtolower((string) $r->risk_category), 'critical'))->count(),
        ]);

        return view('imprest.audit-ledger', compact('agingRecords', 'stats'))->with(
            'operationalCreate',
            $this->form('Add audit ledger row', 'Persists to imprest_audit_ledgers.', 'imprest.audit-ledger.store', [
                ['name' => 'imprest_ref', 'label' => 'Imprest ref', 'required' => true],
                ['name' => 'staff_name', 'label' => 'Staff', 'required' => true],
                ['name' => 'staff_no', 'label' => 'Staff number'],
                ['name' => 'department', 'label' => 'Department'],
                ['name' => 'amount_due', 'label' => 'Amount due'],
                ['name' => 'issue_date', 'label' => 'Issue date'],
                ['name' => 'due_date', 'label' => 'Due date'],
                ['name' => 'days_overdue', 'label' => 'Days overdue', 'type' => 'number'],
                ['name' => 'risk_category', 'label' => 'Risk category'],
                ['name' => 'recovery_status', 'label' => 'Recovery status'],
            ]),
        );
    }

    public function storeAuditLedger(Request $request): RedirectResponse
    {
        return $this->store($request, ImprestAuditLedger::class, [
            'imprest_ref' => ['required', 'string', 'max:80', 'unique:imprest_audit_ledgers,imprest_ref'],
            'staff_name' => ['required', 'string', 'max:190'],
            'staff_no' => ['nullable', 'string', 'max:40'],
            'department' => ['nullable', 'string', 'max:190'],
            'amount_due' => ['nullable', 'string', 'max:80'],
            'issue_date' => ['nullable', 'string', 'max:40'],
            'due_date' => ['nullable', 'string', 'max:40'],
            'days_overdue' => ['nullable', 'integer', 'min:0'],
            'risk_category' => ['nullable', 'string', 'max:80'],
            'recovery_status' => ['nullable', 'string', 'max:40'],
        ], ['recovery_status' => 'Open', 'days_overdue' => 0], 'Audit ledger row saved.');
    }

    /**
     * @param  class-string<Model>  $model
     * @param  array<string, list<mixed>>  $rules
     * @param  array<string, mixed>  $defaults
     */
    private function store(Request $request, string $model, array $rules, array $defaults, string $message): RedirectResponse
    {
        $this->authorizePermission($request, 'imprest.manage');
        $data = $request->validate($rules);
        foreach ($defaults as $key => $value) {
            if (! array_key_exists($key, $data) || $data[$key] === null || $data[$key] === '') {
                $data[$key] = $value;
            }
        }
        $model::query()->create($data);

        return back()->with('success', $message);
    }

    /**
     * @param  list<array{name: string, label: string, type?: string, required?: bool}>  $fields
     * @return array{title: string, hint: string, action: string, fields: list<array{name: string, label: string, type?: string, required?: bool}>}
     */
    private function form(string $title, string $hint, string $route, array $fields): array
    {
        return [
            'title' => $title,
            'hint' => $hint,
            'action' => route($route),
            'fields' => $fields,
        ];
    }
}
