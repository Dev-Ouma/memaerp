<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AdmissionIntake;
use App\Models\Course;
use App\Models\ProgrammeOffering;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class LegalAndCookieConsentTest extends TestCase
{
    use RefreshDatabase;

    public function test_terms_page_loads_with_full_regulations_and_pdf_export(): void
    {
        $response = $this->get(route('legal.terms'));

        $response->assertOk();
        $response->assertSee('Terms &amp; Conditions of Admission &amp; Enrolment', false);
        $response->assertSee('Republic of Kenya');
        $response->assertSee('Universities Act');
        $response->assertSee('Export PDF / Print');
        $response->assertSee('522 522');
        $response->assertSee('0113636154');
    }

    public function test_terms_pdf_export_route_works(): void
    {
        $response = $this->get(route('legal.terms.pdf'));
        $response->assertOk();
        $response->assertSee('Terms &amp; Conditions of Admission &amp; Enrolment', false);
    }

    public function test_privacy_policy_page_loads_with_kdpa_compliance(): void
    {
        $response = $this->get(route('legal.privacy'));

        $response->assertOk();
        $response->assertSee('Institutional Privacy &amp; Data Protection Policy', false);
        $response->assertSee('Kenya Data Protection Act');
        $response->assertSee('Office of the Data Protection Commissioner');
        $response->assertSee('dpo@mema.ac.ke');
        $response->assertSee('Export PDF / Print');
    }

    public function test_privacy_pdf_export_route_works(): void
    {
        $response = $this->get(route('legal.privacy.pdf'));
        $response->assertOk();
        $response->assertSee('Institutional Privacy &amp; Data Protection Policy', false);
    }

    public function test_cookie_policy_page_loads_with_preference_controls(): void
    {
        $response = $this->get(route('legal.cookies'));

        $response->assertOk();
        $response->assertSee('Institutional Cookie Policy');
        $response->assertSee('Strictly Necessary Cookies');
        $response->assertSee('Manage Cookie Preferences');
    }

    public function test_apply_page_has_valid_terms_and_privacy_links_and_cookie_banner(): void
    {
        $course = Course::create(['code' => 'CS101', 'name' => 'Computer Science']);
        $intake = AdmissionIntake::create([
            'code' => 'SEP-2026',
            'name' => 'September 2026 Intake',
            'opens_at' => '2026-06-01',
            'closes_at' => '2026-09-20',
            'acceptance_deadline' => '2026-09-30',
            'is_published' => true,
        ]);
        $offering = ProgrammeOffering::create([
            'course_id' => $course->id,
            'admission_intake_id' => $intake->id,
            'campus' => 'Main Campus',
            'study_mode' => 'Full-time',
            'capacity' => 100,
            'application_fee' => 1000,
            'is_published' => true,
        ]);

        $response = $this->get(route('admissions.apply', $offering));

        $response->assertOk();
        $response->assertSee(route('legal.terms'));
        $response->assertSee(route('legal.privacy'));
        $response->assertSee('mema-cookie-banner');
        $response->assertSee('Cookie &amp; Privacy Choices', false);
    }
}
