<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\SpBill;
use App\Models\SpProvider;
use App\Models\SpTax;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ServiceProvidersDeskEndToEndTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_service_providers_desk_end_to_end(): void
    {
        $this->seedRbac();
        $officer = User::factory()->create(['role' => 'staff', 'is_active' => true]);
        $this->grantRole($officer, 'finance_officer');
        $this->actingAs($officer);

        $this->post(route('service-providers.taxes.store'), [
            'code' => 'VAT16',
            'name' => 'VAT',
            'type' => 'VAT',
            'rate' => '16',
            'status' => 'Active',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->post(route('service-providers.items.store'), [
            'code' => 'ITM-01',
            'name' => 'Printer Toner',
            'category' => 'Consumable',
            'unit_cost' => 'KES 4,500',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->post(route('service-providers.provider-groups.store'), [
            'code' => 'GRP-ICT',
            'name' => 'ICT Vendors',
            'status' => 'Active',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->post(route('service-providers.providers.store'), [
            'provider_code' => 'PRV-9001',
            'name' => 'E2E Supplies Ltd',
            'group' => 'ICT Vendors',
            'contact' => 'sales@e2e.supplies',
            'status' => 'Active',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->post(route('service-providers.vendor-approval.store'), [
            'ref' => 'VA-01',
            'name' => 'E2E Supplies Ltd',
            'kra_pin' => 'P051234567X',
            'status' => 'Pending',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->post(route('service-providers.invoice-permissions.store'), [
            'staff_name' => 'Finance Clerk',
            'policy_level' => 'HOD + Finance',
            'last_audited' => '2026-09-01',
            'status' => 'Active',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->post(route('service-providers.bills.store'), [
            'ref' => 'BILL-01',
            'vendor' => 'E2E Supplies Ltd',
            'amount' => 'KES 12,000',
            'status' => 'Unpaid',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->post(route('service-providers.payment-permissions.store'), [
            'staff_name' => 'Bursar',
            'limit_amount' => 'KES 500,000',
            'compliance' => 'CFO escalation above limit',
            'status' => 'Active',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->post(route('service-providers.payments.store'), [
            'ref' => 'PAY-01',
            'vendor' => 'E2E Supplies Ltd',
            'amount' => 'KES 12,000',
            'mode' => 'EFT',
            'status' => 'Paid',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->post(route('service-providers.debit-notes.store'), [
            'ref' => 'DN-01',
            'vendor' => 'E2E Supplies Ltd',
            'amount' => 'KES 1,000',
            'reason' => 'Damaged goods',
            'status' => 'Open',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->post(route('service-providers.credit-notes.store'), [
            'ref' => 'CN-01',
            'vendor' => 'E2E Supplies Ltd',
            'amount' => 'KES 500',
            'reason' => 'Overcharge',
            'status' => 'Open',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseHas('sp_taxes', ['code' => 'VAT16']);
        $this->assertDatabaseHas('sp_providers', ['provider_code' => 'PRV-9001', 'name' => 'E2E Supplies Ltd']);
        $this->assertDatabaseHas('sp_bills', ['ref' => 'BILL-01']);

        $this->get(route('service-providers.providers'))
            ->assertOk()
            ->assertSee('E2E Supplies Ltd')
            ->assertSee('PRV-9001')
            ->assertDontSee('Safaricom PLC');
        $this->get(route('service-providers.taxes'))->assertOk()->assertSee('VAT16');
        $this->get(route('service-providers.bills'))->assertOk()->assertSee('BILL-01');
        $this->get(route('service-providers.invoice-permissions'))->assertOk()->assertSee('HOD + Finance');
    }

    public function test_service_provider_screens_render_empty_without_demo_vendors(): void
    {
        $this->seedRbac();
        $officer = User::factory()->create(['role' => 'staff', 'is_active' => true]);
        $this->grantRole($officer, 'finance_officer');

        foreach ([
            'service-providers.taxes',
            'service-providers.items',
            'service-providers.provider-groups',
            'service-providers.providers',
            'service-providers.vendor-approval',
            'service-providers.invoice-permissions',
            'service-providers.bills',
            'service-providers.payment-permissions',
            'service-providers.payments',
            'service-providers.debit-notes',
            'service-providers.credit-notes',
        ] as $route) {
            $this->actingAs($officer)->get(route($route))
                ->assertOk()
                ->assertDontSee('Safaricom PLC');
        }

        $this->assertSame(0, SpTax::query()->count());
        $this->assertSame(0, SpProvider::query()->count());
        $this->assertSame(0, SpBill::query()->count());
    }

    public function test_staff_without_service_providers_manage_cannot_write(): void
    {
        $this->seedRbac();
        $staff = User::factory()->create(['role' => 'staff', 'is_active' => true]);

        $this->actingAs($staff)->post(route('service-providers.providers.store'), [
            'provider_code' => 'DENIED',
            'name' => 'Denied Vendor',
        ])->assertForbidden();
    }
}
