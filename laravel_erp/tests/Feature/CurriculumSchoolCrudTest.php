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

    public function test_authenticated_user_can_view_schools_page(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->get(route('curriculum.school'));

        $response->assertOk();
        $response->assertSee('Academic School & Faculty Setup', false);
        $response->assertSee('Create School');
    }

    public function test_admin_can_create_school(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->post(route('curriculum.school.store'), [
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

    public function test_admin_can_update_school(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $school = School::create([
            'code' => 'SCH-TMP',
            'name' => 'Temporary School',
            'dean' => 'Dr. Initial Dean',
            'status' => 'Active',
        ]);

        $response = $this->actingAs($user)->put(route('curriculum.school.update', $school), [
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
        $user = User::factory()->create(['role' => 'admin']);
        $school = School::create([
            'code' => 'SCH-DEL',
            'name' => 'School to Delete',
            'status' => 'Active',
        ]);

        $response = $this->actingAs($user)->delete(route('curriculum.school.destroy', $school), [
            'deletion_reason' => 'School was created for an integration test.',
        ]);

        $response->assertRedirect(route('curriculum.school'));
        $this->assertSoftDeleted('schools', [
            'id' => $school->id,
        ]);
        $this->assertDatabaseHas('deletion_records', [
            'entity_type' => 'school',
            'record_id' => (string) $school->id,
            'deleted_by' => $user->id,
            'status' => 'deleted',
        ]);
    }
}
