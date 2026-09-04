<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CurriculumSchoolCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->seedRbac();
    }

    public function test_authenticated_user_can_view_schools_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('curriculum.school'));

        $response->assertOk();
        $response->assertSee('Academic School & Faculty Setup', false);
        $response->assertSee('Create School');
    }

    public function test_admin_can_create_school(): void
    {
        $response = $this->actingAs($this->admin)->post(route('curriculum.school.store'), [
            'code' => 'SCH-LAW',
            'name' => 'School of Law and Legal Studies',
            'dean' => 'Prof. Githu Muigai',
            'departments_count' => 2,
            'programmes_count' => 4,
            'email' => 'dean.law@mema.ac.ke',
            'phone' => '+254 700 999 111',
            'building' => 'Justice Chambers, Block B',
            'description' => 'Commercial Law, Constitutional Law, Public International Law.',
            'status' => 'Active',
        ]);

        $response->assertRedirect(route('curriculum.school'));
        $this->assertDatabaseHas('schools', [
            'code' => 'SCH-LAW',
            'name' => 'School of Law and Legal Studies',
        ]);
        $this->get(route('curriculum.school'))
            ->assertOk()
            ->assertSee('SCH-LAW')
            ->assertSee('School of Law and Legal Studies');
    }

    public function test_admin_can_create_school_without_optional_contact_fields(): void
    {
        $response = $this->actingAs($this->admin)->post(route('curriculum.school.store'), [
            'code' => 'SCH-MIN',
            'name' => 'Minimal School',
            'status' => 'Active',
        ]);

        $response->assertRedirect(route('curriculum.school'));
        $this->assertDatabaseHas('schools', [
            'code' => 'SCH-MIN',
            'name' => 'Minimal School',
            'email' => null,
            'phone' => null,
            'building' => null,
            'description' => null,
        ]);
    }

    public function test_admin_can_create_academic_year_without_description(): void
    {
        $response = $this->actingAs($this->admin)->post(route('cohort.academic-year.store'), [
            'name' => '2027/2028',
            'code' => 'AY2728',
            'start_date' => '2027-09-01',
            'end_date' => '2028-08-31',
            'status' => 'Upcoming',
        ]);

        $response->assertRedirect(route('cohort.academic-year'));
        $this->assertDatabaseHas('cohort_academic_years', [
            'code' => 'AY2728',
            'name' => '2027/2028',
            'description' => null,
        ]);
    }

    public function test_admin_can_update_school(): void
    {
        $school = School::create([
            'code' => 'SCH-TMP',
            'name' => 'Temporary School',
            'dean' => 'Dr. Initial Dean',
            'status' => 'Active',
        ]);

        $response = $this->actingAs($this->admin)->put(route('curriculum.school.update', $school), [
            'code' => 'SCH-TMP-UPD',
            'name' => 'Updated School Name',
            'dean' => 'Prof. Updated Dean',
            'departments_count' => 5,
            'programmes_count' => 10,
            'email' => 'updated@mema.ac.ke',
            'phone' => '+254 711 222 333',
            'building' => 'New Wing',
            'description' => 'Updated Description',
            'status' => 'Active',
        ]);

        $response->assertRedirect(route('curriculum.school'));
        $this->assertDatabaseHas('schools', [
            'id' => $school->id,
            'code' => 'SCH-TMP-UPD',
            'name' => 'Updated School Name',
            'dean' => 'Prof. Updated Dean',
        ]);
    }

    public function test_admin_can_delete_school(): void
    {
        $school = School::create([
            'code' => 'SCH-DEL',
            'name' => 'School to Delete',
            'status' => 'Active',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('curriculum.school.destroy', $school), [
            'deletion_reason' => 'School was created for an integration test.',
        ]);

        $response->assertRedirect(route('curriculum.school'));
        $this->assertSoftDeleted('schools', [
            'id' => $school->id,
        ]);
        $this->assertDatabaseHas('deletion_records', [
            'entity_type' => 'school',
            'record_id' => (string) $school->id,
            'deleted_by' => $this->admin->id,
            'status' => 'deleted',
        ]);
    }
}
