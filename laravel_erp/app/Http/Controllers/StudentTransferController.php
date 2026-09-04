<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesCataloguePermission;
use App\Models\CourseExemption;
use App\Models\CreditTransfer;
use App\Models\FacultyTransfer;
use App\Models\Student;
use App\Models\TransferWindow;
use App\Support\SoftStatsBag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class StudentTransferController extends Controller
{
    use AuthorizesCataloguePermission;

    public function datesSetup(Request $request): View
    {
        $records = TransferWindow::query()->latest()->get();
        $dates = $records->map(fn (TransferWindow $row): array => [
            'id' => $row->id,
            'type' => $row->type,
            'academic_year' => $row->academic_year,
            'notification_date' => $row->notification_date?->format('Y-m-d') ?? '—',
            'start_date' => $row->start_date?->format('Y-m-d') ?? '—',
            'end_date' => $row->end_date?->format('Y-m-d') ?? '—',
            'status' => $row->status,
        ])->all();
        $stats = new SoftStatsBag([
            'windows' => $records->count(),
            'creditTransfer' => $records->filter(fn (TransferWindow $r): bool => str_contains(strtolower($r->type), 'credit'))->count(),
            'interIntra' => $records->filter(fn (TransferWindow $r): bool => str_contains(strtolower($r->type), 'inter'))->count(),
            'open' => $records->filter(fn (TransferWindow $r): bool => str_contains(strtolower($r->status), 'open'))->count(),
        ]);

        return view('transfers.dates-setup', compact('dates', 'stats'))->with('operationalCreate', [
            'title' => 'Add transfer window',
            'hint' => 'Persists to transfer_windows.',
            'action' => route('transfers.dates.store'),
            'fields' => [
                ['name' => 'type', 'label' => 'Transfer type', 'required' => true],
                ['name' => 'academic_year', 'label' => 'Academic year', 'required' => true],
                ['name' => 'notification_date', 'label' => 'Notification date', 'type' => 'date'],
                ['name' => 'start_date', 'label' => 'Start date', 'type' => 'date'],
                ['name' => 'end_date', 'label' => 'End date', 'type' => 'date'],
                ['name' => 'status', 'label' => 'Status'],
            ],
        ]);
    }

    public function storeDate(Request $request): RedirectResponse
    {
        $this->authorizePermission($request, 'transfers.manage');
        $data = $request->validate([
            'type' => ['required', 'string', 'max:80'],
            'academic_year' => ['required', 'string', 'max:20'],
            'notification_date' => ['nullable', 'date'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['nullable', 'string', 'max:40'],
        ]);
        TransferWindow::query()->updateOrCreate(
            ['type' => $data['type'], 'academic_year' => $data['academic_year']],
            [
                'notification_date' => $data['notification_date'] ?? null,
                'start_date' => $data['start_date'] ?? null,
                'end_date' => $data['end_date'] ?? null,
                'status' => $data['status'] ?? 'Open',
            ],
        );

        return back()->with('success', 'Transfer window saved.');
    }

    public function interIntra(Request $request): View
    {
        $query = FacultyTransfer::query()->latest();
        if ($request->filled('status')) {
            $needle = strtolower((string) $request->query('status'));
            $query->whereRaw('LOWER(status) LIKE ?', ['%'.$needle.'%']);
        }
        if ($request->filled('search')) {
            $needle = '%'.strtolower((string) $request->query('search')).'%';
            $query->where(function ($q) use ($needle): void {
                $q->whereRaw('LOWER(name) LIKE ?', [$needle])
                    ->orWhereRaw('LOWER(COALESCE(email, \'\')) LIKE ?', [$needle])
                    ->orWhereRaw('LOWER(COALESCE(reg_no, \'\')) LIKE ?', [$needle]);
            });
        }
        $records = $query->get();
        $transfers = $records->map(fn (FacultyTransfer $row): array => [
            'id' => $row->id,
            'name' => $row->name,
            'email' => $row->email ?? '—',
            'reg_no' => $row->reg_no ?? '—',
            'type' => $row->type,
            'current_programme' => $row->current_programme ?? '—',
            'transfer_programme' => $row->transfer_programme ?? '—',
            'reason' => $row->reason ?? '—',
            'status' => $row->status,
        ])->all();
        $stats = new SoftStatsBag([
            'total' => $records->count(),
            'endorsed' => $records->filter(fn (FacultyTransfer $r): bool => str_contains(strtolower($r->status), 'endors') || str_contains(strtolower($r->status), 'approv'))->count(),
            'pending' => $records->filter(fn (FacultyTransfer $r): bool => str_contains(strtolower($r->status), 'pending'))->count(),
            'rejected' => $records->filter(fn (FacultyTransfer $r): bool => str_contains(strtolower($r->status), 'reject'))->count(),
        ]);

        return view('transfers.inter-intra', [
            'transfers' => $transfers,
            'stats' => $stats,
            'search' => $request->query('search'),
            'status' => $request->query('status'),
        ])->with('operationalCreate', [
            'title' => 'Add faculty transfer',
            'hint' => 'Persists to faculty_transfers.',
            'action' => route('transfers.inter-intra.store'),
            'fields' => [
                ['name' => 'name', 'label' => 'Student name', 'required' => true],
                ['name' => 'email', 'label' => 'Email'],
                ['name' => 'reg_no', 'label' => 'Registration number'],
                ['name' => 'type', 'label' => 'Transfer type'],
                ['name' => 'current_programme', 'label' => 'From programme'],
                ['name' => 'transfer_programme', 'label' => 'To programme'],
                ['name' => 'reason', 'label' => 'Reason', 'type' => 'textarea'],
                ['name' => 'status', 'label' => 'Status'],
            ],
        ]);
    }

    public function storeInterIntra(Request $request): RedirectResponse
    {
        $this->authorizePermission($request, 'transfers.manage');
        $data = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'email' => ['nullable', 'email', 'max:190'],
            'reg_no' => ['nullable', 'string', 'max:40'],
            'type' => ['nullable', 'string', 'max:40'],
            'current_programme' => ['nullable', 'string', 'max:190'],
            'transfer_programme' => ['nullable', 'string', 'max:190'],
            'reason' => ['nullable', 'string', 'max:2000'],
            'status' => ['nullable', 'string', 'max:40'],
        ]);
        FacultyTransfer::query()->create([
            ...$data,
            'type' => $data['type'] ?? 'Intra',
            'status' => $data['status'] ?? 'Pending',
            'student_id' => Student::query()->where('admission_number', $data['reg_no'] ?? '')->value('id'),
        ]);

        return back()->with('success', 'Faculty transfer saved.');
    }

    public function updateInterIntraStatus(Request $request, FacultyTransfer $transfer): RedirectResponse
    {
        $this->authorizePermission($request, 'transfers.manage');
        $data = $request->validate([
            'status' => ['required', 'string', 'max:40'],
        ]);
        $transfer->update(['status' => $data['status']]);

        return back()->with('success', 'Transfer status updated.');
    }

    public function creditTransfers(Request $request): View
    {
        $query = CreditTransfer::query()->latest();
        if ($request->filled('status')) {
            $needle = strtolower((string) $request->query('status'));
            $query->whereRaw('LOWER(status) LIKE ?', ['%'.$needle.'%']);
        }
        if ($request->filled('search')) {
            $needle = '%'.strtolower((string) $request->query('search')).'%';
            $query->where(function ($q) use ($needle): void {
                $q->whereRaw('LOWER(name) LIKE ?', [$needle])
                    ->orWhereRaw('LOWER(COALESCE(admission_number, \'\')) LIKE ?', [$needle])
                    ->orWhereRaw('LOWER(COALESCE(course_code, \'\')) LIKE ?', [$needle]);
            });
        }
        $records = $query->get();
        $creditEntries = $records->map(fn (CreditTransfer $row): array => [
            'id' => $row->id,
            'name' => $row->name,
            'admission_number' => $row->admission_number ?? '—',
            'course_code' => $row->course_code ?? '—',
            'course_name' => $row->course_name ?? '—',
            'programme_code' => $row->programme_code ?? '—',
            'programme_name' => $row->programme_name ?? '—',
            'prior_institution' => $row->prior_institution ?? '—',
            'credits' => $row->credits !== null ? (string) $row->credits : '—',
            'status' => $row->status,
            'status_type' => $row->status_type ?? strtolower($row->status),
        ])->all();
        $total = $records->count();
        $pending = $records->filter(fn (CreditTransfer $r): bool => str_contains(strtolower($r->status), 'pending'))->count();
        $approved = $records->filter(fn (CreditTransfer $r): bool => str_contains(strtolower($r->status), 'approv'))->count();
        $rejected = $records->filter(fn (CreditTransfer $r): bool => str_contains(strtolower($r->status), 'reject'))->count();
        $stats = new SoftStatsBag([
            'totalRequests' => $total,
            'totalRate' => $this->percent($total, max(1, $total)),
            'pendingApprovals' => $pending,
            'unassignedPending' => $pending,
            'pendingRate' => $this->percent($pending, max(1, $total)),
            'approvedTransfers' => $approved,
            'approvedRate' => $this->percent($approved, max(1, $total)),
            'rejectedRequests' => $rejected,
            'rejectedRate' => $this->percent($rejected, max(1, $total)),
        ]);

        return view('transfers.credit-transfers', [
            'creditEntries' => $creditEntries,
            'stats' => $stats,
            'search' => $request->query('search'),
            'status' => $request->query('status'),
        ])->with('operationalCreate', [
            'title' => 'Add credit transfer',
            'hint' => 'Persists to credit_transfers.',
            'action' => route('transfers.credits.store'),
            'fields' => [
                ['name' => 'name', 'label' => 'Student name', 'required' => true],
                ['name' => 'admission_number', 'label' => 'Admission / reg number'],
                ['name' => 'course_code', 'label' => 'Course code'],
                ['name' => 'course_name', 'label' => 'Course name'],
                ['name' => 'programme_code', 'label' => 'Programme code'],
                ['name' => 'programme_name', 'label' => 'Programme name'],
                ['name' => 'prior_institution', 'label' => 'Prior institution'],
                ['name' => 'credits', 'label' => 'Credits', 'type' => 'number'],
                ['name' => 'status', 'label' => 'Status'],
            ],
        ]);
    }

    public function storeCredit(Request $request): RedirectResponse
    {
        $this->authorizePermission($request, 'transfers.manage');
        $data = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'admission_number' => ['nullable', 'string', 'max:40'],
            'course_code' => ['nullable', 'string', 'max:40'],
            'course_name' => ['nullable', 'string', 'max:190'],
            'programme_code' => ['nullable', 'string', 'max:40'],
            'programme_name' => ['nullable', 'string', 'max:190'],
            'prior_institution' => ['nullable', 'string', 'max:190'],
            'credits' => ['nullable', 'integer', 'min:0', 'max:999'],
            'status' => ['nullable', 'string', 'max:40'],
        ]);
        $status = $data['status'] ?? 'Pending';
        CreditTransfer::query()->create([
            ...$data,
            'status' => $status,
            'status_type' => strtolower($status),
            'student_id' => Student::query()->where('admission_number', $data['admission_number'] ?? '')->value('id'),
        ]);

        return back()->with('success', 'Credit transfer saved.');
    }

    public function updateCreditStatus(Request $request, CreditTransfer $credit): RedirectResponse
    {
        $this->authorizePermission($request, 'transfers.manage');
        $data = $request->validate([
            'status' => ['required', 'string', 'max:40'],
            'status_type' => ['nullable', 'string', 'max:40'],
        ]);
        $credit->update([
            'status' => $data['status'],
            'status_type' => $data['status_type'] ?? strtolower($data['status']),
        ]);

        return back()->with('success', 'Credit transfer status updated.');
    }

    public function exemptions(Request $request): View
    {
        $query = CourseExemption::query()->latest();
        if ($request->filled('status')) {
            $needle = strtolower((string) $request->query('status'));
            $query->whereRaw('LOWER(status) LIKE ?', ['%'.$needle.'%']);
        }
        if ($request->filled('search')) {
            $needle = '%'.strtolower((string) $request->query('search')).'%';
            $query->where(function ($q) use ($needle): void {
                $q->whereRaw('LOWER(name) LIKE ?', [$needle])
                    ->orWhereRaw('LOWER(COALESCE(admission_number, \'\')) LIKE ?', [$needle])
                    ->orWhereRaw('LOWER(COALESCE(course_code, \'\')) LIKE ?', [$needle]);
            });
        }
        $records = $query->get();
        $allEntries = $records->map(fn (CourseExemption $row): array => [
            'id' => $row->id,
            'name' => $row->name,
            'admission_number' => $row->admission_number ?? '—',
            'course_code' => $row->course_code ?? '—',
            'course_name' => $row->course_name ?? '—',
            'programme_code' => $row->programme_code ?? '—',
            'programme_name' => $row->programme_name ?? '—',
            'status' => $row->status,
        ])->all();
        $total = $records->count();
        $pending = $records->filter(fn (CourseExemption $r): bool => str_contains(strtolower($r->status), 'pending'))->count();
        $approved = $records->filter(fn (CourseExemption $r): bool => str_contains(strtolower($r->status), 'approv'))->count();
        $rejected = $records->filter(fn (CourseExemption $r): bool => str_contains(strtolower($r->status), 'reject'))->count();
        $stats = new SoftStatsBag([
            'totalRequests' => $total,
            'totalRate' => $this->percent($total, max(1, $total)),
            'pendingApprovals' => $pending,
            'unassignedPending' => $pending,
            'pendingRate' => $this->percent($pending, max(1, $total)),
            'approvedExemptions' => $approved,
            'approvedRate' => $this->percent($approved, max(1, $total)),
            'rejectedRequests' => $rejected,
            'rejectedRate' => $this->percent($rejected, max(1, $total)),
        ]);

        return view('transfers.exemptions', [
            'allEntries' => $allEntries,
            'stats' => $stats,
            'search' => $request->query('search'),
            'status' => $request->query('status'),
        ])->with('operationalCreate', [
            'title' => 'Add exemption',
            'hint' => 'Persists to course_exemptions.',
            'action' => route('transfers.exemptions.store'),
            'fields' => [
                ['name' => 'name', 'label' => 'Student name', 'required' => true],
                ['name' => 'admission_number', 'label' => 'Admission / reg number'],
                ['name' => 'course_code', 'label' => 'Course code'],
                ['name' => 'course_name', 'label' => 'Course name'],
                ['name' => 'programme_code', 'label' => 'Programme code'],
                ['name' => 'programme_name', 'label' => 'Programme name'],
                ['name' => 'status', 'label' => 'Status'],
            ],
        ]);
    }

    public function storeExemption(Request $request): RedirectResponse
    {
        $this->authorizePermission($request, 'transfers.manage');
        $data = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'admission_number' => ['nullable', 'string', 'max:40'],
            'course_code' => ['nullable', 'string', 'max:40'],
            'course_name' => ['nullable', 'string', 'max:190'],
            'programme_code' => ['nullable', 'string', 'max:40'],
            'programme_name' => ['nullable', 'string', 'max:190'],
            'status' => ['nullable', 'string', 'max:40'],
        ]);
        CourseExemption::query()->create([
            ...$data,
            'status' => $data['status'] ?? 'Pending',
            'student_id' => Student::query()->where('admission_number', $data['admission_number'] ?? '')->value('id'),
        ]);

        return back()->with('success', 'Exemption saved.');
    }

    public function updateExemptionStatus(Request $request, CourseExemption $exemption): RedirectResponse
    {
        $this->authorizePermission($request, 'transfers.manage');
        $data = $request->validate([
            'status' => ['required', 'string', 'max:40'],
        ]);
        $exemption->update(['status' => $data['status']]);

        return back()->with('success', 'Exemption status updated.');
    }

    private function percent(int $part, int $whole): string
    {
        return round(($part / max(1, $whole)) * 100, 2).'%';
    }
}
