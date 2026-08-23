<?php

declare(strict_types=1);

namespace Tests\Feature\Institution;

use App\Modules\Iam\Models\User;
use App\Modules\Institution\Models\AcademicYear;
use App\Modules\Institution\Models\MasterLookup;
use App\Modules\Institution\Models\Term;
use App\Modules\Institution\Notifications\TermActivatedNotification;
use App\Platform\Support\Scope;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

final class InstitutionMasterDataTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();
        Notification::fake();
        $this->admin = $this->userWithRole('system-admin', Scope::institution());
        $this->token = $this->admin->createToken('institution-tests')->plainTextToken;
    }

    public function test_lists_complete_institution_hierarchy_with_search_and_pagination(): void
    {
        $this->authorized()->getJson('/api/v1/institution/campuses?search=main')
            ->assertOk()
            ->assertJsonPath('data.0.code', 'MAIN')
            ->assertJsonPath('meta.total', 1);

        $this->authorized()->getJson('/api/v1/institution/faculties')
            ->assertOk()
            ->assertJsonFragment(['code' => 'FSCI']);

        $this->authorized()->getJson('/api/v1/institution/departments?search=computer')
            ->assertOk()
            ->assertJsonPath('data.0.code', 'CS')
            ->assertJsonPath('data.0.faculty.code', 'FSCI');
    }

    public function test_authorized_operator_can_create_an_approved_campus(): void
    {
        $this->authorized()->postJson('/api/v1/institution/campuses', [
            'code' => 'KSM',
            'name' => 'Kisumu Learning Centre',
            'town' => 'Kisumu',
            'status' => 'ACTIVE',
            'resolution_reference' => 'COUNCIL-2026-017',
        ])->assertCreated()
            ->assertJsonPath('data.status', 'ACTIVE');

        $this->assertDatabaseHas('institution.campuses', [
            'institution_id' => $this->institution->id,
            'code' => 'KSM',
            'is_active' => true,
        ]);
    }

    public function test_unprivileged_user_cannot_modify_structure(): void
    {
        $user = $this->userWithNoRoles();
        $token = $user->createToken('denied')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/institution/campuses', ['code' => 'DENIED', 'name' => 'Denied'])
            ->assertForbidden();
    }

    public function test_creates_and_activates_an_academic_year_and_term_end_to_end(): void
    {
        $yearResponse = $this->authorized()->postJson('/api/v1/institution/academic-years', [
            'code' => '2028/2029',
            'name' => 'Academic Year 2028/2029',
            'starts_on' => '2028-09-01',
            'ends_on' => '2029-08-31',
        ])->assertCreated();
        $yearId = $yearResponse->json('data.id');

        $termResponse = $this->authorized()->postJson('/api/v1/institution/terms', [
            'academic_year_id' => $yearId,
            'study_mode_code' => 'FULL_TIME',
            'code' => '2028/2029-S1',
            'name' => 'Semester 1',
            'sequence' => 1,
            'term_type' => 'SEMESTER',
            'starts_on' => '2028-09-04',
            'ends_on' => '2028-12-15',
            'registration_opens_at' => '2028-08-20T08:00:00+03:00',
            'registration_closes_at' => '2028-09-15T23:59:59+03:00',
        ])->assertCreated();
        $termId = $termResponse->json('data.id');

        $this->authorized()->postJson("/api/v1/institution/academic-years/{$yearId}/activate", [
            'senate_resolution_reference' => 'SENATE-2028-044',
        ])->assertOk()->assertJsonPath('data.status', 'ACTIVE');

        $this->authorized()->postJson("/api/v1/institution/terms/{$termId}/activate")
            ->assertOk()->assertJsonPath('data.status', 'ACTIVE');
        Notification::assertSentTo($this->admin, TermActivatedNotification::class);

        $this->authorized()->getJson('/api/v1/institution/academic-years/current')
            ->assertOk()
            ->assertJsonPath('data.code', '2028/2029')
            ->assertJsonPath('data.terms.0.code', '2028/2029-S1');

        self::assertSame(1, AcademicYear::query()->where('institution_id', $this->institution->id)->where('is_current', true)->count());
        self::assertSame(1, Term::query()->where('institution_id', $this->institution->id)->where('study_mode_code', 'FULL_TIME')->where('is_current', true)->count());
    }

    public function test_rejects_overlapping_active_terms_for_the_same_study_mode(): void
    {
        $year = AcademicYear::query()->where('institution_id', $this->institution->id)->current()->firstOrFail();
        $first = Term::query()->where('institution_id', $this->institution->id)->where('is_current', true)->firstOrFail();
        $first->forceFill(['status' => 'ACTIVE'])->save();

        $overlap = Term::query()->create([
            'institution_id' => $this->institution->id,
            'academic_year_id' => $year->id,
            'study_mode_code' => 'FULL_TIME',
            'code' => 'OVERLAP-S1',
            'name' => 'Overlapping Semester',
            'sequence' => 3,
            'term_type' => 'SEMESTER',
            'starts_on' => $first->starts_on->addWeek(),
            'ends_on' => $first->ends_on->subWeek(),
            'status' => 'DRAFT',
            'is_current' => false,
        ]);

        $this->authorized()->postJson("/api/v1/institution/terms/{$overlap->id}/activate")
            ->assertUnprocessable()
            ->assertJsonPath('error.fields.starts_on.0', 'ERR-CAL-001: This term overlaps an active term for the same study mode.');
    }

    public function test_master_lookups_are_cached_and_invalidated_on_write(): void
    {
        $key = "institution:{$this->institution->id}:lookups:nationality";
        Cache::forget($key);

        $cached = $this->authorized()->getJson('/api/v1/institution/lookups/nationality')
            ->assertOk()
            ->assertJsonFragment(['code' => 'KE']);
        self::assertLessThan(50, (float) $cached->json('meta.elapsed_ms'));
        self::assertTrue(Cache::has($key));

        $this->authorized()->postJson('/api/v1/institution/lookups/nationality', [
            'code' => 'RW', 'name' => 'Rwandan',
        ])->assertCreated();
        self::assertFalse(Cache::has($key));
        self::assertTrue(MasterLookup::query()->where('type', 'NATIONALITY')->where('code', 'RW')->exists());
    }

    public function test_governed_hierarchy_supports_units_updates_and_archive_instead_of_delete(): void
    {
        $department = $this->authorized()->getJson('/api/v1/institution/departments')->json('data.0');
        $unit = $this->authorized()->postJson('/api/v1/institution/units', [
            'department_id' => $department['id'],
            'code' => 'AI-LAB',
            'name' => 'Artificial Intelligence Laboratory',
            'type' => 'CENTRE',
            'status' => 'PENDING_APPROVAL',
        ])->assertCreated()->json('data');

        $this->authorized()->patchJson("/api/v1/institution/units/{$unit['id']}", [
            'status' => 'ACTIVE',
            'resolution_reference' => 'SENATE-2026-071',
        ])->assertOk()->assertJsonPath('data.is_active', true);

        $this->authorized()->patchJson("/api/v1/institution/units/{$unit['id']}", ['status' => 'ARCHIVED'])
            ->assertOk()->assertJsonPath('data.is_active', false);

        $this->authorized()->getJson('/api/v1/institution/units?search=artificial')
            ->assertOk()->assertJsonPath('data.0.status', 'ARCHIVED');
    }

    public function test_configures_study_modes_intakes_and_critical_calendar_events(): void
    {
        $year = AcademicYear::query()->where('institution_id', $this->institution->id)->current()->firstOrFail();
        $mode = $this->authorized()->postJson('/api/v1/institution/study-modes', [
            'code' => 'BLOCK_RELEASE', 'name' => 'Block Release',
        ])->assertCreated()->json('data');
        $this->authorized()->patchJson("/api/v1/institution/study-modes/{$mode['id']}", ['is_active' => false])
            ->assertOk()->assertJsonPath('data.is_active', false);

        $intake = $this->authorized()->postJson('/api/v1/institution/intakes', [
            'academic_year_id' => $year->id,
            'code' => 'MAY-2027',
            'name' => 'May 2027 Intake',
            'opens_on' => '2026-10-01',
            'closes_on' => '2027-04-15',
            'reporting_on' => '2027-05-03',
            'status' => 'ACTIVE',
        ])->assertCreated()->json('data');
        $this->authorized()->patchJson("/api/v1/institution/intakes/{$intake['id']}", ['status' => 'ARCHIVED'])
            ->assertOk()->assertJsonPath('data.status', 'ARCHIVED');

        $this->authorized()->postJson('/api/v1/institution/calendar-events', [
            'academic_year_id' => $year->id,
            'event_type' => 'DEADLINE',
            'title' => 'Final fee payment deadline',
            'starts_at' => '2026-09-18T17:00:00+03:00',
            'is_critical' => true,
        ])->assertCreated()->assertJsonPath('data.is_critical', true);

        $this->authorized()->getJson("/api/v1/institution/calendar-events?academic_year_id={$year->id}")
            ->assertOk()->assertJsonFragment(['title' => 'Final fee payment deadline']);
    }

    public function test_exports_governed_directory_and_calendar_reports(): void
    {
        $this->authorized()->get('/api/v1/institution/reports/directory?format=json')
            ->assertOk()->assertJsonFragment(['code' => 'MAIN']);

        $csv = $this->authorized()->get('/api/v1/institution/reports/directory?format=csv')
            ->assertOk()->assertDownload('institutional-directory.csv');
        self::assertStringContainsString('campus_code,campus_name', $csv->streamedContent());

        $directoryPdf = $this->authorized()->get('/api/v1/institution/reports/directory?format=pdf')
            ->assertOk()->assertDownload('institutional-directory.pdf');
        $directoryContent = $directoryPdf->getContent();
        self::assertIsString($directoryContent);
        self::assertStringStartsWith('%PDF', $directoryContent);

        $calendarPdf = $this->authorized()->get('/api/v1/institution/reports/calendar')
            ->assertOk();
        self::assertSame('application/pdf', $calendarPdf->headers->get('content-type'));
        $calendarContent = $calendarPdf->getContent();
        self::assertIsString($calendarContent);
        self::assertStringStartsWith('%PDF', $calendarContent);
    }

    private function authorized(): static
    {
        return $this->withHeader('Authorization', 'Bearer '.$this->token);
    }
}
