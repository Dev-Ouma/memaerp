<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AcademicDepartment;
use App\Models\AcademicProgramme;
use App\Models\DeletionActionRequest;
use App\Models\DeletionRecord;
use App\Models\Platform\LegalHold;
use App\Models\User;
use App\Services\RecycleBinService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class RecycleBinTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRbac();
    }

    public function test_admin_can_view_the_database_paginated_recycle_bin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->grantRole($admin, 'data_protection_officer');

        $this->actingAs($admin)->get(route('admin.setups.recycle-bin.index'))
            ->assertOk()->assertSee('System Recycle Bin')->assertSee('Database governed');
    }

    public function test_non_admin_cannot_view_recycle_bin(): void
    {
        $user = User::factory()->create(['role' => 'staff']);
        $this->actingAs($user)->get(route('admin.setups.recycle-bin.index'))->assertForbidden();
        $this->actingAs($user)->get(route('recycle-bin'))->assertForbidden();
    }

    public function test_delete_requires_a_reason_and_records_actor_snapshot_and_audit(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->grantRole($admin, 'data_protection_officer');
        $department = AcademicDepartment::create(['code' => 'DEPT-TEST', 'name' => 'Testing Engineering', 'status' => 'Active']);

        $this->actingAs($admin)->delete(route('curriculum.department.destroy', $department))
            ->assertSessionHasErrors('deletion_reason');
        $this->assertNotSoftDeleted('academic_departments', ['id' => $department->id]);

        $this->actingAs($admin)->delete(route('curriculum.department.destroy', $department), [
            'deletion_reason' => 'Duplicate master record approved for removal.',
        ])->assertRedirect(route('curriculum.department'));

        $this->assertSoftDeleted('academic_departments', ['id' => $department->id]);
        $this->assertDatabaseHas('deletion_records', [
            'entity_type' => 'department', 'record_id' => (string) $department->id,
            'deleted_by' => $admin->id, 'deleted_by_role' => 'admin', 'status' => 'deleted',
        ]);
        $this->assertDatabaseHas('audit_events', ['action' => 'record.soft_deleted', 'subject_id' => (string) $department->id]);
    }

    public function test_record_can_be_restored_and_audited(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->grantRole($admin, 'data_protection_officer');
        $department = AcademicDepartment::create(['code' => 'DEPT-RESTORE', 'name' => 'Restore Me', 'status' => 'Active']);
        $this->actingAs($admin)->delete(route('curriculum.department.destroy', $department), [
            'deletion_reason' => 'Testing the governed restore workflow.',
        ]);
        $deletion = DeletionRecord::query()->where('record_id', (string) $department->id)->firstOrFail();

        $this->actingAs($admin)->post(route('admin.setups.recycle-bin.restore', $deletion))->assertRedirect();

        $this->assertNotSoftDeleted('academic_departments', ['id' => $department->id]);
        $this->assertDatabaseHas('deletion_records', ['id' => $deletion->id, 'status' => 'restored', 'restored_by' => $admin->id]);
        $this->assertDatabaseHas('audit_events', ['action' => 'record.restored', 'subject_id' => (string) $department->id]);
    }

    public function test_purge_is_blocked_during_retention_or_legal_hold(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->grantRole($admin, 'data_protection_officer');
        $programme = AcademicProgramme::create(['code' => 'MEMA-HOLD', 'title' => 'Held Programme', 'level' => 'Undergraduate', 'status' => 'Active']);
        app(RecycleBinService::class)->delete($programme, $admin, 'programme', 'Programme is obsolete after curriculum review.', '/curriculum/programme');
        $deletion = DeletionRecord::query()->firstOrFail();

        $this->actingAs($admin)->post(route('admin.setups.recycle-bin.purge.request', $deletion), [
            'reason' => 'Retention-approved disposal of obsolete setup.',
        ])->assertSessionHasErrors('retention');

        $deletion->update(['purge_after' => now()->subDay()]);
        LegalHold::create([
            'subject_type' => AcademicProgramme::class, 'subject_id' => (string) $programme->id,
            'reason' => 'Regulatory review underway.', 'placed_by' => $admin->id, 'placed_at' => now(),
        ]);
        $this->actingAs($admin)->post(route('admin.setups.recycle-bin.purge.request', $deletion), [
            'reason' => 'Retention-approved disposal of obsolete setup.',
        ])->assertSessionHasErrors('legal_hold');
    }

    public function test_permanent_purge_requires_a_different_checker(): void
    {
        $maker = User::factory()->create(['role' => 'admin']);
        $checker = User::factory()->create(['role' => 'admin']);
        $this->grantRole($maker, 'data_protection_officer');
        $this->grantRole($checker, 'data_protection_officer');
        $programme = AcademicProgramme::create(['code' => 'MEMA-PURGE', 'title' => 'Purge Candidate', 'level' => 'Undergraduate', 'status' => 'Active']);
        $deletion = app(RecycleBinService::class)->delete($programme, $maker, 'programme', 'Approved obsolete programme cleanup.', '/curriculum/programme');
        $deletion->update(['purge_after' => now()->subDay()]);

        $this->actingAs($maker)->post(route('admin.setups.recycle-bin.purge.request', $deletion), [
            'reason' => 'Retention elapsed and disposal was authorised.',
        ])->assertRedirect();
        $action = DeletionActionRequest::query()->firstOrFail();

        $this->actingAs($maker)->post(route('admin.setups.recycle-bin.purge.approve', $action), [
            'decision_note' => 'I am trying to approve my own purge request.',
        ])->assertSessionHasErrors('checker');
        $this->actingAs($checker)->post(route('admin.setups.recycle-bin.purge.approve', $action), [
            'decision_note' => 'Independently checked retention and dependencies.',
        ])->assertRedirect();

        $this->assertDatabaseMissing('academic_programmes', ['id' => $programme->id]);
        $this->assertDatabaseHas('deletion_records', ['id' => $deletion->id, 'status' => 'purged', 'purged_by' => $checker->id]);
        $this->assertDatabaseHas('audit_events', ['action' => 'record.permanently_purged', 'subject_id' => (string) $programme->id]);
    }

    public function test_bulk_permanent_deletion_is_disabled(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->grantRole($admin, 'data_protection_officer');
        $this->actingAs($admin)->delete(route('admin.setups.recycle-bin.empty'))->assertForbidden();
    }
}
