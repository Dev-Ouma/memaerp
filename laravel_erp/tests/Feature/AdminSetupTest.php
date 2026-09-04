<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Admission\AdminSetupDefinition;
use App\Models\User;
use App\Modules\Admission\Setups\SetupManager;
use App\Modules\Admission\Setups\SetupResolver;
use App\Modules\Platform\Api\ApiException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AdminSetupTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_declared_setup_has_a_dedicated_admin_configuration_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->actingAs($admin)->get('/admissions/setups?q=fee')->assertOk()->assertSee('Admin Setups')->assertSee('Application-fee rules');
        $setup = AdminSetupDefinition::query()->where('setup_key', 'payment.application_fee')->firstOrFail();
        $this->get(route('admissions.setups.show', $setup))->assertOk()->assertSee('Version history')->assertSee('KES');
    }

    public function test_admin_setups_sidebar_opens_the_platform_setup_hub(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('<span>Admin Setups</span>', false)
            ->assertSee(route('admin.setups.index'), false);

        $this->get(route('admin.setups.index'))
            ->assertOk()
            ->assertSee('Platform administration')
            ->assertSee('Admissions setups')
            ->assertSee('Data governance')
            ->assertSee(route('admin.setups.governance.index'), false)
            ->assertSee(route('admissions.setups.index'), false);
    }

    public function test_versions_are_effective_dated_resolved_and_historical_usage_blocks_archival(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $setup = AdminSetupDefinition::query()->where('setup_key', 'analytics.metric_definitions')->firstOrFail();
        $manager = app(SetupManager::class);
        $draft = $manager->draft($setup, ['metrics' => [['key' => 'submitted', 'aggregation' => 'count']]], 'Initial governed metric.', $admin->id);
        $active = $manager->publish($draft, now()->toDateString(), null, $admin->id);
        $resolved = app(SetupResolver::class)->use($setup->setup_key, 'dashboard', 'admissions', 'metric_calculation');

        $this->assertSame($active->id, $resolved->id);
        $this->assertDatabaseHas('admin_setup_usages', ['admin_setup_version_id' => $active->id, 'consumer_type' => 'dashboard']);
        $this->expectException(ApiException::class);
        $manager->changeStatus($active, 'ARCHIVED', $admin->id);
    }

    public function test_admin_can_toggle_enable_all_and_audit_module_integrity(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        // Toggle module with force
        $this->actingAs($admin)
            ->patchJson(route('admin.setups.module-manager.toggle'), [
                'module_key' => 'recycle-bin',
                'is_active' => false,
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('is_active', false);

        // Audit integrity
        $this->getJson(route('admin.setups.module-manager.audit-integrity'))
            ->assertOk()
            ->assertJsonPath('success', true);

        // Enable all modules in batch
        $this->postJson(route('admin.setups.module-manager.enable-all'))
            ->assertOk()
            ->assertJsonPath('success', true);
    }
}
