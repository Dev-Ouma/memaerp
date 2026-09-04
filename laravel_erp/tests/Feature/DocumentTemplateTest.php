<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DocumentTemplateTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_document_templates_hub_renders_successfully(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.setups.document-templates'));
        $response->assertOk();
        $response->assertSee('Institutional Document Templates &amp; Live Generation', false);
        $response->assertSee('Official University Admission Letter');
        $response->assertSee('FORM MUC/ADM/01');
        $response->assertSee('FORM MUC/MED/02');
        $response->assertSee('MUC/FIN/SCH/2026');
    }

    public function test_admission_letter_preview_renders_matching_institutional_template(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.setups.document-templates.preview', ['templateKey' => 'admission_letter']));
        $response->assertOk();
        $response->assertSee('MEMA UNIVERSITY COLLEGE');
        $response->assertSee('Office of the Deputy Vice-Chancellor');
        $response->assertSee('RE: ADMISSION INTO');
        $response->assertSee('TUITION FEES');
        $response->assertSee('FEE PAYMENT');
        $response->assertSee('COMMENCEMENT DATE');
        $response->assertSee('OTHER IMPORTANT INFORMATION');
        $response->assertSee('ACADEMIC REGISTRAR');
    }

    public function test_all_registered_document_templates_preview_cleanly(): void
    {
        $templates = ['admission_letter', 'acceptance_form', 'medical_form', 'fee_structure', 'enrolment_attestation', 'provisional_transcript'];

        foreach ($templates as $key) {
            $response = $this->actingAs($this->admin)->get(route('admin.setups.document-templates.preview', ['templateKey' => $key]));
            $response->assertOk();
            $response->assertSee('MEMA UNIVERSITY COLLEGE');
        }
    }

    public function test_document_template_pdf_endpoint_returns_download_or_html(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.setups.document-templates.pdf', ['templateKey' => 'admission_letter']));
        $response->assertOk();
    }
}
