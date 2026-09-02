<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AcademicDepartment;
use App\Models\DeletionRecord;
use App\Models\Platform\LegalHold;
use App\Models\Platform\RetentionRule;
use App\Models\User;
use App\Services\RecycleBinService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class GovernanceAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRbac();
    }

    public function test_non_admin_is_denied_governance_and_audit_pages(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($staff)->get(route('admin.setups.governance.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('admin.setups.governance.audit'))->assertForbidden();
    }

    public function test_admin_can_publish_an_effective_dated_retention_version(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->grantRole($admin, 'data_protection_officer');
        $existing = RetentionRule::query()->where('code', 'CURRICULUM-MASTER-DATA')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.setups.governance.retention.store'), [
            'code' => 'CURRICULUM-MASTER-DATA',
            'subject_type' => 'curriculum_master_data',
            'description' => 'Revised curriculum master-data retention period.',
            'retention_months' => 24,
            'disposal_action' => 'PURGE',
            'effective_from' => now()->addDay()->toDateString(),
            'change_reason' => 'The institutional retention committee approved two years.',
        ])->assertRedirect();

        $this->assertDatabaseHas('retention_rules', [
            'code' => 'CURRICULUM-MASTER-DATA', 'version' => 2, 'retention_months' => 24,
            'status' => 'SCHEDULED', 'created_by' => $admin->id,
        ]);
        $this->assertDatabaseHas('retention_rules', [
            'id' => $existing->id, 'version' => 1, 'status' => 'ACTIVE',
            'effective_to' => now()->toDateString(),
        ]);
        $this->assertDatabaseHas('audit_events', ['action' => 'retention_rule.version_published']);
    }

    public function test_deletion_retains_the_exact_effective_retention_rule_version(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->grantRole($admin, 'data_protection_officer');
        $rule = RetentionRule::query()->where('code', 'CURRICULUM-MASTER-DATA')->firstOrFail();
        $department = AcademicDepartment::create(['code' => 'DEPT-POLICY', 'name' => 'Policy Test', 'status' => 'Active']);

        app(RecycleBinService::class)->delete(
            $department,
            $admin,
            'department',
            'Master data was superseded through approved governance.',
            '/curriculum/department',
        );

        $this->assertDatabaseHas('deletion_records', [
            'record_id' => (string) $department->id,
            'retention_rule_id' => $rule->id,
        ]);
    }

    public function test_legal_hold_can_be_placed_and_released_with_audit_evidence(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->grantRole($admin, 'data_protection_officer');
        $department = AcademicDepartment::create(['code' => 'DEPT-HOLD-UI', 'name' => 'Held Department', 'status' => 'Active']);
        $deletion = app(RecycleBinService::class)->delete(
            $department,
            $admin,
            'department',
            'Record is entering a regulatory evidence review.',
            '/curriculum/department',
        );

        $this->actingAs($admin)->post(route('admin.setups.governance.holds.store'), [
            'deletion_record_id' => $deletion->id,
            'reason' => 'Regulator requested preservation during the investigation.',
        ])->assertRedirect();
        $hold = LegalHold::query()->firstOrFail();
        $this->assertDatabaseHas('audit_events', ['action' => 'legal_hold.placed', 'subject_id' => (string) $department->id]);

        $this->actingAs($admin)->patch(route('admin.setups.governance.holds.release', $hold), [
            'release_reason' => 'The regulator confirmed that the investigation is closed.',
        ])->assertRedirect();
        $this->assertNotNull($hold->fresh()->released_at);
        $this->assertDatabaseHas('audit_events', ['action' => 'legal_hold.released', 'subject_id' => (string) $department->id]);
    }

    public function test_governance_dashboard_and_audit_filters_are_database_driven(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->grantRole($admin, 'data_protection_officer');
        $department = AcademicDepartment::create(['code' => 'DEPT-AUDIT-UI', 'name' => 'Audit Department', 'status' => 'Active']);
        app(RecycleBinService::class)->delete($department, $admin, 'department', 'Generate an auditable governance event.', '/curriculum/department');

        $this->actingAs($admin)->get(route('admin.setups.governance.index'))
            ->assertOk()->assertSee('Data governance')->assertSee('Retention rule versions');
        $this->get(route('admin.setups.governance.audit', ['action' => 'soft_deleted']))
            ->assertOk()->assertSee('record.soft_deleted')->assertDontSee('No audit events match');

        $this->assertSame(1, DeletionRecord::query()->where('status', 'deleted')->count());
    }
}
