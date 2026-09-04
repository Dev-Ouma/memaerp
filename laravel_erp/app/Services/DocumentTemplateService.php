<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AdmissionApplication;

final class DocumentTemplateService
{
    /**
     * Return all available institutional document templates.
     *
     * @return array<string, array{
     *     key: string,
     *     name: string,
     *     category: string,
     *     code: string,
     *     description: string,
     *     icon: string,
     *     badge: string,
     *     placeholders: list<array{key: string, label: string, example: string}>
     * }>
     */
    public function catalogue(): array
    {
        return [
            'admission_letter' => [
                'key' => 'admission_letter',
                'name' => 'Official University Admission Letter',
                'category' => 'Admissions & Intake',
                'code' => 'MUC/ADM/LETTER/01',
                'description' => 'Official letter of admission issued to admitted applicants with QR token, tuition schedule, and reporting instructions.',
                'icon' => 'mail',
                'badge' => 'CRITICAL',
                'placeholders' => [
                    ['key' => 'applicant_name', 'label' => 'Applicant Full Name', 'example' => 'Ms. Jackline Chebet'],
                    ['key' => 'admission_number', 'label' => 'Student Admission Number', 'example' => 'BCS/042/2026'],
                    ['key' => 'programme_title', 'label' => 'Degree Programme Name', 'example' => 'Bachelor of Science in Computer Science'],
                    ['key' => 'school_name', 'label' => 'Faculty / School', 'example' => 'School of Computing & Informatics'],
                    ['key' => 'academic_year', 'label' => 'Academic Year', 'example' => '2026/2027'],
                    ['key' => 'commencement_date', 'label' => 'Reporting & Commencement Date', 'example' => '15th September 2026'],
                    ['key' => 'semester_fee', 'label' => 'Semester Tuition Fee', 'example' => 'KES 48,500.00'],
                    ['key' => 'total_first_semester', 'label' => 'Total Semester 1 Payable', 'example' => 'KES 63,200.00'],
                    ['key' => 'ref_number', 'label' => 'Reference Number', 'example' => 'MUC/ADM/2026/09/8821'],
                    ['key' => 'verification_token', 'label' => 'QR Verification Hash', 'example' => 'MC-VER-77A91B82-2026'],
                ],
            ],
            'acceptance_form' => [
                'key' => 'acceptance_form',
                'name' => 'Student Acceptance & Declaration Form',
                'category' => 'Legal & Onboarding',
                'code' => 'FORM MUC/ADM/01',
                'description' => 'Mandatory legal agreement signed by the student confirming acceptance of offer and adherence to University Statutes.',
                'icon' => 'file-check',
                'badge' => 'MANDATORY',
                'placeholders' => [
                    ['key' => 'applicant_name', 'label' => 'Candidate Name', 'example' => 'Ms. Jackline Chebet'],
                    ['key' => 'national_id', 'label' => 'National ID / Passport', 'example' => '38491029'],
                    ['key' => 'admission_number', 'label' => 'Admission Number', 'example' => 'BCS/042/2026'],
                    ['key' => 'programme_title', 'label' => 'Programme Name', 'example' => 'Bachelor of Science in Computer Science'],
                    ['key' => 'next_of_kin', 'label' => 'Next of Kin / Sponsor', 'example' => 'David Chebet (Father)'],
                ],
            ],
            'medical_form' => [
                'key' => 'medical_form',
                'name' => 'Student Medical Examination Report',
                'category' => 'Health & Clearance',
                'code' => 'FORM MUC/MED/02',
                'description' => 'Clinical health assessment and immunization record required from a registered medical practitioner upon reporting.',
                'icon' => 'activity',
                'badge' => 'COMPLIANCE',
                'placeholders' => [
                    ['key' => 'student_name', 'label' => 'Student Name', 'example' => 'Ms. Jackline Chebet'],
                    ['key' => 'admission_number', 'label' => 'Admission Number', 'example' => 'BCS/042/2026'],
                    ['key' => 'blood_group', 'label' => 'Blood Group', 'example' => 'O Positive (O+)'],
                    ['key' => 'allergies', 'label' => 'Known Allergies / Pre-existing', 'example' => 'None recorded'],
                ],
            ],
            'fee_structure' => [
                'key' => 'fee_structure',
                'name' => 'Complete 4-Year Programme Fee Structure',
                'category' => 'Finance & Invoicing',
                'code' => 'MUC/FIN/SCH/2026',
                'description' => 'Official schedule of semester tuition, administrative charges, statutory levies, and payment accounts.',
                'icon' => 'credit-card',
                'badge' => 'FINANCIAL',
                'placeholders' => [
                    ['key' => 'programme_title', 'label' => 'Programme', 'example' => 'Bachelor of Science in Computer Science'],
                    ['key' => 'tuition_per_sem', 'label' => 'Tuition per Semester', 'example' => 'KES 48,500.00'],
                    ['key' => 'statutory_charges', 'label' => 'Annual Statutory Charges', 'example' => 'KES 14,700.00'],
                    ['key' => 'paybill_number', 'label' => 'Official Paybill', 'example' => '222111'],
                ],
            ],
            'enrolment_attestation' => [
                'key' => 'enrolment_attestation',
                'name' => 'Certificate of Active Student Enrolment',
                'category' => 'Academic Affairs',
                'code' => 'MUC/REG/ATTEST/2026',
                'description' => 'Official bonafide student attestation for HELB loan applications, immigration visa clearance, and embassy verification.',
                'icon' => 'badge-check',
                'badge' => 'OFFICIAL',
                'placeholders' => [
                    ['key' => 'student_name', 'label' => 'Student Name', 'example' => 'Ms. Jackline Chebet'],
                    ['key' => 'admission_number', 'label' => 'Admission Number', 'example' => 'BCS/042/2026'],
                    ['key' => 'current_year_of_study', 'label' => 'Stage / Year of Study', 'example' => 'Year 1 Semester 1'],
                    ['key' => 'completion_expected', 'label' => 'Expected Graduation Date', 'example' => 'December 2030'],
                ],
            ],
            'provisional_transcript' => [
                'key' => 'provisional_transcript',
                'name' => 'Provisional Academic Performance Transcript',
                'category' => 'Examinations',
                'code' => 'MUC/EXAM/TRANS/01',
                'description' => 'Official Senate-approved semester mark sheet, cumulative GPA, and course unit classification.',
                'icon' => 'graduation-cap',
                'badge' => 'TRANSCRIPT',
                'placeholders' => [
                    ['key' => 'student_name', 'label' => 'Student Full Name', 'example' => 'Ms. Jackline Chebet'],
                    ['key' => 'admission_number', 'label' => 'Admission Number', 'example' => 'BCS/042/2026'],
                    ['key' => 'cumulative_gpa', 'label' => 'Cumulative GPA / Average', 'example' => '74.2% (First Class Honours)'],
                ],
            ],
        ];
    }

