<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ImprestAuditLedger;
use App\Models\ImprestClaimMatrix;
use App\Models\ImprestPermission;
use App\Models\ImprestRequisition;
use App\Models\ImprestSurrender;
use App\Models\ImprestSurrenderRule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ImprestDeskEndToEndTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_imprest_desk_end_to_end(): void
    {
        $this->seedRbac();
        $officer = User::factory()->create(['role' => 'staff', 'is_active' => true]);
        $this->grantRole($officer, 'finance_officer');
        $this->actingAs($officer);

        $this->post(route('imprest.permissions.store'), [
            'role_title' => 'HOD Approver',
            'authority_level' => 'Level 2',
            'min_limit' => 'KES 1,000',
            'max_limit' => 'KES 50,000',
            'allowed_categories' => 'Travel, Field',
            'status' => 'Active',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->post(route('imprest.claim-approvals.store'), [
            'workflow_code' => 'WF-IMP-01',
            'claim_category' => 'Travel',
            'originating_unit' => 'Registry',
            'workflow_sequence' => 'Officer > HOD > Finance',
            'auto_escalation_hours' => 48,
            'delegate_allowed' => 'Yes',
            'status' => 'Active',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->post(route('imprest.surrender-permissions.store'), [
            'policy_code' => 'SUR-01',
            'title' => '14-day surrender',
            'timeline' => '14 days',
            'document_requirements' => 'Receipts, itinerary',
            'non_compliance_action' => 'Payroll recovery',
            'waiver_authority' => 'Bursar',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->post(route('imprest.requisitions.store'), [
            'requisition_no' => 'REQ-9001',
            'applicant_name' => 'E2E Officer',
            'department' => 'Academic Affairs',
            'vote_head' => '2210500',
            'amount_requested' => 'KES 25,000',
            'purpose' => 'Field supervision',
            'disbursement_mode' => 'M-Pesa',
            'surrender_due_date' => '2026-09-30',
            'status' => 'Pending',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->post(route('imprest.surrenders.store'), [
            'surrender_no' => 'SUR-9001',
            'requisition_ref' => 'REQ-9001',
            'staff_name' => 'E2E Officer',
            'department' => 'Academic Affairs',
            'imprest_amount' => 'KES 25,000',
            'actual_expenditure' => 'KES 22,000',
            'unspent_refund' => 'KES 3,000',
            'surrender_status' => 'Pending',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->post(route('imprest.audit-ledger.store'), [
            'imprest_ref' => 'IMP-9001',
            'staff_name' => 'E2E Officer',
            'staff_no' => 'STF-22',
            'department' => 'Academic Affairs',
            'amount_due' => 'KES 3,000',
            'issue_date' => '2026-09-01',
            'due_date' => '2026-09-15',
            'days_overdue' => 5,
            'risk_category' => 'Watch',
            'recovery_status' => 'Open',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseHas('imprest_permissions', ['role_title' => 'HOD Approver']);
        $this->assertDatabaseHas('imprest_requisitions', ['requisition_no' => 'REQ-9001']);
        $this->assertDatabaseHas('imprest_surrenders', ['surrender_no' => 'SUR-9001']);
        $this->assertDatabaseHas('imprest_audit_ledgers', ['imprest_ref' => 'IMP-9001']);

        $this->get(route('imprest.permissions'))->assertOk()->assertSee('HOD Approver')->assertSee('KES 50,000');
        $this->get(route('imprest.requisitions'))->assertOk()->assertSee('REQ-9001')->assertSee('M-Pesa');
        $this->get(route('imprest.audit-ledger'))->assertOk()->assertSee('IMP-9001')->assertSee('E2E Officer');
    }

    public function test_imprest_screens_render_empty(): void
    {
        $this->seedRbac();
        $officer = User::factory()->create(['role' => 'staff', 'is_active' => true]);
        $this->grantRole($officer, 'finance_officer');

        foreach ([
            'imprest.permissions',
            'imprest.claim-approvals',
            'imprest.surrender-permissions',
            'imprest.requisitions',
            'imprest.surrenders',
            'imprest.audit-ledger',
        ] as $route) {
            $this->actingAs($officer)->get(route($route))->assertOk();
        }

        $this->assertSame(0, ImprestPermission::query()->count());
        $this->assertSame(0, ImprestClaimMatrix::query()->count());
        $this->assertSame(0, ImprestSurrenderRule::query()->count());
        $this->assertSame(0, ImprestRequisition::query()->count());
        $this->assertSame(0, ImprestSurrender::query()->count());
        $this->assertSame(0, ImprestAuditLedger::query()->count());
    }

    public function test_staff_without_imprest_manage_cannot_write(): void
    {
        $this->seedRbac();
        $staff = User::factory()->create(['role' => 'staff', 'is_active' => true]);

        $this->actingAs($staff)->post(route('imprest.permissions.store'), [
            'role_title' => 'Denied Role',
            'status' => 'Active',
        ])->assertForbidden();

        $this->assertDatabaseMissing('imprest_permissions', ['role_title' => 'Denied Role']);
    }
}
