<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\SystemMaintenanceConfig;
use App\Models\SystemVersion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SystemMaintenanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_view_maintenance_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'staff']);

        $response = $this->actingAs($user)->get(route('admin.setups.system-maintenance.index'));

        $response->assertForbidden();
    }

    public function test_admin_can_view_maintenance_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        // Insert initial setup version so query doesn't fail
        SystemVersion::create([
            'version' => '1.0.0',
            'type' => 'major',
            'changelog' => 'Initial bootstrap of the ERP.',
            'installed_at' => now(),
        ]);
        SystemMaintenanceConfig::query()->updateOrCreate([], [
            'maintenance_message' => 'Database-backed maintenance message',
        ]);

        $response = $this->actingAs($user)->get(route('admin.setups.system-maintenance.index'));

        $response->assertOk();
        $response->assertSee('MEMA <span class="text-[#00f2fe]">OpsCenter</span>', false);
        $response->assertSee('Database-backed maintenance message');
    }

    public function test_admin_can_update_lockdown_configurations(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->post(route('admin.setups.system-maintenance.lockdown.update'), [
            'is_lockdown' => '1',
            'lockdown_type' => 'read_only',
            'ip_whitelist' => '127.0.0.1,10.0.0.1',
            'maintenance_message' => 'System is currently restricted.',
            'locked_modules' => ['registration'],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('system_maintenance_configs', [
            'is_lockdown' => true,
            'lockdown_type' => 'read_only',
            'ip_whitelist' => '127.0.0.1,10.0.0.1',
            'maintenance_message' => 'System is currently restricted.',
        ]);
    }

    public function test_lockdown_blocks_non_whitelisted_ips(): void
    {
        // Setup lockdown
        $config = SystemMaintenanceConfig::first();
        if (! $config) {
            $config = new SystemMaintenanceConfig;
        }
        $config->is_lockdown = true;
        $config->lockdown_type = 'offline';
        $config->ip_whitelist = '192.168.1.50';
        $config->maintenance_message = 'Scheduled database upgrade.';
        $config->save();

        // Create standard staff user
        $staff = User::factory()->create(['role' => 'staff']);

        // Attempt access from non-whitelisted IP
        $response = $this->actingAs($staff)->withServerVariables(['REMOTE_ADDR' => '200.50.25.12'])->get(route('dashboard'));

        $response->assertStatus(503);
        $response->assertSee('Scheduled database upgrade.');

        // Attempt access from whitelisted IP
        $response2 = $this->actingAs($staff)->withServerVariables(['REMOTE_ADDR' => '192.168.1.50'])->get(route('dashboard'));

        $response2->assertOk();
    }

    public function test_lockdown_blocks_write_requests_in_read_only_mode(): void
    {
        // Setup read-only lockdown
        $config = SystemMaintenanceConfig::first();
        if (! $config) {
            $config = new SystemMaintenanceConfig;
        }
        $config->is_lockdown = true;
        $config->lockdown_type = 'read_only';
        $config->ip_whitelist = '127.0.0.1';
        $config->save();

        $staff = User::factory()->create(['role' => 'staff']);

        // GET request is allowed
        $this->actingAs($staff)->withServerVariables(['REMOTE_ADDR' => '192.168.1.50'])->get(route('dashboard'))->assertOk();

        // POST request is blocked
        $this->actingAs($staff)->withServerVariables(['REMOTE_ADDR' => '192.168.1.50'])
            ->post(route('admin.setups.system-maintenance.broadcast'), ['message' => 'Test'])
            ->assertStatus(503);
    }

    public function test_admin_can_clear_cache(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->postJson(route('admin.setups.system-maintenance.cache.clear'), [
            'target' => 'app',
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
    }

    public function test_admin_can_trigger_backup(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->post(route('admin.setups.system-maintenance.backup.create'));

        $response->assertRedirect();
        $this->assertDatabaseCount('system_backups', 1);
    }

    public function test_admin_can_rollback_version(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $version = SystemVersion::create([
            'version' => '1.2.0',
            'type' => 'minor',
            'changelog' => 'New release test.',
            'installed_at' => now(),
        ]);

        $response = $this->actingAs($user)->post(route('admin.setups.system-maintenance.version.rollback', $version));

        $response->assertRedirect();
        $this->assertNotNull($version->fresh()->rolled_back_at);
    }
}