    /**
     * Provide sample/prototype data model for realistic rendering when no active application is chosen.
     *
     * @return array<string, mixed>
     */
    public function samplePayload(): array
    {
        return [
            'applicant' => [
                'name' => 'Ms. Jackline Chebet',
                'first_name' => 'Jackline',
                'last_name' => 'Chebet',
                'title' => 'Ms.',
                'gender' => 'Female',
                'email' => 'jackline.chebet@mema.ac.ke',
                'phone' => '+254 712 345 678',
                'national_id' => '38491029',
                'county' => 'Nandi',
                'nationality' => 'Kenyan',
                'address' => 'P.O. Box 412 - 30300, Kapsabet',
                'next_of_kin' => 'David Chebet (Father) — Tel: +254 722 000 111',
            ],
            'application' => [
                'application_number' => 'MC-APP-2026-08821',
                'reference_number' => 'MUC/ADM/2026/09/8821',
                'admission_number' => 'BCS/042/2026',
                'programme_title' => 'Bachelor of Science in Computer Science',
                'programme_code' => 'BCS',
                'school_name' => 'School of Computing & Informatics',
                'department_name' => 'Department of Computer Science & Software Engineering',
                'qualification_level' => 'Undergraduate Degree',
                'study_mode' => 'Full Time (Regular)',
                'campus' => 'Main Campus — Technology Towers',
                'academic_year' => '2026/2027 Academic Year',
                'duration' => '4 Academic Years (8 Semesters)',
                'commencement_date' => '15th September 2026',
                'reporting_date' => '15th September 2026',
                'orientation_dates' => '15th – 19th September 2026',
                'classes_begin_date' => '22nd September 2026',
                'acceptance_deadline' => '10th September 2026',
                'issue_date' => now()->format('jS F Y'),
            ],
            'fees' => [
                'tuition_semester' => 48500.00,
                'caution_money' => 5000.00,
                'student_id' => 1000.00,
                'examination_fee' => 3500.00,
                'library_fee' => 2000.00,
                'medical_fee' => 2000.00,
                'activity_fee' => 1200.00,
                'total_semester_1' => 63200.00,
                'subsequent_semesters' => 55200.00,
                'paybill_number' => '222111',
                'bank_account' => 'Kenya Commercial Bank (KCB) — A/C No: 1109283741',
                'bank_branch' => 'University Way Branch',
            ],
            'verification' => [
                'token' => 'MUC-VER-77A91B82-202609',
                'checksum' => hash('sha256', 'MUC-VER-77A91B82-202609-BCS/042/2026-JacklineChebet'),
                'url' => url('/admissions/verify/MUC-VER-77A91B82-202609'),
            ],
            'signatory' => [
                'name' => 'Dr. Paul K. Webuye',
                'title' => 'ACADEMIC REGISTRAR',
                'office' => 'Office of the Deputy Vice-Chancellor (Academic & Student Affairs)',
                'institution' => 'MEMA UNIVERSITY COLLEGE',
            ],
            'transcript_units' => [
                ['code' => 'BCS 111', 'title' => 'Introduction to Computer Programming', 'credits' => 3.0, 'marks' => 78, 'grade' => 'A'],
                ['code' => 'BCS 112', 'title' => 'Discrete Mathematics for Computing', 'credits' => 3.0, 'marks' => 82, 'grade' => 'A'],
                ['code' => 'BCS 113', 'title' => 'Computer Architecture & Digital Logic', 'credits' => 3.0, 'marks' => 71, 'grade' => 'B+'],
                ['code' => 'BCS 114', 'title' => 'Communication & Technical Writing Skills', 'credits' => 2.0, 'marks' => 80, 'grade' => 'A'],
                ['code' => 'BCS 115', 'title' => 'Fundamentals of Database Systems', 'credits' => 3.0, 'marks' => 75, 'grade' => 'A'],
                ['code' => 'BCS 116', 'title' => 'Calculus for Computational Sciences', 'credits' => 3.0, 'marks' => 68, 'grade' => 'B'],
            ],
        ];
    }

