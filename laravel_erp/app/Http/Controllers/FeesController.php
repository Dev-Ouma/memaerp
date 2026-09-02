<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

final class FeesController extends Controller
{
    /**
     * 1. Payment Accounts
     */
    public function paymentAccounts(Request $request): View
    {
        $stats = [
            'totalAccounts' => 5,
            'mpesaBridgesActive' => 2,
            'bankDirectIpn' => 3,
            'clearedBalance' => 'KES 42,850,200',
        ];

        $accounts = [
            [
                'id' => 1,
                'account_no' => 'MEMA-ACC-01',
                'name' => 'Equity Bank Tuition Collection Account',
                'bank_name' => 'Equity Bank Kenya',
                'account_number' => '121027891012',
                'integration_type' => 'Direct Bank IPN Integration',
                'trimester_revenue' => 'KES 24,150,000',
                'status' => 'Operational & Connected',
            ],
            [
                'id' => 2,
                'account_no' => 'MEMA-ACC-02',
                'name' => 'KCB Main Operating Account',
                'bank_name' => 'Kenya Commercial Bank',
                'account_number' => '110278901234',
                'integration_type' => 'Direct Bank IPN Integration',
                'trimester_revenue' => 'KES 12,800,000',
                'status' => 'Operational & Connected',
            ],
            [
                'id' => 3,
                'account_no' => 'MEMA-MPESA-03',
                'name' => 'M-Pesa Daraja 2.0 tuition Paybill',
                'bank_name' => 'Safaricom M-Pesa',
                'account_number' => '8234500 (Tuition Paybill)',
                'integration_type' => 'Real-Time C2B API Instant Push',
                'trimester_revenue' => 'KES 5,900,200',
                'status' => 'Operational & Connected',
            ],
        ];

        return view('fees.payment-accounts', compact('stats', 'accounts'));
    }

    /**
     * 2. Payment Types
     */
    public function paymentTypes(Request $request): View
    {
        $stats = [
            'definedTypes' => 12,
            'mandatoryPayments' => 4,
            'optionalPayments' => 8,
            'policyVersion' => 'Fees & Charges Guide 2026/2027',
        ];

        $types = [
            [
                'id' => 1,
                'type_code' => 'PAY-TUI',
                'name' => 'Core Tuition Fees',
                'category' => 'Academic Fees',
                'mandatory' => 'Mandatory / Core',
                'ledger_allocation' => 'Tuition Revenue Ledger',
                'refund_policy' => 'Refundable under Dean Approved deferment',
                'status' => 'Active Type',
            ],
            [
                'id' => 2,
                'type_code' => 'PAY-LIB',
                'name' => 'Trimester Library Access Fee',
                'category' => 'Administrative Fees',
                'mandatory' => 'Mandatory / Core',
                'ledger_allocation' => 'Library Support Ledger',
                'refund_policy' => 'Non-Refundable',
                'status' => 'Active Type',
            ],
            [
                'id' => 3,
                'type_code' => 'PAY-SUPP',
                'name' => 'Supplementary Examination Fee',
                'category' => 'Special Assessments',
                'mandatory' => 'On-Demand / Contingent',
                'ledger_allocation' => 'Exam Administration Ledger',
                'refund_policy' => 'Non-Refundable',
                'status' => 'Active Type',
            ],
        ];

        return view('fees.payment-types', compact('stats', 'types'));
    }

