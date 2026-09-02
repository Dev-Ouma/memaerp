<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BudgetProposal;
use App\Models\BudgetSubmitter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class BudgetingModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_grant_and_revoke_proposal_submitter_access(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $staff = User::factory()->create(['role' => 'staff', 'is_active' => true]);

        $this->actingAs($admin)->post(route('budgeting.permissions.store'), [
            'user_id' => $staff->id,
            'department' => 'School of Computing',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $grant = BudgetSubmitter::query()->where('user_id', $staff->id)->firstOrFail();
        $this->assertTrue($grant->is_active);
        $this->assertDatabaseHas('audit_logs', ['action' => 'budget.submitter_granted']);

        $this->delete(route('budgeting.permissions.destroy', $grant))->assertRedirect();
        $this->assertFalse($grant->refresh()->is_active);
    }

    public function test_unauthorized_staff_cannot_create_a_budget_proposal(): void
    {
        $staff = User::factory()->create(['role' => 'staff', 'is_active' => true]);

        $this->actingAs($staff)->post(route('budgeting.proposals.store'), $this->proposalData())->assertForbidden();
        $this->assertDatabaseCount('budget_proposals', 0);
    }

    public function test_authorized_submitter_creates_unique_drafts_and_can_submit_own_proposal(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $staff = User::factory()->create(['role' => 'staff', 'is_active' => true]);
        BudgetSubmitter::create(['user_id' => $staff->id, 'department' => 'School of Computing', 'is_active' => true, 'granted_by' => $admin->id, 'granted_at' => now()]);

        $this->actingAs($staff)->post(route('budgeting.proposals.store'), $this->proposalData())->assertRedirect()->assertSessionHasNoErrors();
        $this->post(route('budgeting.proposals.store'), $this->proposalData())->assertRedirect()->assertSessionHasNoErrors();

        $first = BudgetProposal::query()->orderBy('id')->firstOrFail();
        $this->assertSame('BGT-2027-00001', $first->proposal_ref);
        $this->assertDatabaseHas('budget_proposals', ['proposal_ref' => 'BGT-2027-00002']);
        $this->get(route('budgeting.proposals'))
            ->assertOk()
            ->assertSee('BGT-2027-00001')
            ->assertSee('Replace laboratory workstations and network switches.');

        $this->post(route('budgeting.proposals.transition', $first), [
            'status' => 'SUBMITTED',
            'lock_version' => $first->lock_version,
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('SUBMITTED', $first->refresh()->status);
        $this->assertDatabaseHas('budget_proposal_transitions', ['budget_proposal_id' => $first->id, 'from_status' => 'DRAFT', 'to_status' => 'SUBMITTED']);
    }

    public function test_admin_processes_only_permitted_transitions_and_return_requires_reason(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $proposal = BudgetProposal::create([
            'proposal_ref' => 'BGT-2027-00001', 'fiscal_year' => 2027, 'trimester' => 'Trimester II',
            'department' => 'Library', 'description' => 'Renew licensed academic databases.',
            'requested_amount' => 2400000, 'status' => 'SUBMITTED', 'submitted_by' => $admin->id,
        ])->refresh();

        $this->actingAs($admin)->post(route('budgeting.proposals.transition', $proposal), [
            'status' => 'RETURNED', 'lock_version' => $proposal->lock_version,
        ])->assertSessionHasErrors('reason');
        $this->assertSame('SUBMITTED', $proposal->refresh()->status);

        $this->post(route('budgeting.proposals.transition', $proposal), [
            'status' => 'HOD_APPROVED', 'approved_amount' => 2200000, 'lock_version' => $proposal->lock_version,
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('HOD_APPROVED', $proposal->refresh()->status);
        $this->assertSame('2200000.00', $proposal->approved_amount);
        $this->assertDatabaseHas('audit_logs', ['action' => 'budget.proposal_transitioned', 'subject_id' => (string) $proposal->id]);
    }

    /** @return array<string, int|string> */
    private function proposalData(): array
    {
        return [
            'fiscal_year' => 2027,
            'trimester' => 'Trimester II',
            'department' => 'School of Computing',
            'description' => 'Replace laboratory workstations and network switches.',
            'requested_amount' => 5850000,
        ];
    }
}
