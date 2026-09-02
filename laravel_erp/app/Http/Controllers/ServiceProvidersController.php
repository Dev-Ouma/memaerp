<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

final class ServiceProvidersController extends Controller
{
    /**
     * Helper to load stats and rows for Service Providers submodules
     */
    private function getCommonData(): array
    {
        return [
            'stats' => [
                'totalProviders' => 42,
                'pendingApprovals' => 3,
                'billsUnpaid' => 14,
                'totalOutstanding' => 'KES 4,850,200',
            ],
            'providers' => [
                [
                    'id' => 1,
                    'provider_code' => 'PROV-SAFA-01',
                    'name' => 'Safaricom PLC',
                    'group' => 'Telecommunications & Internet ISP',
                    'contact' => 'biz-ops@safaricom.co.ke',
                    'outstanding_bills' => 'KES 1,200,000',
                    'status' => 'Active Supplier',
                ],
                [
                    'id' => 2,
                    'provider_code' => 'PROV-KPLC-02',
                    'name' => 'Kenya Power & Lighting Company (KPLC)',
                    'group' => 'Utilities & Energy Infrastructure',
                    'contact' => 'billing@kplc.co.ke',
                    'outstanding_bills' => 'KES 650,000',
                    'status' => 'Active Supplier',
                ],
                [
                    'id' => 3,
                    'provider_code' => 'PROV-TEXT-03',
                    'name' => 'MEMA Textbooks & Stationers Ltd',
                    'group' => 'Academic Publishing & Supplies',
                    'contact' => 'supplies@memabooks.co.ke',
                    'outstanding_bills' => 'KES 420,000',
                    'status' => 'Pending Re-Audit',
                ],
            ],
        ];
    }

    /**
     * 1. Taxes Setup
     */
    public function taxes(Request $request): View
    {
        $stats = ['taxSchemes' => 3, 'withholdingTax' => '5.0%', 'vatRate' => '16.0%', 'lastAudited' => 'Active'];
        $taxes = [
            ['code' => 'VAT-16', 'name' => 'Standard Value Added Tax', 'rate' => '16.0%', 'type' => 'Output Tax', 'status' => 'Operational'],
            ['code' => 'WHT-5', 'name' => 'Withholding Tax on Professional Services', 'rate' => '5.0%', 'type' => 'Withholding Tax', 'status' => 'Operational'],
        ];

        return view('service-providers.taxes', compact('stats', 'taxes'));
    }

    /**
     * 2. Items
     */
    public function items(Request $request): View
    {
        $stats = ['inventoryItems' => 142, 'reorderLevelAlerts' => 5, 'activeCategoryCount' => 8];
        $items = [
            ['code' => 'ITEM-DESK-01', 'name' => 'Double Pedestal Office Oak Desk', 'category' => 'Furniture & Equipment', 'unit_cost' => 'KES 15,000', 'stock' => 45],
            ['code' => 'ITEM-PPR-A4', 'name' => 'Executive White A4 Printing Paper Reams', 'category' => 'Stationery Supplies', 'unit_cost' => 'KES 650', 'stock' => 240],
        ];

        return view('service-providers.items', compact('stats', 'items'));
    }

    /**
     * 3. Provider Groups
     */
    public function providerGroups(Request $request): View
    {
        $stats = ['activeGroups' => 6, 'capacityCap' => 'Unlimited', 'slaVetting' => 'Mandatory'];
        $groups = [
            ['code' => 'GRP-UTIL', 'name' => 'Utilities & Energy Infrastructure', 'desc' => 'Power, water, waste disposal corporate accounts', 'status' => 'Active Group'],
            ['code' => 'GRP-IT', 'name' => 'IT Consultants & Internet Providers', 'desc' => 'Software licensing, broadband connections, support dockets', 'status' => 'Active Group'],
        ];

        return view('service-providers.provider-groups', compact('stats', 'groups'));
    }