    /**
     * 3. Payment Source
     */
    public function paymentSource(Request $request): View
    {
        $stats = [
            'fundingSources' => 6,
            'helbDisbursements' => 'KES 18,450,000',
            'scholarshipsManaged' => 142,
            'corporateSponsors' => 8,
        ];

        $sources = [
            [
                'id' => 1,
                'source_code' => 'SRC-SELF',
                'name' => 'Self-Sponsored / Private Funding',
                'description' => 'Tuition paid directly by student guardian or employer',
                'allocation_rule' => 'Direct Invoice Clearing',
                'candidates_count' => 12450,
                'status' => 'Active Source',
            ],
            [
                'id' => 2,
                'source_code' => 'SRC-HELB',
                'name' => 'HELB / Government Scholarship Scheme',
                'description' => 'Higher Education Loans Board direct disbursement',
                'allocation_rule' => 'Batch Smart Split Allocation',
                'candidates_count' => 1850,
                'status' => 'Active Source',
            ],
            [
                'id' => 3,
                'source_code' => 'SRC-CDF',
                'name' => 'Constituency Development Fund (CDF) Bursary',
                'description' => 'County & Constituency bursary allocations',
                'allocation_rule' => 'Voucher Code Clearance Verification',
                'candidates_count' => 450,
                'status' => 'Active Source',
            ],
        ];

        return view('fees.payment-source', compact('stats', 'sources'));
    }

    /**
     * 4. Fee Setup
     */
    public function feeSetup(Request $request): View
    {
        $stats = [
            'activeStructures' => 18,
            'highestTrimesterFee' => 'KES 90,000 (PhD. CS)',
            'lowestTrimesterFee' => 'KES 45,005 (BSc. CS)',
            'averageTuition' => 'KES 54,000',
        ];

        $structures = [
            [
                'id' => 1,
                'structure_code' => 'FEE-BCS-2024',
                'programme' => 'Bachelor of Science in Computer Science',
                'cohort' => 'COH-2024-SEP-MAIN',
                'tuition_fee' => 'KES 45,005',
                'admin_fee' => 'KES 8,000 (Library & Amenities)',
                'total_per_trimester' => 'KES 53,005',
                'last_updated' => '12 Sep 2024',
                'status' => 'Approved & Active',
            ],
            [
                'id' => 2,
                'structure_code' => 'FEE-PHD-2024',
                'programme' => 'PhD in Computer Science',
                'cohort' => 'COH-2024-SEP-MAIN',
                'tuition_fee' => 'KES 90,000',
                'admin_fee' => 'KES 12,000 (Labs & Thesis Review)',
                'total_per_trimester' => 'KES 102,000',
                'last_updated' => '12 Sep 2024',
                'status' => 'Approved & Active',
            ],
            [
                'id' => 3,
                'structure_code' => 'FEE-BIT-2025',
                'programme' => 'BSc. Information Technology',
                'cohort' => 'COH-2025-JAN-INT',
                'tuition_fee' => 'KES 45,005',
                'admin_fee' => 'KES 8,000',
                'total_per_trimester' => 'KES 53,005',
                'last_updated' => '05 Jan 2025',
                'status' => 'Approved & Active',
            ],
        ];

        return view('fees.fee-setup', compact('stats', 'structures'));
    }

    /**
     * 5. Fee Payables
     */
    public function feePayables(Request $request): View
    {
        $stats = [
            'totalInvoiced' => 'KES 748,500,200',
            'totalCollected' => 'KES 682,450,200',
            'outstandingArrears' => 'KES 66,050,000',
            'collectionRate' => '91.2%',
        ];

        $payables = [
            [
                'id' => 1,
                'payable_ref' => 'INV-2027-T2-0912',
                'student_name' => 'Brenda Chepkoech',
                'reg_no' => 'MEMA/BCS/2024/0912',
                'programme' => 'BSc. Computer Science',
                'invoiced_amount' => 'KES 53,005',
                'amount_paid' => 'KES 53,005',
                'outstanding_balance' => 'KES 0 (Cleared)',
                'clearance_status' => 'Fully Cleared / 100%',
                'status' => 'Invoice Settled',
            ],
            [
                'id' => 2,
                'payable_ref' => 'INV-2027-T2-1104',
                'student_name' => 'Emmanuel Kiprono Mutai',
                'reg_no' => 'MEMA/BIT/2023/1104',
                'programme' => 'BSc. Information Technology',
                'invoiced_amount' => 'KES 53,005',
                'amount_paid' => 'KES 45,000',
                'outstanding_balance' => 'KES 8,005 (Arrears)',
                'clearance_status' => 'Partial Clearance / 84.8%',
                'status' => 'Partially Paid',
            ],
            [
                'id' => 3,
                'payable_ref' => 'INV-2027-T2-0744',
                'student_name' => 'Kelvin Mwenda Gitonga',
                'reg_no' => 'MEMA/BSC/2023/0744',
                'programme' => 'BSc. Data Analytics',
                'invoiced_amount' => 'KES 53,005',
                'amount_paid' => 'KES 0',
                'outstanding_balance' => 'KES 53,005 (Arrears)',
                'clearance_status' => '0% Clearance (Deficit)',
                'status' => 'Defaulter / Arrears',
            ],
        ];

        return view('fees.fee-payables', compact('stats', 'payables'));
    }

