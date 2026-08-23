<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Course\Models\Course;
use App\Modules\Curriculum\Models\Programme;
use App\Modules\Iam\Models\User;
use App\Modules\Institution\Models\Department;
use Database\Seeders\CurriculumAndCourseSeeder;
use Database\Seeders\DemoUserSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

final class CurriculumAndCourseTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();
        $this->seed(DemoUserSeeder::class);
        $this->seed(CurriculumAndCourseSeeder::class);

        $this->admin = User::query()->where('email', 'admin@mema.ac.ke')->firstOrFail();
        $this->token = $this->admin->createToken('test-suite')->plainTextToken;
    }

    public function test_can_list_programmes(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/v1/curriculum/programmes');

        $response->assertStatus(200);
        $response->assertJsonFragment(['code' => 'BSC-CS']);
    }

    public function test_can_fetch_programme_curriculum_tree(): void
    {
        $programme = Programme::query()->where('code', 'BSC-CS')->firstOrFail();

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/v1/curriculum/programmes/'.$programme->id);

        $response->assertStatus(200);
        $response->assertJsonPath('data.code', 'BSC-CS');
        $response->assertJsonPath('data.versions.0.version_code', '2026-V1');
    }

    public function test_can_create_new_programme(): void
    {
        $dept = Department::query()->where('code', 'CS')->firstOrFail();

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson('/api/v1/curriculum/programmes', [
                'department_id' => $dept->id,
                'code' => 'BIT',
                'name' => 'Bachelor of Information Technology',
                'award_level' => 'BACHELORS',
                'duration_years' => 4,
                'total_credits_required' => 120,
            ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.code', 'BIT');
    }

    public function test_can_list_master_courses_catalogue(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/v1/courses');

        $response->assertStatus(200);
        $response->assertJsonFragment(['code' => 'CSC 101']);
        $response->assertJsonFragment(['code' => 'CSC 201']);
    }

    public function test_can_fetch_course_with_prerequisites(): void
    {
        $course = Course::query()->where('code', 'CSC 201')->firstOrFail();

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/v1/courses/'.$course->id);

        $response->assertStatus(200);
        $response->assertJsonPath('data.code', 'CSC 201');
        $response->assertJsonPath('data.prerequisites.0.prerequisite_course.code', 'CSC 102');
    }

    public function test_can_fetch_active_semester_offerings(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/v1/courses/offerings/active');

        $response->assertStatus(200);
        $response->assertJsonFragment(['section_code' => 'A']);
    }
}