    /**
     * Build dynamic template data from a live AdmissionApplication or fallback to realistic prototype.
     *
     * @return array<string, mixed>
     */
    public function resolvePayload(?AdmissionApplication $application = null): array
    {
        if ($application === null) {
            return $this->samplePayload();
        }

        $application->loadMissing(['applicant.user', 'offering.course', 'offering.intake', 'offer']);
        $sample = $this->samplePayload();

        $applicantUser = $application->applicant?->user;
        $course = $application->offering?->course;
        $intake = $application->offering?->intake;
        $offer = $application->offer;

        if ($applicantUser !== null) {
            $sample['applicant']['name'] = $applicantUser->name;
            $sample['applicant']['first_name'] = $applicantUser->first_name ?: $applicantUser->name;
            $sample['applicant']['last_name'] = $applicantUser->last_name ?: '';
            $sample['applicant']['email'] = $applicantUser->email;
            $sample['applicant']['gender'] = ucfirst($applicantUser->gender ?? 'Unspecified');
        }

        if ($application->applicant !== null) {
            $sample['applicant']['phone'] = $application->applicant->phone ?: $sample['applicant']['phone'];
            $sample['applicant']['national_id'] = $application->applicant->applicant_number ?: $sample['applicant']['national_id'];
            $sample['applicant']['county'] = $application->applicant->county ?: $sample['applicant']['county'];
            $sample['applicant']['nationality'] = $application->applicant->nationality ?: $sample['applicant']['nationality'];
        }

        $sample['application']['application_number'] = $application->application_number;
        $sample['application']['reference_number'] = $offer?->offer_number ?? 'MUC/ADM/'.now()->format('Y/m').'/'.strtoupper(substr($application->id, 0, 6));
        $sample['application']['admission_number'] = $application->admission_number ?? ($course ? "{$course->code}/".str_pad((string) $application->id, 3, '0', STR_PAD_LEFT).'/'.now()->format('Y') : $sample['application']['admission_number']);
        $sample['application']['programme_title'] = $course?->name ?? $sample['application']['programme_title'];
        $sample['application']['programme_code'] = $course?->code ?? $sample['application']['programme_code'];
        $sample['application']['academic_year'] = $intake?->academic_year ?? $sample['application']['academic_year'];
        $sample['application']['commencement_date'] = $intake?->start_date?->format('jS F Y') ?? $sample['application']['commencement_date'];
        $sample['application']['reporting_date'] = $intake?->start_date?->format('jS F Y') ?? $sample['application']['reporting_date'];
        $sample['application']['acceptance_deadline'] = $intake?->acceptance_deadline?->format('jS F Y') ?? $sample['application']['acceptance_deadline'];

        if ($offer !== null) {
            $sample['verification']['token'] = $offer->verification_token;
            $sample['verification']['checksum'] = $offer->checksum ?? hash('sha256', $offer->id.$offer->offer_number);
            $sample['verification']['url'] = url('/admissions/verify/'.$offer->verification_token);
        }

        return $sample;
    }
}
