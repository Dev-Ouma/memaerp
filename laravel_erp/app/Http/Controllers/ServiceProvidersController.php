<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\OperationalRecordService;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ServiceProvidersController extends Controller
{
    public function __construct(private readonly OperationalRecordService $records) {}

    public function taxes(Request $request): View
    {
        return $this->records->screen($request, 'service-providers.taxes', 'service-providers', 'tax', 'taxes', [
            ['key' => 'taxCodes', 'op' => 'count'],
            ['key' => 'active', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Active'],
            ['key' => 'inactive', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Inactive'],
            ['key' => 'vat', 'op' => 'count_match', 'field' => 'name', 'needle' => 'VAT'],
        ], [
            ['name' => 'tax_code', 'label' => 'Tax code', 'required' => true],
            ['name' => 'name', 'label' => 'Name', 'required' => true],
            ['name' => 'rate', 'label' => 'Rate %'],
            ['name' => 'status', 'label' => 'Status'],
        ]);
    }

    public function items(Request $request): View
    {
        return $this->records->screen($request, 'service-providers.items', 'service-providers', 'item', 'items', [
            ['key' => 'catalogItems', 'op' => 'count'],
            ['key' => 'active', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Active'],
            ['key' => 'discontinued', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Discontinued'],
            ['key' => 'services', 'op' => 'count_match', 'field' => 'item_type', 'needle' => 'Service'],
        ], [
            ['name' => 'item_code', 'label' => 'Item code', 'required' => true],
            ['name' => 'name', 'label' => 'Name', 'required' => true],
            ['name' => 'item_type', 'label' => 'Type'],
            ['name' => 'unit_price', 'label' => 'Unit price'],
            ['name' => 'status', 'label' => 'Status'],
        ]);
    }

    public function providerGroups(Request $request): View
    {
        return $this->records->screen($request, 'service-providers.provider-groups', 'service-providers', 'provider_group', 'groups', [
            ['key' => 'groups', 'op' => 'count'],
            ['key' => 'active', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Active'],
            ['key' => 'archived', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Archived'],
            ['key' => 'strategic', 'op' => 'count_match', 'field' => 'priority', 'needle' => 'Strategic'],
        ], [
            ['name' => 'group_code', 'label' => 'Group code', 'required' => true],
            ['name' => 'name', 'label' => 'Name', 'required' => true],
            ['name' => 'priority', 'label' => 'Priority'],
            ['name' => 'status', 'label' => 'Status'],
        ]);
    }

    public function providers(Request $request): View
    {
        return $this->records->screen($request, 'service-providers.providers', 'service-providers', 'provider', 'providers', [
            ['key' => 'totalProviders', 'op' => 'count'],
            ['key' => 'pendingApprovals', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Pending'],
            ['key' => 'billsUnpaid', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Outstanding'],
            ['key' => 'totalOutstanding', 'op' => 'sum_money', 'field' => 'outstanding_bills'],
        ], [
            ['name' => 'provider_code', 'label' => 'Provider code', 'required' => true],
            ['name' => 'name', 'label' => 'Name', 'required' => true],
            ['name' => 'group', 'label' => 'Group'],
            ['name' => 'contact', 'label' => 'Contact'],
            ['name' => 'outstanding_bills', 'label' => 'Outstanding bills'],
            ['name' => 'status', 'label' => 'Status'],
        ]);
    }

    public function vendorApproval(Request $request): View
    {
        return $this->records->screen($request, 'service-providers.vendor-approval', 'service-providers', 'vendor_approval', 'approvals', [
            ['key' => 'pending', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Pending'],
            ['key' => 'approved', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Approved'],
            ['key' => 'rejected', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Rejected'],
            ['key' => 'total', 'op' => 'count'],
        ], [
            ['name' => 'provider_code', 'label' => 'Provider code', 'required' => true],
            ['name' => 'name', 'label' => 'Vendor name', 'required' => true],
            ['name' => 'requested_by', 'label' => 'Requested by'],
            ['name' => 'status', 'label' => 'Status'],
        ]);
    }

    public function invoicePermissions(Request $request): View
    {
        return $this->records->screen($request, 'service-providers.invoice-permissions', 'service-providers', 'invoice_permission', 'rows', [
            ['key' => 'policyLevel', 'op' => 'count'],
            ['key' => 'lastAudited', 'op' => 'count'],
            ['key' => 'active', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Active'],
            ['key' => 'totalPermissions', 'op' => 'count'],
        ], [
            ['name' => 'staff_name', 'label' => 'Staff', 'required' => true],
            ['name' => 'department', 'label' => 'Department'],
            ['name' => 'limit_amount', 'label' => 'Invoice limit'],
            ['name' => 'policy_level', 'label' => 'Policy level'],
            ['name' => 'last_audited', 'label' => 'Last audited'],
            ['name' => 'status', 'label' => 'Status'],
        ], [
            'stats' => [
                'policyLevel' => 'Database-governed invoice upload policy',
                'lastAudited' => 'Derived from module_records (no mock timestamps)',
            ],
        ]);
    }

    public function bills(Request $request): View
    {
        return $this->records->screen($request, 'service-providers.bills', 'service-providers', 'bill', 'bills', [
            ['key' => 'unpaid', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Unpaid'],
            ['key' => 'paid', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Paid'],
            ['key' => 'overdue', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Overdue'],
            ['key' => 'totalAmount', 'op' => 'sum_money', 'field' => 'amount'],
        ], [
            ['name' => 'bill_ref', 'label' => 'Bill ref', 'required' => true],
            ['name' => 'provider_name', 'label' => 'Provider', 'required' => true],
            ['name' => 'amount', 'label' => 'Amount'],
            ['name' => 'due_date', 'label' => 'Due date', 'type' => 'date'],
            ['name' => 'status', 'label' => 'Status'],
        ]);
    }

    public function paymentPermissions(Request $request): View
    {
        return $this->records->screen($request, 'service-providers.payment-permissions', 'service-providers', 'payment_permission', 'rows', [
            ['key' => 'tierLimit', 'op' => 'count'],
            ['key' => 'compliance', 'op' => 'count'],
            ['key' => 'active', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Active'],
            ['key' => 'totalPermissions', 'op' => 'count'],
        ], [
            ['name' => 'staff_name', 'label' => 'Staff', 'required' => true],
            ['name' => 'department', 'label' => 'Department'],
            ['name' => 'limit_amount', 'label' => 'Payment limit'],
            ['name' => 'status', 'label' => 'Status'],
        ], [
            'stats' => [
                'tierLimit' => 'Limits stored per permission row in module_records',
                'compliance' => 'Compliance derived from live permission rows',
            ],
        ]);
    }

    public function payments(Request $request): View
    {
        return $this->records->screen($request, 'service-providers.payments', 'service-providers', 'payment', 'payments', [
            ['key' => 'paid', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Paid'],
            ['key' => 'pending', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Pending'],
            ['key' => 'failed', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Failed'],
            ['key' => 'totalAmount', 'op' => 'sum_money', 'field' => 'amount'],
        ], [
            ['name' => 'payment_ref', 'label' => 'Payment ref', 'required' => true],
            ['name' => 'provider_name', 'label' => 'Provider', 'required' => true],
            ['name' => 'amount', 'label' => 'Amount'],
            ['name' => 'payment_date', 'label' => 'Payment date', 'type' => 'date'],
            ['name' => 'status', 'label' => 'Status'],
        ]);
    }

    public function debitNotes(Request $request): View
    {
        return $this->records->screen($request, 'service-providers.debit-notes', 'service-providers', 'debit_note', 'debitNotes', [
            ['key' => 'open', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Open'],
            ['key' => 'applied', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Applied'],
            ['key' => 'void', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Void'],
            ['key' => 'totalAmount', 'op' => 'sum_money', 'field' => 'amount'],
        ], [
            ['name' => 'note_ref', 'label' => 'Note ref', 'required' => true],
            ['name' => 'provider_name', 'label' => 'Provider', 'required' => true],
            ['name' => 'amount', 'label' => 'Amount'],
            ['name' => 'reason', 'label' => 'Reason'],
            ['name' => 'status', 'label' => 'Status'],
        ]);
    }

    public function creditNotes(Request $request): View
    {
        return $this->records->screen($request, 'service-providers.credit-notes', 'service-providers', 'credit_note', 'creditNotes', [
            ['key' => 'open', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Open'],
            ['key' => 'applied', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Applied'],
            ['key' => 'void', 'op' => 'count_match', 'field' => 'status', 'needle' => 'Void'],
            ['key' => 'totalAmount', 'op' => 'sum_money', 'field' => 'amount'],
        ], [
            ['name' => 'note_ref', 'label' => 'Note ref', 'required' => true],
            ['name' => 'provider_name', 'label' => 'Provider', 'required' => true],
            ['name' => 'amount', 'label' => 'Amount'],
            ['name' => 'reason', 'label' => 'Reason'],
            ['name' => 'status', 'label' => 'Status'],
        ]);
    }
}
