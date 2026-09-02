<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\BudgetProposal;
use App\Models\BudgetProposalTransition;
use App\Models\BudgetSubmitter;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class BudgetingController extends Controller
{
    private const TRANSITIONS = [
        'DRAFT' => ['SUBMITTED'],
        'SUBMITTED' => ['HOD_APPROVED', 'RETURNED', 'REJECTED'],
        'HOD_APPROVED' => ['DEAN_APPROVED', 'RETURNED', 'REJECTED'],
        'DEAN_APPROVED' => ['CFO_APPROVED', 'RETURNED', 'REJECTED'],
        'CFO_APPROVED' => ['APPROVED', 'RETURNED', 'REJECTED'],
        'RETURNED' => ['SUBMITTED'],
    ];

    public function permissions(Request $request): View
    {
        abort_unless($request->user()->isAdmin(), 403);
        $submitters = BudgetSubmitter::query()->with('user')->latest('granted_at')->get();
        $stats = ['authorizedStaff' => $submitters->where('is_active', true)->count(), 'approvalTierLevel' => 'HOD + Dean + CFO + VC', 'auditTrail' => 'Active', 'status' => 'Policy Locked'];
        $eligibleUsers = User::query()->where('is_active', true)->whereIn('role', ['staff', 'admin'])->orderBy('name')->get();

        return view('budgeting.permissions', compact('stats', 'submitters', 'eligibleUsers'));
    }

    public function storeSubmitter(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);
        $data = $request->validate([
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')->where('is_active', true)],
            'department' => ['required', 'string', 'max:190'],
        ]);
        $submitter = BudgetSubmitter::query()->updateOrCreate(
            ['user_id' => $data['user_id']],
            ['department' => $data['department'], 'is_active' => true, 'granted_by' => $request->user()->id, 'granted_at' => now()],
        );
        AuditLog::record('budget.submitter_granted', $submitter, null, $submitter->toArray());

        return back()->with('success', 'Budget proposal submitter access granted.');
    }

    public function destroySubmitter(Request $request, BudgetSubmitter $submitter): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);
        $before = $submitter->toArray();
        $submitter->update(['is_active' => false]);
        AuditLog::record('budget.submitter_revoked', $submitter, $before, $submitter->toArray());

        return back()->with('success', 'Budget proposal submitter access revoked.');
    }

    public function proposals(Request $request): View
    {
        $query = BudgetProposal::query()->with('submitter')->latest();
        if (! $request->user()->isAdmin()) {
            $query->where('submitted_by', $request->user()->id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->upper()->toString());
        }
        $proposals = $query->paginate(25)->withQueryString();
        $statsQuery = BudgetProposal::query();
        if (! $request->user()->isAdmin()) {
            $statsQuery->where('submitted_by', $request->user()->id);
        }
        $requested = (float) (clone $statsQuery)->sum('requested_amount');
        $approved = (float) (clone $statsQuery)->sum('approved_amount');
        $stats = [
            'totalProposals' => (clone $statsQuery)->count(),
            'approvedAmount' => $this->money($approved),
            'requestedAmount' => $this->money($requested),
            'varianceDeficit' => $this->money(max(0, $requested - $approved)),
        ];
        $canSubmit = $request->user()->isAdmin() || BudgetSubmitter::query()->where('user_id', $request->user()->id)->where('is_active', true)->exists();

        return view('budgeting.proposals', compact('stats', 'proposals', 'canSubmit'));
    }

    public function storeProposal(Request $request): RedirectResponse
    {
        $authorization = BudgetSubmitter::query()->where('user_id', $request->user()->id)->where('is_active', true)->first();
        abort_unless($request->user()->isAdmin() || $authorization, 403);
        $data = $request->validate([
            'fiscal_year' => ['required', 'integer', 'between:2020,2200'],
            'trimester' => ['required', Rule::in(['Trimester I', 'Trimester II', 'Trimester III'])],
            'department' => ['required', 'string', 'max:190'],
            'description' => ['required', 'string', 'min:10', 'max:3000'],
            'requested_amount' => ['required', 'numeric', 'min:1', 'max:999999999999.99'],
        ]);
        $proposal = DB::transaction(function () use ($data, $request): BudgetProposal {
            DB::table('budget_number_sequences')->insertOrIgnore([
                'fiscal_year' => $data['fiscal_year'], 'next_number' => 1, 'created_at' => now(), 'updated_at' => now(),
            ]);
            $sequence = DB::table('budget_number_sequences')->where('fiscal_year', $data['fiscal_year'])->lockForUpdate()->first();
            $number = (int) $sequence->next_number;
            DB::table('budget_number_sequences')->where('fiscal_year', $data['fiscal_year'])->update(['next_number' => $number + 1, 'updated_at' => now()]);

            return BudgetProposal::create([...$data, 'proposal_ref' => sprintf('BGT-%d-%05d', $data['fiscal_year'], $number), 'submitted_by' => $request->user()->id]);
        }, 3);
        AuditLog::record('budget.proposal_created', $proposal, null, $proposal->toArray());

        return back()->with('success', "Budget proposal {$proposal->proposal_ref} created as a draft.");
    }

    public function transition(Request $request, BudgetProposal $proposal): RedirectResponse
    {
        $isOwner = $proposal->submitted_by === $request->user()->id;
        $isAdmin = $request->user()->isAdmin();
        abort_unless($isAdmin || $isOwner, 403);
        $allowed = self::TRANSITIONS[$proposal->status] ?? [];
        $data = $request->validate([
            'status' => ['required', Rule::in($allowed)],
            'reason' => [Rule::requiredIf(in_array($request->input('status'), ['RETURNED', 'REJECTED'], true)), 'nullable', 'string', 'min:10', 'max:1000'],
            'approved_amount' => ['nullable', 'numeric', 'min:0', 'max:'.$proposal->requested_amount],
            'lock_version' => ['required', 'integer'],
        ]);
        abort_if((int) $data['lock_version'] !== $proposal->lock_version, 409, 'This proposal changed in another session. Refresh and try again.');
        abort_if(! $isAdmin && $data['status'] !== 'SUBMITTED', 403);

        DB::transaction(function () use ($proposal, $data, $request): void {
            $locked = BudgetProposal::query()->lockForUpdate()->findOrFail($proposal->id);
            abort_if($locked->lock_version !== (int) $data['lock_version'], 409, 'This proposal changed in another session. Refresh and try again.');
            $before = $locked->toArray();
            $attributes = ['status' => $data['status'], 'lock_version' => $locked->lock_version + 1];
            if ($data['status'] === 'SUBMITTED') {
                $attributes['submitted_at'] = now();
            }
            if (in_array($data['status'], ['APPROVED', 'REJECTED'], true)) {
                $attributes['decided_at'] = now();
            }
            if (isset($data['approved_amount'])) {
                $attributes['approved_amount'] = $data['approved_amount'];
            }
            $locked->update($attributes);
            BudgetProposalTransition::create([
                'budget_proposal_id' => $locked->id, 'from_status' => $before['status'], 'to_status' => $data['status'],
                'actor_user_id' => $request->user()->id, 'reason' => $data['reason'] ?? null,
                'approved_amount' => $data['approved_amount'] ?? null, 'occurred_at' => now(),
            ]);
            AuditLog::record('budget.proposal_transitioned', $locked, $before, $locked->toArray());
        }, 3);

        return back()->with('success', 'Budget proposal status updated.');
    }

    private function money(float $amount): string
    {
        return 'KES '.number_format($amount, 2);
    }
}