    /**
     * 6. Pending Payment Confirmation
     */
    public function pendingPayments(Request $request): View
    {
        $stats = [
            'unconfirmedTransactions' => 34,
            'bankSlipUploads' => 18,
            'mpesaDiscrepancies' => 16,
            'totalAwaitingAudit' => 'KES 1,420,000',
        ];

        $pendings = [
            [
                'id' => 1,
                'payment_ref' => 'PND-2027-0112',
                'student_name' => 'Brenda Chepkoech',
                'reg_no' => 'MEMA/BCS/2024/0912',
                'payment_method' => 'Bank Slip Upload (Equity Bank)',
                'transaction_ref' => 'TRX-EQY-891044',
                'amount' => 'KES 53,005',
                'upload_timestamp' => '28-08-2026 14:20',
                'verdict' => 'Awaiting Bank Statement Audit Match',
                'status' => 'Pending Audit',
            ],
            [
                'id' => 2,
                'payment_ref' => 'PND-2027-0113',
                'student_name' => 'Emmanuel Kiprono Mutai',
                'reg_no' => 'MEMA/BIT/2023/1104',
                'payment_method' => 'M-Pesa Discrepancy (C2B)',
                'transaction_ref' => 'QRT8913B92',
                'amount' => 'KES 45,000',
                'upload_timestamp' => '29-08-2026 06:10',
                'verdict' => 'Amount Mismatch / Re-Keyed by Student',
                'status' => 'Awaiting Registry Vetting',
            ],
        ];

        return view('fees.pending-payments', compact('stats', 'pendings'));
    }

    /**
     * 7. Payment Receipt
     */
    public function paymentReceipt(Request $request): View
    {
        $stats = [
            'receiptsIssued' => 18450,
            'receiptsIssuedToday' => 112,
            'receiptAccuracy' => '100% System Checked',
            'auditLogIntegrity' => 'Immutable Cryptographic Ledger',
        ];

        $studentInfo = [
            'name' => 'Brenda Chepkoech',
            'reg_no' => 'MEMA/BCS/2024/0912',
            'programme' => 'Bachelor of Science in Computer Science',
            'school' => 'School of Science and Technology',
            'cohort' => 'COH-2024-SEP-MAIN',
            'total_billed_trimester' => 'KES 53,005',
            'total_cleared_trimester' => 'KES 53,005',
            'balance_remaining' => 'KES 0 (Fully Cleared)',
        ];

        $receipts = [
            [
                'id' => 1,
                'receipt_no' => 'REC-2027-0912',
                'amount_paid' => 'KES 53,005',
                'payment_mode' => 'Equity Bank Direct IPN',
                'bank_transaction_id' => 'TRX-EQY-891044',
                'timestamp' => '28-08-2026 15:30',
                'status' => 'Receipt Printed / Signed',
            ],
        ];

        return view('fees.payment-receipt', compact('stats', 'studentInfo', 'receipts'));
    }
}
