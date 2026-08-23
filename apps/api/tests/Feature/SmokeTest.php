<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

/**
 * The cheapest possible check that the application boots: routes resolve, providers register,
 * middleware runs. When every other test fails at once, this one says whether the cause is a
 * broken behaviour or a broken container.
 */
final class SmokeTest extends TestCase
{
    public function test_the_api_root_lists_portal_entry_points(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertJsonPath('system', 'MEMA ERP API')
            ->assertJsonPath('portals.admin', 'http://localhost:3005/login');
    }

    public function test_the_health_endpoint_responds(): void
    {
        $this->get('/up')->assertStatus(200);
    }
}
