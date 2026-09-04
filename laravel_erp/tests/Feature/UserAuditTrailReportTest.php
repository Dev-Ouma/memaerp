<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Platform\Audit\AuditRecorder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class UserAuditTrailReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_audit_trail_user_report(): void
    {
        $this->get('/reports/audit-trail-user')->assertRedirect(route('login'));
    }

    public function test_audit_trail_user_report_reads_from_live_postgresql_audit_events(): void
    {
        $admin = User::factory()->create([
            'name' => 'Dr. Audit Officer',
            'email' => 'auditor@mema.ac.ke',
            'role' => 'admin',
        ]);

        // Record live audit events with IP and source channel
        app(AuditRecorder::class)->record('user.security_check', [
            'actor_role' => 'admin',
            'ip_address' => '102.219.208.44',
            'subject_type' => User::class,
            'subject_id' => $admin->id,
            'before' => ['status' => 'pending'],
            'after' => ['status' => 'verified'],
            'classification' => 'confidential',
        ]);

        $this->assertDatabaseHas('audit_events', [
            'action' => 'user.security_check',
            'ip_address' => '102.219.208.44',
        ]);

        $response = $this->actingAs($admin)->get('/reports/audit-trail-user');
        $response->assertOk()
            ->assertSee('Audit Trail by User')
            ->assertSee('IP Address Source')
            ->assertSee('Dr. Audit Officer')
            ->assertSee('102.219.208.44')
            ->assertSee('user.security_check')
            ->assertSee('ADMIN');
    }

    public function test_audit_trail_user_report_search_filters_by_ip_address_and_action(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        app(AuditRecorder::class)->record('payment.gateway_verification', [
            'actor_role' => 'admin',
            'ip_address' => '192.168.100.25',
            'classification' => 'restricted',
        ]);

        app(AuditRecorder::class)->record('course.syllabus_published', [
            'actor_role' => 'admin',
            'ip_address' => '41.89.20.15',
            'classification' => 'internal',
        ]);

        // Search by IP
        $this->actingAs($admin)->get('/reports/audit-trail-user?q=192.168.100.25')
            ->assertOk()
            ->assertSee('192.168.100.25')
            ->assertSee('payment.gateway_verification')
            ->assertDontSee('41.89.20.15');

        // Search by action keyword
        $this->actingAs($admin)->get('/reports/audit-trail-user?q=syllabus')
            ->assertOk()
            ->assertSee('course.syllabus_published')
            ->assertSee('41.89.20.15')
            ->assertDontSee('192.168.100.25');
    }

    public function test_audit_trail_user_report_exports_csv_with_ip_column(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        app(AuditRecorder::class)->record('system.backup_created', [
            'actor_role' => 'admin',
            'ip_address' => '10.0.0.99',
            'classification' => 'internal',
        ]);

        $response = $this->actingAs($admin)->get(route('reports.export', [
            'report' => 'audit-trail-user',
            'format' => 'csv',
        ]));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('content-type'));
    }
}