    /**
     * 4. Providers
     */
    public function providers(Request $request): View
    {
        $data = $this->getCommonData();
        $stats = $data['stats'];
        $providers = $data['providers'];

        return view('service-providers.providers', compact('stats', 'providers'));
    }

    /**
     * 5. Vendor Registration Approval
     */
    public function vendorApproval(Request $request): View
    {
        $stats = ['awaitingVetting' => 3, 'approvedVendors' => 42, 'rejectedVendors' => 2];
        $approvals = [
            ['ref' => 'VND-APP-09', 'name' => 'Apex Office Supplies Ltd', 'kra_pin' => 'P051234567A', 'compliance_doc' => 'Tax Compliance Clear', 'status' => 'Awaiting Vetting'],
        ];

        return view('service-providers.vendor-approval', compact('stats', 'approvals'));
    }

    /**
     * 6. Supplier Invoice Permission
     */
    public function invoicePermissions(Request $request): View
    {
        $stats = ['authorizedStaff' => 8, 'policyLevel' => 'Strict Dual Control', 'lastAudited' => 'Compliant'];

        return view('service-providers.invoice-permissions', compact('stats'));
    }

    /**
     * 7. Bills
     */
    public function bills(Request $request): View
    {
        $stats = ['totalBills' => 148, 'unpaidBills' => 14, 'paidBills' => 134, 'totalBillsVolume' => 'KES 12,850,000'];
        $bills = [
            ['ref' => 'BILL-2027-012', 'vendor' => 'Safaricom PLC', 'amount' => 'KES 420,000', 'due_date' => '15 Mar 2027', 'status' => 'Unpaid / Awaiting Approval'],
        ];

        return view('service-providers.bills', compact('stats', 'bills'));
    }

    /**
     * 8. Supplier Payment Permission
     */
    public function paymentPermissions(Request $request): View
    {
        $stats = ['authorizedSignatures' => 4, 'tierLimit' => 'KES 500,000 limit per HOD', 'compliance' => 'RBAC Enabled'];

        return view('service-providers.payment-permissions', compact('stats'));
    }

    /**
     * 9. Payments
     */
    public function payments(Request $request): View
    {
        $stats = ['totalPaymentsVolume' => 'KES 8,420,000', 'paymentsToday' => 2, 'channelsActive' => 3];
        $payments = [
            ['ref' => 'PAY-2027-891', 'vendor' => 'Safaricom PLC', 'amount' => 'KES 1,200,000', 'mode' => 'EFT Bank Transfer', 'date' => '28 Aug 2026', 'status' => 'Settled & Reconciled'],
        ];

        return view('service-providers.payments', compact('stats', 'payments'));
    }

    /**
     * 10. Debit Notes
     */
    public function debitNotes(Request $request): View
    {
        $stats = ['activeDebitNotes' => 4, 'totalReductionValue' => 'KES 180,000', 'reconciliation' => 'Audit Cleared'];
        $debitNotes = [
            ['ref' => 'DBN-2027-01', 'vendor' => 'MEMA Textbooks & Stationers Ltd', 'amount' => 'KES 45,000', 'reason' => 'Damaged textbooks returned', 'status' => 'Ledger Applied'],
        ];

        return view('service-providers.debit-notes', compact('stats', 'debitNotes'));
    }

    /**
     * 11. Credit Notes
     */
    public function creditNotes(Request $request): View
    {
        $stats = ['activeCreditNotes' => 2, 'totalCreditValue' => 'KES 90,000', 'reconciliation' => 'Audit Cleared'];
        $creditNotes = [
            ['ref' => 'CRN-2027-04', 'vendor' => 'Safaricom PLC', 'amount' => 'KES 50,000', 'reason' => 'Broadband downtime rebate credit', 'status' => 'Ledger Applied'],
        ];

        return view('service-providers.credit-notes', compact('stats', 'creditNotes'));
    }
}
