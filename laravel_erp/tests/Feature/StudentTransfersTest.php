<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class StudentTransfersTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_exemptions_page(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->get(route('transfers.exemptions'));

        $response->assertOk();
        $response->assertSee('Exemptions');
        $response->assertSee('View and manage student exemptions requests');
        $response->assertSee('Total Requests');
        $response->assertSee('1,683');
        $response->assertSee('Pending Approvals');
        $response->assertSee('158 unassigned');
        $response->assertSee('DANIEL KIBET');
        $response->assertSee('BE02/33013/2025');
        $response->assertSee('ECO 101 - Introduction to Microeconomics');
        $response->assertSee('INSTRUCTOR SENTBACK');
        $response->assertSee('DELANY MUKARIA');
        $response->assertSee('APPROVED');
    }

    public function test_authenticated_user_can_view_transfer_subpages(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)->get(route('transfers.dates-setup'))->assertOk()->assertSee('Transfer Dates Setup');
        $this->actingAs($user)->get(route('transfers.inter-intra'))->assertOk()->assertSee('Inter/Intra Faculty Transfers');
        $this->actingAs($user)->get(route('transfers.credit-transfers'))->assertOk()->assertSee('Credit Transfers');
    }
}
