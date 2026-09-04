<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Student;
use App\Models\User;
use App\Models\UserStakeholderType;
use App\Services\AcademicYearService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ErpWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_sees_login_and_authenticated_user_sees_dashboard(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Sign In')
            ->assertSee('Email or Username')
            ->assertSee('Forgot password?')
            ->assertSee('Create Applicant Account / Sign Up')
            ->assertSee('data-processing-message="Signing you in securely…"', false);
        $user = User::factory()->create(['role' => 'admin', 'first_name' => 'Admin', 'is_active' => true]);
        $this->actingAs($user)->get('/dashboard')->assertOk()->assertSee('Application Overview');
    }

    public function test_dashboard_handles_empty_attendance_without_dividing_by_zero(): void
    {
        $user = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('0%')
            ->assertSee('0 YTD cycle')
            ->assertDontSee('18,267 YTD Cycle')
            ->assertDontSee('+18.4% YoY');
    }

    public function test_admin_can_create_a_course_and_student(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->actingAs($admin)->post('/courses', ['name' => 'Computer Science', 'code' => 'CS', 'next_student_serial' => 1])->assertSessionHasNoErrors();
        $course = Course::where('code', 'CS')->firstOrFail();
        $this->actingAs($admin)->post('/students', ['first_name' => 'Jane', 'last_name' => 'Doe', 'course_id' => $course->id])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('students', ['course_id' => $course->id]);
        $this->assertDatabaseHas('students', ['admission_number' => 'CS/001/'.now()->format('Y')]);
        $this->assertDatabaseHas('users', ['email' => 'cs001'.now()->format('Y').'@student.mema.ac.ke', 'role' => 'student']);
        $this->assertSame(2, $course->fresh()->next_student_serial);
        $this->get(route('students.index'))
            ->assertOk()
            ->assertSee('Jane Doe')
            ->assertSee('Computer Science');
    }

    public function test_each_stakeholder_receives_their_own_dashboard(): void
    {
        foreach ([
            'admin' => 'College-wide operations',
            'staff' => 'Your teaching allocation',
            'student' => 'Your course progress',
            'parent' => 'linked learner',
        ] as $role => $dashboardCopy) {
            $user = User::factory()->create(['role' => $role, 'first_name' => ucfirst($role), 'is_active' => true]);
            $this->actingAs($user)->get('/dashboard')->assertOk()->assertSee($dashboardCopy);
        }
    }

    public function test_admin_can_impersonate_and_return_to_their_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $student = User::factory()->create(['role' => 'student', 'first_name' => 'Learner', 'is_active' => true]);
        Student::create(['user_id' => $student->id, 'admission_number' => 'TEST/001']);

        $this->actingAs($admin)
            ->post(route('impersonate.start', $student))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('impersonator_id', $admin->id);
        $this->assertAuthenticatedAs($student);

        $this->post(route('impersonate.stop'))->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_non_admin_cannot_impersonate_another_user(): void
    {
        $teacher = User::factory()->create(['role' => 'staff', 'is_active' => true]);
        $student = User::factory()->create(['role' => 'student', 'is_active' => true]);

        $this->actingAs($teacher)->post(route('impersonate.start', $student))->assertForbidden();
        $this->assertAuthenticatedAs($teacher);
    }

    public function test_academic_year_rolls_over_on_first_september(): void
    {
        $service = app(AcademicYearService::class);
        $beforeRollover = $service->current(CarbonImmutable::parse('2026-08-31'));
        $afterRollover = $service->current(CarbonImmutable::parse('2026-09-01'));

        $this->assertSame('2025-09-01', $beforeRollover->start_date->toDateString());
        $this->assertSame('2026-08-31', $beforeRollover->end_date->toDateString());
        $this->assertSame('2026-09-01', $afterRollover->start_date->toDateString());
        $this->assertSame('2027-08-31', $afterRollover->end_date->toDateString());
    }

    public function test_profile_menu_pages_and_preferences_are_integrated_into_the_main_app(): void
    {
        $user = User::factory()->create(['role' => 'admin', 'first_name' => 'Admin', 'is_active' => true]);

        $this->actingAs($user)->get('/dashboard')->assertOk()->assertSee('Open account menu for')->assertSee('Preferences');
        $this->actingAs($user)->get('/account/profile')->assertOk()->assertSee('My account');
        $this->actingAs($user)->put('/account/preferences', [
            'language' => 'sw', 'timezone' => 'Africa/Nairobi', 'email_notifications' => '1', 'theme' => 'dark',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('user_preferences', ['user_id' => $user->id, 'language' => 'sw', 'theme' => 'dark']);
        $this->assertDatabaseHas('audit_logs', ['actor_user_id' => $user->id, 'action' => 'preferences.updated']);
    }

    public function test_role_switch_is_limited_to_existing_stakeholder_relationships(): void
    {
        $user = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        UserStakeholderType::create(['user_id' => $user->id, 'stakeholder_type' => 'staff', 'is_active' => true]);

        $this->actingAs($user)->post('/account/switch-role', ['stakeholder_type' => 'staff'])
            ->assertRedirect(route('dashboard'))->assertSessionHas('active_stakeholder_type', 'staff');
        $this->actingAs($user)->post('/account/switch-role', ['stakeholder_type' => 'student'])->assertNotFound();
        $this->assertDatabaseHas('audit_logs', ['actor_user_id' => $user->id, 'action' => 'session.role_switched']);
    }
}
