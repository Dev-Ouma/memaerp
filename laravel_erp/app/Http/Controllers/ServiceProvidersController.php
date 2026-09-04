<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesCataloguePermission;
use App\Models\SpBill;
use App\Models\SpCreditNote;
use App\Models\SpDebitNote;
use App\Models\SpInvoicePermission;
use App\Models\SpItem;
use App\Models\SpPayment;
use App\Models\SpPaymentPermission;
use App\Models\SpProvider;
use App\Models\SpProviderGroup;
use App\Models\SpTax;
use App\Models\SpVendorApproval;
use App\Support\SoftStatsBag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ServiceProvidersController extends Controller
{
    use AuthorizesCataloguePermission;

    public function taxes(Request $request): View
    {
        $records = SpTax::query()->latest()->get();
        $taxes = $records->map(fn (SpTax $row): array => [
            'code' => $row->code,
            'name' => $row->name,
            'type' => $row->type ?? '—',
            'rate' => $row->rate ?? '—',
            'status' => $row->status,
        ])->all();

        return view('service-providers.taxes', [
            'taxes' => $taxes,
            'stats' => new SoftStatsBag(['taxCodes' => $records->count()]),
        ])->with('operationalCreate', $this->form('Add tax', 'Persists to sp_taxes.', 'service-providers.taxes.store', [
            ['name' => 'code', 'label' => 'Tax code', 'required' => true],
            ['name' => 'name', 'label' => 'Name', 'required' => true],
            ['name' => 'type', 'label' => 'Type'],
            ['name' => 'rate', 'label' => 'Rate %'],
            ['name' => 'status', 'label' => 'Status'],
        ]));
    }

    public function storeTaxes(Request $request): RedirectResponse
    {
        return $this->store($request, SpTax::class, [
            'code' => ['required', 'string', 'max:40', 'unique:sp_taxes,code'],
            'name' => ['required', 'string', 'max:190'],
            'type' => ['nullable', 'string', 'max:80'],
            'rate' => ['nullable', 'string', 'max:40'],
            'status' => ['nullable', 'string', 'max:40'],
        ], ['status' => 'Active'], 'Tax saved.');
    }

    public function items(Request $request): View
    {
        $records = SpItem::query()->latest()->get();
        $items = $records->map(fn (SpItem $row): array => [
            'code' => $row->code,
            'name' => $row->name,
            'category' => $row->category ?? '—',
            'unit_cost' => $row->unit_cost ?? '—',
            'stock' => $row->stock ?? '—',
        ])->all();

        return view('service-providers.items', [
            'items' => $items,
            'stats' => new SoftStatsBag(['catalogItems' => $records->count()]),
        ])->with('operationalCreate', $this->form('Add item', 'Persists to sp_items.', 'service-providers.items.store', [
            ['name' => 'code', 'label' => 'Item code', 'required' => true],
            ['name' => 'name', 'label' => 'Name', 'required' => true],
            ['name' => 'category', 'label' => 'Category'],
            ['name' => 'unit_cost', 'label' => 'Unit cost'],
            ['name' => 'stock', 'label' => 'Stock'],
        ]));
    }

    public function storeItems(Request $request): RedirectResponse
    {
        return $this->store($request, SpItem::class, [
            'code' => ['required', 'string', 'max:80', 'unique:sp_items,code'],
            'name' => ['required', 'string', 'max:190'],
            'category' => ['nullable', 'string', 'max:120'],
            'unit_cost' => ['nullable', 'string', 'max:80'],
            'stock' => ['nullable', 'string', 'max:40'],
        ], [], 'Item saved.');
    }

    public function providerGroups(Request $request): View
    {
        $records = SpProviderGroup::query()->latest()->get();
        $groups = $records->map(fn (SpProviderGroup $row): array => [
            'code' => $row->code,
            'name' => $row->name,
            'desc' => $row->desc ?? '—',
            'status' => $row->status,
        ])->all();

        return view('service-providers.provider-groups', [
            'groups' => $groups,
            'stats' => new SoftStatsBag(['groups' => $records->count()]),
        ])->with('operationalCreate', $this->form('Add provider group', 'Persists to sp_provider_groups.', 'service-providers.provider-groups.store', [
            ['name' => 'code', 'label' => 'Group code', 'required' => true],
            ['name' => 'name', 'label' => 'Name', 'required' => true],
            ['name' => 'desc', 'label' => 'Description'],
            ['name' => 'status', 'label' => 'Status'],
        ]));
    }

    public function storeProviderGroups(Request $request): RedirectResponse
    {
        return $this->store($request, SpProviderGroup::class, [
            'code' => ['required', 'string', 'max:80', 'unique:sp_provider_groups,code'],
            'name' => ['required', 'string', 'max:190'],
            'desc' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:40'],
        ], ['status' => 'Active'], 'Provider group saved.');
    }

    public function providers(Request $request): View
    {
        $records = SpProvider::query()->latest()->get();
        $providers = $records->map(fn (SpProvider $row): array => [
            'provider_code' => $row->provider_code,
            'name' => $row->name,
            'group' => $row->group ?? '—',
            'contact' => $row->contact ?? '—',
            'outstanding_bills' => $row->outstanding_bills ?? '—',
            'status' => $row->status,
        ])->all();
        $stats = new SoftStatsBag([
            'totalProviders' => $records->count(),
            'pendingApprovals' => $records->filter(fn (SpProvider $r): bool => str_contains(strtolower($r->status), 'pending'))->count(),
            'billsUnpaid' => $records->filter(fn (SpProvider $r): bool => filled($r->outstanding_bills) && $r->outstanding_bills !== '0')->count(),
            'totalOutstanding' => $records->pluck('outstanding_bills')->filter()->first() ?? 'KES 0',
        ]);

        return view('service-providers.providers', compact('providers', 'stats'))->with(
            'operationalCreate',
            $this->form('Add provider', 'Persists to sp_providers.', 'service-providers.providers.store', [
                ['name' => 'provider_code', 'label' => 'Provider code', 'required' => true],
                ['name' => 'name', 'label' => 'Name', 'required' => true],
                ['name' => 'group', 'label' => 'Group'],
                ['name' => 'contact', 'label' => 'Contact'],
                ['name' => 'outstanding_bills', 'label' => 'Outstanding bills'],
                ['name' => 'status', 'label' => 'Status'],
            ]),
        );
    }

    public function storeProviders(Request $request): RedirectResponse
    {
        return $this->store($request, SpProvider::class, [
            'provider_code' => ['required', 'string', 'max:80', 'unique:sp_providers,provider_code'],
            'name' => ['required', 'string', 'max:190'],
            'group' => ['nullable', 'string', 'max:120'],
            'contact' => ['nullable', 'string', 'max:190'],
            'outstanding_bills' => ['nullable', 'string', 'max:80'],
            'status' => ['nullable', 'string', 'max:40'],
        ], ['status' => 'Active'], 'Provider saved.');
    }

    public function vendorApproval(Request $request): View
    {
        $records = SpVendorApproval::query()->latest()->get();
        $approvals = $records->map(fn (SpVendorApproval $row): array => [
            'ref' => $row->ref,
            'name' => $row->name,
            'kra_pin' => $row->kra_pin ?? '—',
            'compliance_doc' => $row->compliance_doc ?? '—',
            'status' => $row->status,
        ])->all();

        return view('service-providers.vendor-approval', [
            'approvals' => $approvals,
            'stats' => new SoftStatsBag(['pending' => $records->filter(fn (SpVendorApproval $r): bool => str_contains(strtolower($r->status), 'pending'))->count()]),
        ])->with('operationalCreate', $this->form('Add vendor approval', 'Persists to sp_vendor_approvals.', 'service-providers.vendor-approval.store', [
            ['name' => 'ref', 'label' => 'Reference', 'required' => true],
            ['name' => 'name', 'label' => 'Vendor name', 'required' => true],
            ['name' => 'kra_pin', 'label' => 'KRA PIN'],
            ['name' => 'compliance_doc', 'label' => 'Compliance document'],
            ['name' => 'status', 'label' => 'Status'],
        ]));
    }

    public function storeVendorApproval(Request $request): RedirectResponse
    {
        return $this->store($request, SpVendorApproval::class, [
            'ref' => ['required', 'string', 'max:80', 'unique:sp_vendor_approvals,ref'],
            'name' => ['required', 'string', 'max:190'],
            'kra_pin' => ['nullable', 'string', 'max:40'],
            'compliance_doc' => ['nullable', 'string', 'max:190'],
            'status' => ['nullable', 'string', 'max:40'],
        ], ['status' => 'Pending'], 'Vendor approval saved.');
    }

    public function invoicePermissions(Request $request): View
    {
        $records = SpInvoicePermission::query()->latest()->get();
        $stats = new SoftStatsBag([
            'policyLevel' => $records->pluck('policy_level')->filter()->first() ?? 'Database-governed invoice upload policy',
            'lastAudited' => $records->pluck('last_audited')->filter()->first() ?? ($records->count().' permission rows'),
        ]);

        return view('service-providers.invoice-permissions', compact('stats'))->with(
            'operationalCreate',
            $this->form('Add invoice permission', 'Persists to sp_invoice_permissions.', 'service-providers.invoice-permissions.store', [
                ['name' => 'staff_name', 'label' => 'Staff', 'required' => true],
                ['name' => 'department', 'label' => 'Department'],
                ['name' => 'limit_amount', 'label' => 'Invoice limit'],
                ['name' => 'policy_level', 'label' => 'Policy level'],
                ['name' => 'last_audited', 'label' => 'Last audited'],
                ['name' => 'status', 'label' => 'Status'],
            ]),
        );
    }

    public function storeInvoicePermissions(Request $request): RedirectResponse
    {
        return $this->store($request, SpInvoicePermission::class, [
            'staff_name' => ['required', 'string', 'max:190'],
            'department' => ['nullable', 'string', 'max:190'],
            'limit_amount' => ['nullable', 'string', 'max:80'],
            'policy_level' => ['nullable', 'string', 'max:120'],
            'last_audited' => ['nullable', 'string', 'max:80'],
            'status' => ['nullable', 'string', 'max:40'],
        ], ['status' => 'Active'], 'Invoice permission saved.');
    }

    public function bills(Request $request): View
    {
        $records = SpBill::query()->latest()->get();
        $bills = $records->map(fn (SpBill $row): array => [
            'ref' => $row->ref,
            'vendor' => $row->vendor,
            'amount' => $row->amount ?? '—',
            'due_date' => $row->due_date ?? '—',
            'status' => $row->status,
        ])->all();

        return view('service-providers.bills', [
            'bills' => $bills,
            'stats' => new SoftStatsBag(['unpaid' => $records->filter(fn (SpBill $r): bool => str_contains(strtolower($r->status), 'unpaid'))->count()]),
        ])->with('operationalCreate', $this->form('Add bill', 'Persists to sp_bills.', 'service-providers.bills.store', [
            ['name' => 'ref', 'label' => 'Bill ref', 'required' => true],
            ['name' => 'vendor', 'label' => 'Vendor', 'required' => true],
            ['name' => 'amount', 'label' => 'Amount'],
            ['name' => 'due_date', 'label' => 'Due date'],
            ['name' => 'status', 'label' => 'Status'],
        ]));
    }

    public function storeBills(Request $request): RedirectResponse
    {
        return $this->store($request, SpBill::class, [
            'ref' => ['required', 'string', 'max:80', 'unique:sp_bills,ref'],
            'vendor' => ['required', 'string', 'max:190'],
            'amount' => ['nullable', 'string', 'max:80'],
            'due_date' => ['nullable', 'string', 'max:40'],
            'status' => ['nullable', 'string', 'max:40'],
        ], ['status' => 'Unpaid'], 'Bill saved.');
    }

    public function paymentPermissions(Request $request): View
    {
        $records = SpPaymentPermission::query()->latest()->get();
        $stats = new SoftStatsBag([
            'tierLimit' => $records->pluck('limit_amount')->filter()->first() ?? 'Limits stored per permission row',
            'compliance' => $records->pluck('compliance')->filter()->first() ?? ($records->count().' live permission rows'),
        ]);

        return view('service-providers.payment-permissions', compact('stats'))->with(
            'operationalCreate',
            $this->form('Add payment permission', 'Persists to sp_payment_permissions.', 'service-providers.payment-permissions.store', [
                ['name' => 'staff_name', 'label' => 'Staff', 'required' => true],
                ['name' => 'department', 'label' => 'Department'],
                ['name' => 'limit_amount', 'label' => 'Payment limit'],
                ['name' => 'compliance', 'label' => 'Compliance'],
                ['name' => 'status', 'label' => 'Status'],
            ]),
        );
    }

    public function storePaymentPermissions(Request $request): RedirectResponse
    {
        return $this->store($request, SpPaymentPermission::class, [
            'staff_name' => ['required', 'string', 'max:190'],
            'department' => ['nullable', 'string', 'max:190'],
            'limit_amount' => ['nullable', 'string', 'max:80'],
            'compliance' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'string', 'max:40'],
        ], ['status' => 'Active'], 'Payment permission saved.');
    }

    public function payments(Request $request): View
    {
        $records = SpPayment::query()->latest()->get();
        $payments = $records->map(fn (SpPayment $row): array => [
            'ref' => $row->ref,
            'vendor' => $row->vendor,
            'amount' => $row->amount ?? '—',
            'date' => $row->date ?? '—',
            'mode' => $row->mode ?? '—',
            'status' => $row->status,
        ])->all();

        return view('service-providers.payments', [
            'payments' => $payments,
            'stats' => new SoftStatsBag(['paid' => $records->filter(fn (SpPayment $r): bool => str_contains(strtolower($r->status), 'paid'))->count()]),
        ])->with('operationalCreate', $this->form('Add payment', 'Persists to sp_payments.', 'service-providers.payments.store', [
            ['name' => 'ref', 'label' => 'Payment ref', 'required' => true],
            ['name' => 'vendor', 'label' => 'Vendor', 'required' => true],
            ['name' => 'amount', 'label' => 'Amount'],
            ['name' => 'date', 'label' => 'Payment date'],
            ['name' => 'mode', 'label' => 'Mode'],
            ['name' => 'status', 'label' => 'Status'],
        ]));
    }

    public function storePayments(Request $request): RedirectResponse
    {
        return $this->store($request, SpPayment::class, [
            'ref' => ['required', 'string', 'max:80', 'unique:sp_payments,ref'],
            'vendor' => ['required', 'string', 'max:190'],
            'amount' => ['nullable', 'string', 'max:80'],
            'date' => ['nullable', 'string', 'max:40'],
            'mode' => ['nullable', 'string', 'max:80'],
            'status' => ['nullable', 'string', 'max:40'],
        ], ['status' => 'Pending'], 'Payment saved.');
    }

    public function debitNotes(Request $request): View
    {
        $records = SpDebitNote::query()->latest()->get();
        $debitNotes = $records->map(fn (SpDebitNote $row): array => [
            'ref' => $row->ref,
            'vendor' => $row->vendor,
            'amount' => $row->amount ?? '—',
            'reason' => $row->reason ?? '—',
            'status' => $row->status,
        ])->all();

        return view('service-providers.debit-notes', [
            'debitNotes' => $debitNotes,
            'stats' => new SoftStatsBag(['open' => $records->count()]),
        ])->with('operationalCreate', $this->form('Add debit note', 'Persists to sp_debit_notes.', 'service-providers.debit-notes.store', [
            ['name' => 'ref', 'label' => 'Note ref', 'required' => true],
            ['name' => 'vendor', 'label' => 'Vendor', 'required' => true],
            ['name' => 'amount', 'label' => 'Amount'],
            ['name' => 'reason', 'label' => 'Reason'],
            ['name' => 'status', 'label' => 'Status'],
        ]));
    }

    public function storeDebitNotes(Request $request): RedirectResponse
    {
        return $this->store($request, SpDebitNote::class, [
            'ref' => ['required', 'string', 'max:80', 'unique:sp_debit_notes,ref'],
            'vendor' => ['required', 'string', 'max:190'],
            'amount' => ['nullable', 'string', 'max:80'],
            'reason' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:40'],
        ], ['status' => 'Open'], 'Debit note saved.');
    }

    public function creditNotes(Request $request): View
    {
        $records = SpCreditNote::query()->latest()->get();
        $creditNotes = $records->map(fn (SpCreditNote $row): array => [
            'ref' => $row->ref,
            'vendor' => $row->vendor,
            'amount' => $row->amount ?? '—',
            'reason' => $row->reason ?? '—',
            'status' => $row->status,
        ])->all();

        return view('service-providers.credit-notes', [
            'creditNotes' => $creditNotes,
            'stats' => new SoftStatsBag(['open' => $records->count()]),
        ])->with('operationalCreate', $this->form('Add credit note', 'Persists to sp_credit_notes.', 'service-providers.credit-notes.store', [
            ['name' => 'ref', 'label' => 'Note ref', 'required' => true],
            ['name' => 'vendor', 'label' => 'Vendor', 'required' => true],
            ['name' => 'amount', 'label' => 'Amount'],
            ['name' => 'reason', 'label' => 'Reason'],
            ['name' => 'status', 'label' => 'Status'],
        ]));
    }

    public function storeCreditNotes(Request $request): RedirectResponse
    {
        return $this->store($request, SpCreditNote::class, [
            'ref' => ['required', 'string', 'max:80', 'unique:sp_credit_notes,ref'],
            'vendor' => ['required', 'string', 'max:190'],
            'amount' => ['nullable', 'string', 'max:80'],
            'reason' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:40'],
        ], ['status' => 'Open'], 'Credit note saved.');
    }

    /**
     * @param  class-string<Model>  $model
     * @param  array<string, list<mixed>>  $rules
     * @param  array<string, mixed>  $defaults
     */
    private function store(Request $request, string $model, array $rules, array $defaults, string $message): RedirectResponse
    {
        $this->authorizePermission($request, 'service_providers.manage');
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
