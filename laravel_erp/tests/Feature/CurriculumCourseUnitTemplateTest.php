<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CurriculumCourseUnitTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_download_the_bulk_upload_template(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->get(route('curriculum.course-unit.template'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename="mema-course-units-template.csv"');
    }

    public function test_guests_cannot_download_the_template(): void
    {
        $this->get(route('curriculum.course-unit.template'))->assertRedirect(route('login'));
    }

    public function test_template_ships_the_expected_header_row_and_worked_examples(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $rows = $this->downloadTemplateRows($user);

        $this->assertSame([
            'unit_code',
            'unit_title',
            'department',
            'credit_hours',
            'lecture_hours',
            'practical_hours',
            'classification',
            'prerequisites',
            'description',
            'status',
        ], $rows[0]);

        $this->assertCount(5, $rows, 'Template should carry a header row plus four worked examples.');

        foreach (array_slice($rows, 1) as $sample) {
            $this->assertCount(count($rows[0]), $sample, 'Every sample row must fill every column.');
        }
    }

    public function test_a_template_row_satisfies_the_course_unit_create_contract(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $rows = $this->downloadTemplateRows($user);
        $columns = array_shift($rows);

        foreach ($rows as $sample) {
            $payload = array_combine($columns, $sample);

            $response = $this->actingAs($user)->post(route('curriculum.course-unit.store'), $payload);

            $response->assertSessionHasNoErrors();
            $response->assertRedirect(route('curriculum.course-unit'));
            $this->assertDatabaseHas('academic_course_units', [
                'unit_code' => $payload['unit_code'],
                'unit_title' => $payload['unit_title'],
                'classification' => $payload['classification'],
                'status' => $payload['status'],
            ]);
        }
    }

    public function test_course_unit_page_links_to_the_template(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->get(route('curriculum.course-unit'));

        $response->assertOk();
        $response->assertSee(route('curriculum.course-unit.template'), false);
        $response->assertSee('Download Template');
    }

    /**
     * @return list<list<string>>
     */
    private function downloadTemplateRows(User $user): array
    {
        $csv = $this->actingAs($user)->get(route('curriculum.course-unit.template'))->getContent();

        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $csv);
        rewind($stream);

        $rows = [];
        while (($row = fgetcsv($stream, 0, ',', '"', '')) !== false) {
            $rows[] = $row;
        }
        fclose($stream);

        return $rows;
    }
}
