<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Services\AdmissionReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

final class OperationalShimRetiredTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_operational_write_routes_are_gone(): void
    {
        $this->seedRbac();
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $this->actingAs($admin)->post('/operational/smhr/onboarding', [
            'title' => 'Should not persist',
        ])->assertNotFound();

        $this->actingAs($admin)->patch('/operational/records/1/status', [
            'status' => 'Closed',
        ])->assertNotFound();
    }

    public function test_unknown_report_key_does_not_use_module_records(): void
    {
        $report = app(AdmissionReportService::class)->getReportData(
            'unknown-desk-report',
            Request::create('/reports/unknown-desk-report', 'GET'),
        );

        $this->assertSame([], $report['rows']);
        $this->assertSame('unregistered', collect($report['stats'])->firstWhere('label', 'Source')['val'] ?? null);
        $this->assertStringContainsString('No dedicated domain report', $report['description']);
    }
}
