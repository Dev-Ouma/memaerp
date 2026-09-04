<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ModuleState;
use App\Models\User;
use App\Modules\Platform\Modules\ModuleCatalogue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ModuleManagerEndToEndTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_module_manager_with_live_submodule_links(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.setups.module-manager'));

        $response->assertOk();
        $response->assertSee('MEMA ERP Module Manager');
        $response->assertSee('admissions');
        $response->assertSee(route('curriculum.school'), false);
        $response->assertSee(route('task-management.users'), false);
    }

    public function test_staff_cannot_open_module_manager(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($staff)
            ->get(route('admin.setups.module-manager'))
            ->assertForbidden();
    }

    public function test_toggle_persists_and_blocks_staff_from_disabled_module(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($admin)
            ->patchJson(route('admin.setups.module-manager.toggle'), [
                'module_key' => 'reports',
                'is_active' => false,
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('is_active', false);

        $this->assertFalse(ModuleState::isActive('reports'));

        $this->actingAs($staff)
            ->get(route('reports.advanced-analytics'))
            ->assertStatus(503);

        $this->actingAs($admin)
            ->get(route('reports.advanced-analytics'))
            ->assertOk();
    }

    public function test_cannot_disable_a_module_while_dependents_are_active(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->patchJson(route('admin.setups.module-manager.toggle'), [
                'module_key' => 'curriculum',
                'is_active' => false,
            ])
            ->assertStatus(422);
    }

    public function test_enable_all_and_integrity_endpoints_work(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        ModuleState::setActive('reports', false, $admin->id);

        $this->actingAs($admin)
            ->postJson(route('admin.setups.module-manager.enable-all'))
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertTrue(ModuleState::isActive('reports'));

        $this->actingAs($admin)
            ->postJson(route('admin.setups.module-manager.integrity'))
            ->assertOk()
            ->assertJsonPath('success', true);

        foreach (ModuleCatalogue::keys() as $key) {
            $this->assertTrue(ModuleState::isActive($key), $key.' should be active after enable-all');
        }
    }

    public function test_disabling_admissions_blocks_staff_admissions_workspace(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($admin)
            ->patchJson(route('admin.setups.module-manager.toggle'), [
                'module_key' => 'admissions',
                'is_active' => false,
            ])
            ->assertOk();

        $this->actingAs($staff)
            ->get(route('admissions.index'))
            ->assertStatus(503);
    }

    public function test_staff_dashboard_hides_disabled_module_nav(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($admin)
            ->patchJson(route('admin.setups.module-manager.toggle'), [
                'module_key' => 'reports',
                'is_active' => false,
            ])
            ->assertOk();

        $this->actingAs($staff)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('>Reports</span>', false);
    }
}
