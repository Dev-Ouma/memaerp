<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesCataloguePermission;
use App\Models\Course;
use App\Models\FeeFundingSource;
use App\Models\FeeInvoice;
use App\Models\FeePayment;
use App\Models\FeePaymentAccount;
use App\Models\FeePaymentType;
use App\Models\FeeStructure;
use App\Models\Student;
use App\Services\RegistrationFeesService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class FeesController extends Controller
{
    use AuthorizesCataloguePermission;

    public function __construct(
        private readonly RegistrationFeesService $fees,
    ) {}

    public function paymentAccounts(Request $request): View
    {
        $records = FeePaymentAccount::query()->withSum(
            ['payments as confirmed_revenue' => fn ($q) => $q->where('status', 'CONFIRMED')],
            'amount',
        )->latest()->get();

        $accounts = $records->map(fn (FeePaymentAccount $account): array => [
            'account_no' => $account->code,
            'name' => $account->name,
            'bank_name' => $account->bank_name ?? '—',
            'account_number' => $account->account_number ?? '—',
            'integration_type' => $account->integration_type ?? '—',
            'trimester_revenue' => $this->fees->money((float) ($account->confirmed_revenue ?? 0)),
            'status' => $account->status === 'ACTIVE' ? 'Active' : $account->status,
        ]);

        $stats = [
            'totalAccounts' => $records->count(),
            'mpesaBridgesActive' => $records->filter(
                fn (FeePaymentAccount $account): bool => str_contains(strtolower((string) $account->integration_type), 'm-pesa')
                    || str_contains(strtolower((string) $account->integration_type), 'mpesa')
            )->count(),
            'bankDirectIpn' => $records->filter(
                fn (FeePaymentAccount $account): bool => str_contains(strtolower((string) $account->integration_type), 'bank')
                    || str_contains(strtolower((string) $account->bank_name), 'bank')
            )->count(),
            'clearedBalance' => $this->fees->money((float) FeePayment::query()->where('status', 'CONFIRMED')->sum('amount')),
        ];

        return view('fees.payment-accounts', compact('stats', 'accounts'))->with('operationalCreate', [
            'title' => 'Add collection account',
            'hint' => 'Persists to fee_payment_accounts (domain table).',
            'action' => route('fees.accounts.store'),
            'fields' => [
                ['name' => 'code', 'label' => 'Account code', 'required' => true],
                ['name' => 'name', 'label' => 'Account name', 'required' => true],
                ['name' => 'bank_name', 'label' => 'Bank / channel'],
                ['name' => 'account_number', 'label' => 'Account / paybill number'],
                ['name' => 'integration_type', 'label' => 'Integration type (M-Pesa / Bank IPN)'],
                ['name' => 'status', 'label' => 'Status (ACTIVE/INACTIVE)'],
            ],
        ]);
    }

    public function storeAccount(Request $request): RedirectResponse
    {
        $this->authorizePermission($request, 'fees.manage');
        $data = $request->validate([
            'code' => ['required', 'string', 'max:40', 'unique:fee_payment_accounts,code'],
            'name' => ['required', 'string', 'max:255'],
            'bank_name' => ['nullable', 'string', 'max:120'],
            'account_number' => ['nullable', 'string', 'max:80'],
            'integration_type' => ['nullable', 'string', 'max:80'],
            'status' => ['nullable', 'string', Rule::in(['ACTIVE', 'INACTIVE', 'active', 'inactive'])],
        ]);
        $this->fees->createPaymentAccount($data);

        return back()->with('success', 'Collection account saved.');
    }

    public function paymentTypes(Request $request): View
    {
        $records = FeePaymentType::query()->latest()->get();
        $types = $records->map(fn (FeePaymentType $type): array => [
            'type_code' => $type->code,
            'name' => $type->name,
            'category' => $type->category ?? '—',
            'mandatory' => $type->mandatory ? 'Yes' : 'No',
            'ledger_allocation' => $type->ledger_allocation ?? '—',
            'refund_policy' => $type->refund_policy ?? '—',
            'status' => $type->status === 'ACTIVE' ? 'Active' : $type->status,
        ]);
        $stats = [
            'definedTypes' => $records->count(),
            'mandatoryPayments' => $records->where('mandatory', true)->count(),
            'optionalPayments' => $records->where('mandatory', false)->count(),
            'policyVersion' => $records->where('status', 'ACTIVE')->count(),
        ];

        return view('fees.payment-types', compact('stats', 'types'))->with('operationalCreate', [
            'title' => 'Add payment type',
            'hint' => 'Persists to fee_payment_types (domain table).',
            'action' => route('fees.types.store'),
            'fields' => [
                ['name' => 'code', 'label' => 'Type code', 'required' => true],
                ['name' => 'name', 'label' => 'Name', 'required' => true],
                ['name' => 'category', 'label' => 'Category'],
                ['name' => 'mandatory', 'label' => 'Mandatory (1/0)', 'type' => 'number'],
                ['name' => 'ledger_allocation', 'label' => 'Ledger allocation'],
                ['name' => 'refund_policy', 'label' => 'Refund policy'],
                ['name' => 'status', 'label' => 'Status (ACTIVE/INACTIVE)'],
            ],
        ]);
    }

    public function storeType(Request $request): RedirectResponse
    {
        $this->authorizePermission($request, 'fees.manage');
        $data = $request->validate([
            'code' => ['required', 'string', 'max:40', 'unique:fee_payment_types,code'],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:80'],
            'mandatory' => ['nullable'],
            'ledger_allocation' => ['nullable', 'string', 'max:120'],
            'refund_policy' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', Rule::in(['ACTIVE', 'INACTIVE', 'active', 'inactive'])],
        ]);
        FeePaymentType::query()->create([
            'code' => strtoupper(trim((string) $data['code'])),
            'name' => trim((string) $data['name']),
            'category' => $data['category'] ?? null,
            'mandatory' => ! array_key_exists('mandatory', $data)
                || in_array((string) ($data['mandatory'] ?? '1'), ['1', 'true', 'yes', 'on'], true),
            'ledger_allocation' => $data['ledger_allocation'] ?? null,
            'refund_policy' => $data['refund_policy'] ?? null,
            'status' => strtoupper((string) ($data['status'] ?? 'ACTIVE')),
        ]);

        return back()->with('success', 'Payment type saved.');
    }

    public function paymentSource(Request $request): View
    {
        $records = FeeFundingSource::query()->latest()->get();
        $sources = $records->map(fn (FeeFundingSource $source): array => [
            'source_code' => $source->code,
            'name' => $source->name,
            'description' => $source->description ?? '—',
            'allocation_rule' => $source->allocation_rule ?? '—',
            'candidates_count' => $source->candidates_count,
            'status' => $source->status === 'ACTIVE' ? 'Active' : $source->status,
        ]);
        $stats = [
            'fundingSources' => $records->count(),
            'helbDisbursements' => $records->filter(fn (FeeFundingSource $s): bool => str_contains(strtolower($s->name), 'helb'))->count(),
            'scholarshipsManaged' => $records->filter(fn (FeeFundingSource $s): bool => str_contains(strtolower($s->name), 'scholarship'))->count(),
            'corporateSponsors' => $records->filter(fn (FeeFundingSource $s): bool => str_contains(strtolower($s->name), 'corporate'))->count(),
        ];

        return view('fees.payment-source', compact('stats', 'sources'))->with('operationalCreate', [
            'title' => 'Add funding source',
            'hint' => 'Persists to fee_funding_sources (domain table).',
            'action' => route('fees.sources.store'),
            'fields' => [
                ['name' => 'code', 'label' => 'Source code', 'required' => true],
                ['name' => 'name', 'label' => 'Name', 'required' => true],
                ['name' => 'description', 'label' => 'Description'],
                ['name' => 'allocation_rule', 'label' => 'Allocation rule'],
                ['name' => 'candidates_count', 'label' => 'Candidates', 'type' => 'number'],
                ['name' => 'status', 'label' => 'Status (ACTIVE/INACTIVE)'],
            ],
        ]);
    }

    public function storeSource(Request $request): RedirectResponse
    {
        $this->authorizePermission($request, 'fees.manage');
        $data = $request->validate([
            'code' => ['required', 'string', 'max:40', 'unique:fee_funding_sources,code'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'allocation_rule' => ['nullable', 'string', 'max:255'],
            'candidates_count' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'string', Rule::in(['ACTIVE', 'INACTIVE', 'active', 'inactive'])],
        ]);
        FeeFundingSource::query()->create([
            'code' => strtoupper(trim((string) $data['code'])),
            'name' => trim((string) $data['name']),
            'description' => $data['description'] ?? null,
            'allocation_rule' => $data['allocation_rule'] ?? null,
            'candidates_count' => (int) ($data['candidates_count'] ?? 0),
            'status' => strtoupper((string) ($data['status'] ?? 'ACTIVE')),
        ]);

        return back()->with('success', 'Funding source saved.');
    }

    public function feeSetup(Request $request): View
    {
        $records = FeeStructure::query()->with('course')->latest()->get();
        $structures = $records->map(fn (FeeStructure $structure): array => [
            'structure_code' => $structure->code,
            'programme' => $structure->course?->name ?? $structure->title,
            'cohort' => $structure->cohort ?? '—',
            'tuition_fee' => $this->fees->money((float) $structure->tuition_amount, $structure->currency),
            'admin_fee' => $this->fees->money((float) $structure->admin_amount, $structure->currency),
            'total_per_trimester' => $this->fees->money($structure->totalAmount(), $structure->currency),
            'last_updated' => $structure->updated_at?->format('d M Y') ?? '—',
            'status' => $structure->status === 'ACTIVE' ? 'Active' : $structure->status,
        ]);
        $stats = [
            'activeStructures' => $records->where('status', 'ACTIVE')->count(),
            'highestTrimesterFee' => $this->fees->money((float) ($records->max(fn (FeeStructure $s): float => $s->totalAmount()) ?? 0)),
            'lowestTrimesterFee' => $this->fees->money((float) ($records->min(fn (FeeStructure $s): float => $s->totalAmount()) ?? 0)),
            'averageTuition' => $this->fees->money((float) ($records->avg(fn (FeeStructure $s): float => (float) $s->tuition_amount) ?? 0)),
        ];

        $courseOptions = Course::query()->orderBy('code')->get()
            ->mapWithKeys(fn (Course $course): array => [$course->id => $course->code.' — '.$course->name])
            ->all();

        return view('fees.fee-setup', compact('stats', 'structures'))->with('operationalCreate', [
            'title' => 'Configure fee structure',
            'hint' => 'Persists to fee_structures (domain table).',
            'action' => route('fees.structures.store'),
            'fields' => [
                ['name' => 'code', 'label' => 'Structure code', 'required' => true],
                ['name' => 'title', 'label' => 'Title', 'required' => true],
                ['name' => 'course_id', 'label' => 'Programme', 'type' => 'select', 'options' => $courseOptions],
                ['name' => 'cohort', 'label' => 'Cohort'],
                ['name' => 'tuition_amount', 'label' => 'Tuition amount', 'type' => 'number', 'required' => true],
                ['name' => 'admin_amount', 'label' => 'Admin amount', 'type' => 'number'],
                ['name' => 'status', 'label' => 'Status (ACTIVE/INACTIVE)'],
            ],
        ]);
    }

    public function storeStructure(Request $request): RedirectResponse
    {
        $this->authorizePermission($request, 'fees.manage');
        $data = $request->validate([
            'code' => ['required', 'string', 'max:40', 'unique:fee_structures,code'],
            'title' => ['required', 'string', 'max:255'],
            'course_id' => ['nullable', 'exists:courses,id'],
            'cohort' => ['nullable', 'string', 'max:80'],
            'tuition_amount' => ['required', 'numeric', 'min:0'],
            'admin_amount' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'string', Rule::in(['ACTIVE', 'INACTIVE', 'active', 'inactive'])],
        ]);
        $this->fees->createStructure($data);

        return back()->with('success', 'Fee structure saved.');
    }

    public function feePayables(Request $request): View
    {
        $invoices = FeeInvoice::query()->with(['student.user', 'student.course', 'structure'])->latest()->get();
        $payables = $invoices->map(function (FeeInvoice $invoice): array {
            $cleared = (float) $invoice->amount_invoiced > 0
                ? round(((float) $invoice->amount_paid / (float) $invoice->amount_invoiced) * 100, 1)
                : 0;

            return [
                'payable_ref' => $invoice->invoice_no,
                'student_name' => $invoice->student?->user?->name ?? '—',
                'reg_no' => $invoice->student?->admission_number ?? '—',
                'programme' => $invoice->student?->course?->name ?? ($invoice->structure?->title ?? '—'),
                'invoiced_amount' => $this->fees->money((float) $invoice->amount_invoiced),
                'amount_paid' => $this->fees->money((float) $invoice->amount_paid),
                'outstanding_balance' => $this->fees->money($invoice->outstanding()),
                'clearance_status' => $cleared.'% cleared',
                'status' => match ($invoice->status) {
                    'SETTLED' => 'Settled / Cleared',
                    'PARTIAL' => 'Partially Paid',
                    'CANCELLED' => 'Cancelled',
                    default => 'Open / Outstanding',
                },
            ];
        });
        $invoiced = (float) $invoices->sum('amount_invoiced');
        $collected = (float) $invoices->sum('amount_paid');
        $stats = [
            'totalInvoiced' => $this->fees->money($invoiced),
            'totalCollected' => $this->fees->money($collected),
            'outstandingArrears' => $this->fees->money(max(0, $invoiced - $collected)),
            'collectionRate' => ($invoiced > 0 ? round(($collected / $invoiced) * 100, 1) : 0).'%',
        ];

        $invoiceOptions = $invoices->whereIn('status', ['OPEN', 'PARTIAL'])->mapWithKeys(
            fn (FeeInvoice $invoice): array => [
                $invoice->id => $invoice->invoice_no.' — '.($invoice->student?->admission_number ?? 'student').' (outstanding '.$this->fees->money($invoice->outstanding()).')',
            ]
        )->all();
        $accountOptions = FeePaymentAccount::query()->where('status', 'ACTIVE')->orderBy('code')->get()
            ->mapWithKeys(fn (FeePaymentAccount $account): array => [
                $account->id => $account->code.' — '.$account->name,
            ])->all();

        return view('fees.fee-payables', compact('stats', 'payables'))->with('operationalCreate', [
            'title' => 'Record tuition payment',
            'hint' => 'Creates fee_payments and updates the invoice ledger.',
            'action' => route('fees.payments.store'),
            'fields' => [
                ['name' => 'fee_invoice_id', 'label' => 'Invoice', 'type' => 'select', 'required' => true, 'options' => $invoiceOptions],
                ['name' => 'payment_account_id', 'label' => 'Collection account', 'type' => 'select', 'options' => $accountOptions],
                ['name' => 'amount', 'label' => 'Amount', 'type' => 'number', 'required' => true],
                ['name' => 'method', 'label' => 'Method (MPESA/BANK/CASH)', 'required' => true],
                ['name' => 'transaction_ref', 'label' => 'Transaction ref'],
                ['name' => 'confirm', 'label' => 'Confirm now? (1=yes, 0=pending)', 'type' => 'number'],
            ],
        ]);
    }

    public function storePayment(Request $request): RedirectResponse
    {
        $this->authorizePermission($request, 'fees.manage');
        $data = $request->validate([
            'fee_invoice_id' => ['required', 'exists:fee_invoices,id'],
            'payment_account_id' => ['nullable', 'exists:fee_payment_accounts,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['required', 'string', 'max:40'],
            'transaction_ref' => ['nullable', 'string', 'max:80'],
            'confirm' => ['nullable'],
        ]);
        $invoice = FeeInvoice::query()->findOrFail((int) $data['fee_invoice_id']);
        $confirm = ! array_key_exists('confirm', $data)
            || in_array((string) ($data['confirm'] ?? '1'), ['1', 'true', 'yes', 'on'], true);
        $this->fees->recordPayment(
            $invoice,
            (float) $data['amount'],
            (string) $data['method'],
            $data['transaction_ref'] ?? null,
            $request->user(),
            $confirm,
            isset($data['payment_account_id']) ? (int) $data['payment_account_id'] : null,
        );

        return back()->with('success', 'Fee payment recorded.');
    }

    public function pendingPayments(Request $request): View
    {
        $pendings = FeePayment::query()
            ->with(['invoice.student.user'])
            ->where('status', 'PENDING')
            ->latest()
            ->get()
            ->map(fn (FeePayment $payment): array => [
                'payment_ref' => $payment->payment_ref,
                'student_name' => $payment->invoice?->student?->user?->name ?? '—',
                'reg_no' => $payment->invoice?->student?->admission_number ?? '—',
                'payment_method' => $payment->method,
                'transaction_ref' => $payment->transaction_ref ?? '—',
                'amount' => $this->fees->money((float) $payment->amount),
                'upload_timestamp' => $payment->created_at?->format('d M Y H:i') ?? '—',
                'verdict' => 'Awaiting finance confirmation',
                'status' => 'Pending audit',
                'id' => $payment->id,
            ]);
        $stats = [
            'unconfirmedTransactions' => $pendings->count(),
            'bankSlipUploads' => $pendings->filter(fn (array $row): bool => str_contains(strtoupper($row['payment_method']), 'BANK'))->count(),
            'mpesaDiscrepancies' => $pendings->filter(fn (array $row): bool => str_contains(strtoupper($row['payment_method']), 'MPESA'))->count(),
            'totalAwaitingAudit' => $this->fees->money((float) FeePayment::query()->where('status', 'PENDING')->sum('amount')),
        ];

        return view('fees.pending-payments', compact('stats', 'pendings'));
    }

    public function confirmPayment(Request $request, FeePayment $payment): RedirectResponse
    {
        $this->authorizePermission($request, 'fees.manage');
        $this->fees->confirmPayment($payment, $request->user());

        return back()->with('success', 'Payment confirmed and receipt issued.');
    }

    public function paymentReceipt(Request $request): View
    {
        $student = Student::query()->with(['user', 'course'])
            ->whereHas('feeInvoices')
            ->latest()
            ->first()
            ?? Student::query()->with(['user', 'course'])->latest()->first();

        $invoices = $student
            ? FeeInvoice::query()->where('student_id', $student->id)->get()
            : collect();
        $billed = (float) $invoices->sum('amount_invoiced');
        $paid = (float) $invoices->sum('amount_paid');
        $studentInfo = [
            'name' => $student?->user?->name ?? 'No enrolled student',
            'reg_no' => $student?->admission_number ?? '—',
            'programme' => $student?->course?->name ?? '—',
            'school' => $student?->course?->name ?? '—',
            'cohort' => '—',
            'total_billed_trimester' => $this->fees->money($billed),
            'total_cleared_trimester' => $this->fees->money($paid),
            'balance_remaining' => $this->fees->money(max(0, $billed - $paid)),
        ];

        $receipts = FeePayment::query()
            ->with(['invoice.student'])
            ->where('status', 'CONFIRMED')
            ->when($student, fn ($q) => $q->whereHas('invoice', fn ($iq) => $iq->where('student_id', $student->id)))
            ->latest('paid_at')
            ->get()
            ->map(fn (FeePayment $payment): array => [
                'receipt_no' => $payment->receipt_no ?? $payment->payment_ref,
                'amount_paid' => $this->fees->money((float) $payment->amount),
                'payment_mode' => $payment->method,
                'bank_transaction_id' => $payment->transaction_ref ?? '—',
                'timestamp' => $payment->paid_at?->format('d M Y H:i') ?? '—',
                'status' => 'Issued',
            ]);

        $stats = [
            'receiptsIssued' => FeePayment::query()->where('status', 'CONFIRMED')->count(),
            'receiptsIssuedToday' => FeePayment::query()->where('status', 'CONFIRMED')->whereDate('paid_at', today())->count(),
            'receiptAccuracy' => 'Database-backed',
            'auditLogIntegrity' => FeePayment::query()->count().' ledger rows',
        ];

        return view('fees.payment-receipt', compact('stats', 'receipts', 'studentInfo'));
    }
}
