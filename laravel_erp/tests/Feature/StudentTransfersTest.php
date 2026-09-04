<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\CourseExemption;
use App\Models\CreditTransfer;
use App\Models\FacultyTransfer;
use App\Models\TransferWindow;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class StudentTransfersTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_transfers_desk_end_to_end(): void
    {
        $this->seedRbac();
        $officer = User::factory()->create(['role' => 'staff', 'is_active' => true, 'name' => 'Transfers Officer']);
        $this->grantRole($officer, 'transfers_officer');
        $this->actingAs($officer);

        $this->post(route('transfers.dates.store'), [
            'type' => 'Inter/Intra Faculty',
            'academic_year' => '2026/2027',
            'notification_date' => '2026-09-01',
            'start_date' => '2026-09-15',
            'end_date' => '2026-10-15',
            'status' => 'Open',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->post(route('transfers.inter-intra.store'), [
            'name' => 'E2E Transfer Scholar',
            'email' => 'e2e.transfer@student.mema.ac.ke',
            'reg_no' => 'BCS/TR/2026',
            'type' => 'Inter',
            'current_programme' => 'BSc IT',
            'transfer_programme' => 'BSc Computer Science',
            'reason' => 'Programme alignment',
            'status' => 'Pending',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->post(route('transfers.credits.store'), [
            'name' => 'E2E Transfer Scholar',
            'admission_number' => 'BCS/TR/2026',
            'course_code' => 'CS101',
            'course_name' => 'Intro Computing',
            'programme_code' => 'BSCS',
            'programme_name' => 'BSc Computer Science',
            'prior_institution' => 'Konza Tech University',
            'credits' => 3,
            'status' => 'Pending',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->post(route('transfers.exemptions.store'), [
            'name' => 'E2E Transfer Scholar',
            'admission_number' => 'BCS/TR/2026',
            'course_code' => 'MATH100',
            'course_name' => 'Foundation Maths',
            'programme_code' => 'BSCS',
            'programme_name' => 'BSc Computer Science',
            'status' => 'Pending',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseHas('transfer_windows', [
            'type' => 'Inter/Intra Faculty',
            'academic_year' => '2026/2027',
            'status' => 'Open',
        ]);
        $this->assertDatabaseHas('faculty_transfers', [
            'name' => 'E2E Transfer Scholar',
            'reg_no' => 'BCS/TR/2026',
            'status' => 'Pending',
        ]);
        $this->assertDatabaseHas('credit_transfers', [
            'admission_number' => 'BCS/TR/2026',
            'course_code' => 'CS101',
            'status' => 'Pending',
        ]);
        $this->assertDatabaseHas('course_exemptions', [
            'admission_number' => 'BCS/TR/2026',
            'course_code' => 'MATH100',
            'status' => 'Pending',
        ]);

        $faculty = FacultyTransfer::query()->firstOrFail();
        $credit = CreditTransfer::query()->firstOrFail();
        $exemption = CourseExemption::query()->firstOrFail();

        $this->patch(route('transfers.inter-intra.status', $faculty), ['status' => 'Approved'])
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->patch(route('transfers.credits.status', $credit), [
            'status' => 'Approved',
            'status_type' => 'approved',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->patch(route('transfers.exemptions.status', $exemption), ['status' => 'Approved'])
            ->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame('Approved', $faculty->fresh()->status);
        $this->assertSame('Approved', $credit->fresh()->status);
        $this->assertSame('Approved', $exemption->fresh()->status);

        $this->get(route('transfers.dates-setup'))
            ->assertOk()
            ->assertSee('Inter/Intra Faculty')
            ->assertSee('2026/2027')
            ->assertDontSee('DANIEL KIBET');
        $this->get(route('transfers.inter-intra'))
            ->assertOk()
            ->assertSee('E2E Transfer Scholar')
            ->assertSee('BCS/TR/2026')
            ->assertDontSee('1,683');
        $this->get(route('transfers.credit-transfers'))
            ->assertOk()
            ->assertSee('E2E Transfer Scholar')
            ->assertSee('CS101')
            ->assertSee('Intro Computing');
        $this->get(route('transfers.exemptions'))
            ->assertOk()
            ->assertSee('MATH100')
            ->assertSee('Foundation Maths')
            ->assertDontSee('DANIEL KIBET')
            ->assertDontSee('1,683');
    }

    public function test_transfers_screens_render_empty_without_demo_names(): void
    {
        $this->seedRbac();
        $officer = User::factory()->create(['role' => 'staff', 'is_active' => true]);
        $this->grantRole($officer, 'transfers_officer');

        foreach ([
            'transfers.dates-setup',
            'transfers.inter-intra',
            'transfers.credit-transfers',
            'transfers.exemptions',
        ] as $route) {
            $this->actingAs($officer)->get(route($route))
                ->assertOk()
                ->assertDontSee('DANIEL KIBET')
                ->assertDontSee('1,683');
        }

        $this->assertSame(0, TransferWindow::query()->count());
        $this->assertSame(0, FacultyTransfer::query()->count());
        $this->assertSame(0, CreditTransfer::query()->count());
        $this->assertSame(0, CourseExemption::query()->count());
    }

    public function test_staff_without_transfers_manage_cannot_write(): void
    {
        $this->seedRbac();
        $staff = User::factory()->create(['role' => 'staff', 'is_active' => true]);

        $this->actingAs($staff)->post(route('transfers.exemptions.store'), [
            'name' => 'Denied Scholar',
            'admission_number' => 'DENY/1',
            'course_code' => 'X1',
            'status' => 'Pending',
        ])->assertForbidden();

        $this->assertDatabaseMissing('course_exemptions', ['admission_number' => 'DENY/1']);
    }
}
