<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\OperationalRecordService;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ImprestController extends Controller
{
    public function __construct(private readonly OperationalRecordService $records) {}

    public function permissions(Request $request): View
    {
        return $this->records->screen($request, 'imprest.permissions', 'imprest', 'permission', 'permissions', [
            ['key' => 'totalPermissions', 'op' => 'count'],
            ['key' => 'active', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Active'],
            ['key' => 'suspended', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Suspended'],
            ['key' => 'departments', 'op' => 'count'],
        ], [
            ['name' => 'staff_name', 'label' => 'Staff member', 'required' => true],
            ['name' => 'department', 'label' => 'Department'],
            ['name' => 'limit_amount', 'label' => 'Limit amount'],
            ['name' => 'approval_level', 'label' => 'Approval level'],
            ['name' => 'status', 'label' => 'Status'],
        ]);
    }

    public function claimApprovals(Request $request): View
    {
        return $this->records->screen($request, 'imprest.claim-approvals', 'imprest', 'claim_approval', 'approvalMatrices', [
            ['key' => 'matrices', 'op' => 'count'],
            ['key' => 'active', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Active'],
            ['key' => 'pending', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Pending'],
            ['key' => 'retired', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Retired'],
        ], [
            ['name' => 'matrix_code', 'label' => 'Matrix code', 'required' => true],
            ['name' => 'department', 'label' => 'Department'],
            ['name' => 'approver_role', 'label' => 'Approver role'],
            ['name' => 'threshold', 'label' => 'Threshold'],
            ['name' => 'status', 'label' => 'Status'],
        ]);
    }

    public function surrenderPermissions(Request $request): View
    {
        return $this->records->screen($request, 'imprest.surrender-permissions', 'imprest', 'surrender_permission', 'surrenderRules', [
            ['key' => 'rules', 'op' => 'count'],
            ['key' => 'active', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Active'],
            ['key' => 'expired', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Expired'],
            ['key' => 'draft', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Draft'],
        ], [
            ['name' => 'rule_code', 'label' => 'Rule code', 'required' => true],
            ['name' => 'department', 'label' => 'Department'],
            ['name' => 'surrender_window_days', 'label' => 'Surrender window (days)'],
            ['name' => 'status', 'label' => 'Status'],
        ]);
    }

    public function requisitions(Request $request): View
    {
        return $this->records->screen($request, 'imprest.requisitions', 'imprest', 'requisition', 'requisitions', [
            ['key' => 'open', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Open'],
            ['key' => 'approved', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Approved'],
            ['key' => 'rejected', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Rejected'],
            ['key' => 'totalAmount', 'op' => 'sum_money', 'field' => 'amount'],
        ], [
            ['name' => 'requisition_ref', 'label' => 'Requisition ref', 'required' => true],
            ['name' => 'staff_name', 'label' => 'Requester', 'required' => true],
            ['name' => 'department', 'label' => 'Department'],
            ['name' => 'amount', 'label' => 'Amount'],
            ['name' => 'purpose', 'label' => 'Purpose'],
            ['name' => 'status', 'label' => 'Status'],
        ]);
    }

    public function surrenders(Request $request): View
    {
        return $this->records->screen($request, 'imprest.surrenders', 'imprest', 'surrender', 'surrenders', [
            ['key' => 'pending', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Pending'],
            ['key' => 'cleared', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Cleared'],
            ['key' => 'overdue', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Overdue'],
            ['key' => 'total', 'op' => 'count'],
        ], [
            ['name' => 'surrender_ref', 'label' => 'Surrender ref', 'required' => true],
            ['name' => 'requisition_ref', 'label' => 'Linked requisition'],
            ['name' => 'staff_name', 'label' => 'Staff'],
            ['name' => 'amount', 'label' => 'Amount'],
            ['name' => 'status', 'label' => 'Status'],
        ]);
    }

    public function auditLedger(Request $request): View
    {
        return $this->records->screen($request, 'imprest.audit-ledger', 'imprest', 'audit_ledger', 'agingRecords', [
            ['key' => 'ledgerRows', 'op' => 'count'],
            ['key' => 'overdue', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Overdue'],
            ['key' => 'cleared', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Cleared'],
            ['key' => 'outstanding', 'op' => 'sum_money', 'field' => 'amount'],
        ], [
            ['name' => 'ledger_ref', 'label' => 'Ledger ref', 'required' => true],
            ['name' => 'staff_name', 'label' => 'Staff'],
            ['name' => 'amount', 'label' => 'Amount'],
            ['name' => 'aging_days', 'label' => 'Aging days'],
            ['name' => 'status', 'label' => 'Status'],
        ]);
    }
}
