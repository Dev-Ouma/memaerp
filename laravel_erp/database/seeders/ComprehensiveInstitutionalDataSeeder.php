<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AcademicDepartment;
use App\Models\AcademicIntake;
use App\Models\AcademicOffering;
use App\Models\AcademicProgramme;
use App\Models\AcademicSession;
use App\Models\AdmissionApplication;
use App\Models\AdmissionIntake;
use App\Models\AdmissionOffer;
use App\Models\ApplicantProfile;
use App\Models\Course;
use App\Models\DeletionRecord;
use App\Models\Department;
use App\Models\FeeInvoice;
use App\Models\FeePayment;
use App\Models\GuardianStudent;
use App\Models\InstitutionalTask;
use App\Models\PgResearch\PgPlagiarismScan;
use App\Models\PgResearch\PgResearchCandidate;
use App\Models\PgResearch\PgSeminar;
use App\Models\PgResearch\PgSupervisorAllocation;
use App\Models\Platform\LegalHold;
use App\Models\ProgrammeOffering;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentResult;
use App\Models\Subject;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class ComprehensiveInstitutionalDataSeeder extends Seeder
{
    public function run(): void
    {
        // ---------------------------------------------------------------------
        // 1. Staff & System Users (10+ Realistic Institutional Personas)
        // ---------------------------------------------------------------------
        $staffMembers = [
            ['name' => 'Prof. Peter Wabwire', 'email' => 'vc@mema.ac.ke', 'role' => 'admin', 'title' => 'Vice Chancellor'],
            ['name' => 'Dr. Godfrey Ouma', 'email' => 'registrar@mema.ac.ke', 'role' => 'admin', 'title' => 'Academic Registrar'],
            ['name' => 'Dr. Jane Muthoni', 'email' => 'dean.computing@mema.ac.ke', 'role' => 'staff', 'title' => 'Dean, School of Computing'],
            ['name' => 'Dr. David Kiprop', 'email' => 'dean.business@mema.ac.ke', 'role' => 'staff', 'title' => 'Dean, School of Business'],
            ['name' => 'Prof. Alice Wangui', 'email' => 'dean.research@mema.ac.ke', 'role' => 'staff', 'title' => 'Director, PG Research'],
            ['name' => 'Dr. Samuel Otieno', 'email' => 'hod.software@mema.ac.ke', 'role' => 'staff', 'title' => 'HOD, Software Engineering'],
            ['name' => 'Mary Achieng', 'email' => 'bursar@mema.ac.ke', 'role' => 'staff', 'title' => 'Chief Finance Officer'],
            ['name' => 'Grace Nduta', 'email' => 'dpo@mema.ac.ke', 'role' => 'staff', 'title' => 'Data Protection Officer'],
            ['name' => 'Kevin Ombati', 'email' => 'examinations@mema.ac.ke', 'role' => 'staff', 'title' => 'Chief Examinations Officer'],
            ['name' => 'Faith Chebet', 'email' => 'admissions.officer@mema.ac.ke', 'role' => 'staff', 'title' => 'Senior Admissions Officer'],
        ];

        $createdStaff = [];
        foreach ($staffMembers as $s) {
            $nameParts = explode(' ', $s['name'], 2);
            $username = strtolower(str_replace([' ', '.'], '', $s['name']));
            $user = User::where('email', $s['email'])->orWhere('username', $username)->first() ?? new User();
            $user->fill([
                'name' => $s['name'],
                'first_name' => $nameParts[0],
                'last_name' => $nameParts[1] ?? 'Staff',
                'email' => $s['email'],
                'username' => $username,
                'password' => Hash::make('password'),
                'role' => $s['role'],
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
            $user->save();
            $createdStaff[$s['email']] = $user;
        }

        // ---------------------------------------------------------------------
        // 2. Schools & Faculties (10+ Schools)
        // ---------------------------------------------------------------------
        $schoolsData = [
            ['name' => 'School of Computing, Artificial Intelligence & Data Science', 'code' => 'SCAIDS', 'faculty' => 'Computing'],
            ['name' => 'School of Business, Economics & Management', 'code' => 'SBEM', 'faculty' => 'Business'],
            ['name' => 'School of Health Sciences & Biomedical Technology', 'code' => 'SHSBT', 'faculty' => 'Health'],
            ['name' => 'School of Education, Humanities & Social Sciences', 'code' => 'SEHSS', 'faculty' => 'Education'],
            ['name' => 'School of Law, Policy & Governance', 'code' => 'SLPG', 'faculty' => 'Law'],
            ['name' => 'School of Engineering & Applied Physical Sciences', 'code' => 'SEAPS', 'faculty' => 'Engineering'],
            ['name' => 'School of Agriculture, Food Security & Environmental Studies', 'code' => 'SAFES', 'faculty' => 'Agriculture'],
            ['name' => 'School of Media, Digital Communication & Performing Arts', 'code' => 'SMDC', 'faculty' => 'Media'],
            ['name' => 'School of TVET, Vocational & Technical Studies', 'code' => 'STVTS', 'faculty' => 'TVET'],
            ['name' => 'School of Postgraduate & Advanced Doctoral Studies', 'code' => 'SPGDS', 'faculty' => 'Postgraduate'],
        ];

        $createdSchools = [];
        foreach ($schoolsData as $sch) {
            $school = School::updateOrCreate(
                ['code' => $sch['code']],
                ['name' => $sch['name'], 'description' => "CUE-accredited {$sch['name']}."]
            );
            $createdSchools[$sch['code']] = $school;
        }

        // ---------------------------------------------------------------------
        // 3. Academic Departments (10+ Departments)
        // ---------------------------------------------------------------------
        $departmentsData = [
            ['name' => 'Department of Computer Science & Software Engineering', 'code' => 'DCSSE', 'school' => 'SCAIDS'],
            ['name' => 'Department of Artificial Intelligence & Machine Learning', 'code' => 'DAIML', 'school' => 'SCAIDS'],
            ['name' => 'Department of Accounting, Banking & Finance', 'code' => 'DABF', 'school' => 'SBEM'],
            ['name' => 'Department of Procurement, Supply Chain & Logistics', 'code' => 'DPSCL', 'school' => 'SBEM'],
            ['name' => 'Department of Nursing, Public Health & Pharmacy', 'code' => 'DNPHP', 'school' => 'SHSBT'],
            ['name' => 'Department of Curriculum Studies & Educational Leadership', 'code' => 'DCSEL', 'school' => 'SEHSS'],
            ['name' => 'Department of Commercial, Private & Corporate Law', 'code' => 'DCPCL', 'school' => 'SLPG'],
            ['name' => 'Department of Electrical & Telecommunication Engineering', 'code' => 'DETE', 'school' => 'SEAPS'],
            ['name' => 'Department of Agribusiness, Soil Science & Food Technology', 'code' => 'DASFT', 'school' => 'SAFES'],
            ['name' => 'Department of Journalism, Digital Media & Public Relations', 'code' => 'DJDMPR', 'school' => 'SMDC'],
        ];

        $createdDepartments = [];
        foreach ($departmentsData as $dept) {
            $department = AcademicDepartment::updateOrCreate(
                ['code' => $dept['code']],
                [
                    'name' => $dept['name'],
                    'school' => $createdSchools[$dept['school']]->name ?? $dept['school'],
                    'hod' => 'Dr. Samuel Otieno',
                    'status' => 'ACTIVE',
                ]
            );
            $createdDepartments[$dept['code']] = $department;
        }

        // ---------------------------------------------------------------------
        // 4. Courses & Academic Programmes (10+ Degree/Diploma Programmes)
        // ---------------------------------------------------------------------
        $coursesData = [
            ['code' => 'BCS', 'name' => 'Bachelor of Science in Computer Science', 'faculty' => 'Computing', 'department' => 'DCSSE'],
            ['code' => 'BSE', 'name' => 'Bachelor of Science in Software Engineering', 'faculty' => 'Computing', 'department' => 'DCSSE'],
            ['code' => 'BAI', 'name' => 'Bachelor of Science in Artificial Intelligence', 'faculty' => 'Computing', 'department' => 'DAIML'],
            ['code' => 'BBA', 'name' => 'Bachelor of Business Administration', 'faculty' => 'Business', 'department' => 'DABF'],
            ['code' => 'BPL', 'name' => 'Bachelor of Science in Procurement & Logistics', 'faculty' => 'Business', 'department' => 'DPSCL'],
            ['code' => 'MPH', 'name' => 'Master of Public Health & Epidemiology', 'faculty' => 'Health', 'department' => 'DNPHP'],
            ['code' => 'MBA', 'name' => 'Master of Business Administration', 'faculty' => 'Business', 'department' => 'DABF'],
            ['code' => 'MSC-DS', 'name' => 'Master of Science in Data Science & Analytics', 'faculty' => 'Computing', 'department' => 'DAIML'],
            ['code' => 'PHD-CS', 'name' => 'Doctor of Philosophy in Computer Science', 'faculty' => 'Postgraduate', 'department' => 'DCSSE'],
            ['code' => 'DIP-IT', 'name' => 'Diploma in Information Technology & Cloud', 'faculty' => 'TVET', 'department' => 'DCSSE'],
        ];

        $createdCourses = [];
        foreach ($coursesData as $c) {
            $course = Course::updateOrCreate(
                ['code' => $c['code']],
                ['name' => $c['name']]
            );
            $createdCourses[$c['code']] = $course;
        }

        // ---------------------------------------------------------------------
        // 5. Academic Intakes & Offerings (10+ Intakes / Offerings)
        // ---------------------------------------------------------------------
        $intakesData = [
            ['code' => 'SEP-2026', 'name' => 'September 2026 Regular Intake', 'opens' => '2026-06-01', 'closes' => '2026-09-20', 'deadline' => '2026-09-30'],
            ['code' => 'VIRTUAL-SEP-2026', 'name' => 'September 2026 Virtual Campus Intake', 'opens' => '2026-06-01', 'closes' => '2026-10-15', 'deadline' => '2026-10-30'],
            ['code' => 'JAN-2027', 'name' => 'January 2027 Main Campus Intake', 'opens' => '2026-10-01', 'closes' => '2027-01-15', 'deadline' => '2027-01-25'],
            ['code' => 'VIRTUAL-JAN-2027', 'name' => 'January 2027 Online Trimester Intake', 'opens' => '2026-10-01', 'closes' => '2027-01-30', 'deadline' => '2027-02-10'],
            ['code' => 'MAY-2027', 'name' => 'May 2027 Fast-Track Trimester', 'opens' => '2027-02-01', 'closes' => '2027-05-15', 'deadline' => '2027-05-25'],
            ['code' => 'PSSP-2026', 'name' => 'Self-Sponsored Weekend Professional 2026', 'opens' => '2026-07-01', 'closes' => '2026-09-25', 'deadline' => '2026-10-05'],
            ['code' => 'EXECUTIVE-MBA-2026', 'name' => 'Executive MBA Cohort 2026', 'opens' => '2026-06-15', 'closes' => '2026-09-30', 'deadline' => '2026-10-15'],
            ['code' => 'RESEARCH-2026', 'name' => 'Postgraduate Doctoral Research Stream 2026', 'opens' => '2026-01-01', 'closes' => '2026-12-31', 'deadline' => '2026-12-31'],
            ['code' => 'TVET-SEP-2026', 'name' => 'TVET Skills & Technical Intake Sep 2026', 'opens' => '2026-07-01', 'closes' => '2026-09-25', 'deadline' => '2026-10-05'],
            ['code' => 'GLOBAL-2026', 'name' => 'Global Open International Intake 2026', 'opens' => '2026-05-01', 'closes' => '2026-11-30', 'deadline' => '2026-12-15'],
        ];

        $createdIntakes = [];
        foreach ($intakesData as $i) {
            $intake = AdmissionIntake::updateOrCreate(
                ['code' => $i['code']],
                [
                    'name' => $i['name'],
                    'opens_at' => $i['opens'],
                    'closes_at' => $i['closes'],
                    'acceptance_deadline' => $i['deadline'],
                    'is_published' => true,
                ]
            );
            $createdIntakes[$i['code']] = $intake;
        }

        // Create 10+ Programme Offerings
        $createdOfferings = [];
        $offeringMatrix = [
            ['course' => 'BCS', 'intake' => 'SEP-2026', 'mode' => 'Full-time', 'campus' => 'Main Campus', 'fee' => 1000, 'cap' => 120],
            ['course' => 'BCS', 'intake' => 'VIRTUAL-SEP-2026', 'mode' => 'Online', 'campus' => 'Virtual Campus', 'fee' => 1000, 'cap' => 300],
            ['course' => 'BSE', 'intake' => 'SEP-2026', 'mode' => 'Full-time', 'campus' => 'Main Campus', 'fee' => 1000, 'cap' => 90],
            ['course' => 'BAI', 'intake' => 'VIRTUAL-SEP-2026', 'mode' => 'Online', 'campus' => 'Virtual Campus', 'fee' => 1500, 'cap' => 150],
            ['course' => 'BBA', 'intake' => 'SEP-2026', 'mode' => 'Full-time', 'campus' => 'Main Campus', 'fee' => 1000, 'cap' => 100],
            ['course' => 'BPL', 'intake' => 'PSSP-2026', 'mode' => 'Weekend', 'campus' => 'Nairobi CBD', 'fee' => 1000, 'cap' => 80],
            ['course' => 'MPH', 'intake' => 'VIRTUAL-SEP-2026', 'mode' => 'Online', 'campus' => 'Virtual Campus', 'fee' => 2000, 'cap' => 50],
            ['course' => 'MBA', 'intake' => 'EXECUTIVE-MBA-2026', 'mode' => 'Blended', 'campus' => 'Nairobi CBD', 'fee' => 2000, 'cap' => 60],
            ['course' => 'MSC-DS', 'intake' => 'RESEARCH-2026', 'mode' => 'Blended', 'campus' => 'Main Campus', 'fee' => 2000, 'cap' => 45],
            ['course' => 'DIP-IT', 'intake' => 'TVET-SEP-2026', 'mode' => 'Full-time', 'campus' => 'Main Campus', 'fee' => 500, 'cap' => 100],
        ];

        foreach ($offeringMatrix as $om) {
            $courseId = $createdCourses[$om['course']]->id ?? 1;
            $intakeId = $createdIntakes[$om['intake']]->id ?? 1;

            $offering = ProgrammeOffering::updateOrCreate(
                [
                    'course_id' => $courseId,
                    'admission_intake_id' => $intakeId,
                    'study_mode' => $om['mode'],
                    'campus' => $om['campus'],
                ],
                [
                    'capacity' => $om['cap'],
                    'application_fee' => $om['fee'],
                    'is_published' => true,
                ]
            );
            $createdOfferings[] = $offering;
        }

        // ---------------------------------------------------------------------
        // 6. Realistic Applicants & Applications (10 Diverse Lifecycle Records)
        // ---------------------------------------------------------------------
        $applicantsData = [
            ['name' => 'Wanjiku Mwangi', 'email' => 'wanjiku.m@gmail.com', 'phone' => '0711223344', 'status' => 'DRAFT', 'offering' => 0, 'grade' => 'B+'],
            ['name' => 'Brian Kipkoech', 'email' => 'brian.k@gmail.com', 'phone' => '0722334455', 'status' => 'SUBMITTED', 'offering' => 1, 'grade' => 'A-'],
            ['name' => 'Amina Hassan', 'email' => 'amina.h@gmail.com', 'phone' => '0733445566', 'status' => 'UNDER_REVIEW', 'offering' => 2, 'grade' => 'B'],
            ['name' => 'Emmanuel Ochieng', 'email' => 'emmanuel.o@gmail.com', 'phone' => '0744556677', 'status' => 'VERIFIED', 'offering' => 3, 'grade' => 'A'],
            ['name' => 'Mercy Chepkemoi', 'email' => 'mercy.c@gmail.com', 'phone' => '0755667788', 'status' => 'SHORTLISTED', 'offering' => 4, 'grade' => 'B+'],
            ['name' => 'Dennis Maina', 'email' => 'dennis.m@gmail.com', 'phone' => '0766778899', 'status' => 'APPROVAL_PENDING', 'offering' => 5, 'grade' => 'B'],
            ['name' => 'Valerie Wairimu', 'email' => 'valerie.w@gmail.com', 'phone' => '0777889900', 'status' => 'ADMITTED', 'offering' => 6, 'grade' => 'Upper 2nd'],
            ['name' => 'Collins Kiprotich', 'email' => 'collins.k@gmail.com', 'phone' => '0788990011', 'status' => 'ENROLLED', 'offering' => 7, 'grade' => 'Upper 2nd'],
            ['name' => 'Sharon Akinyi', 'email' => 'sharon.a@gmail.com', 'phone' => '0799001122', 'status' => 'INFO_REQUESTED', 'offering' => 8, 'grade' => 'B-'],
            ['name' => 'Victor Mutua', 'email' => 'victor.m@gmail.com', 'phone' => '0700112233', 'status' => 'REVOKED', 'offering' => 9, 'grade' => 'C+'],
        ];

        $session = AcademicSession::firstOrCreate(
            ['start_date' => '2026-09-01', 'end_date' => '2027-08-31']
        );

        foreach ($applicantsData as $idx => $appData) {
            $nameParts = explode(' ', $appData['name'], 2);
            $username = 'app_'.strtolower($nameParts[0]).'_'.($idx + 1);
            $appUser = User::where('email', $appData['email'])->orWhere('username', $username)->first() ?? new User();
            $appUser->fill([
                'name' => $appData['name'],
                'first_name' => $nameParts[0],
                'last_name' => $nameParts[1] ?? 'Applicant',
                'email' => $appData['email'],
                'username' => $username,
                'password' => Hash::make('password'),
                'role' => 'applicant',
                'gender' => ($idx % 2 === 0) ? 'F' : 'M',
                'is_active' => true,
            ]);
            $appUser->save();

            $piNumber = 'PI-2026-'.str_pad((string)($idx + 101), 4, '0', STR_PAD_LEFT);
            $profile = ApplicantProfile::updateOrCreate(
                ['user_id' => $appUser->id],
                [
                    'applicant_number' => $piNumber,
                    'phone' => $appData['phone'],
                    'date_of_birth' => Carbon::now()->subYears(19 + $idx)->format('Y-m-d'),
                    'nationality' => 'Kenyan',
                    'county' => ['Nairobi', 'Kiambu', 'Mombasa', 'Kisumu', 'Nakuru', 'Uasin Gishu'][$idx % 6],
                    'identity_type' => 'national_id',
                    'identity_number' => '38'.str_pad((string)($idx + 1420), 6, '0', STR_PAD_LEFT),
                    'qr_token' => Str::random(48),
                ]
            );

            $offering = $createdOfferings[$appData['offering'] % count($createdOfferings)];
            $appNumber = 'APP-2026-'.str_pad((string)($idx + 101), 4, '0', STR_PAD_LEFT);

            $application = AdmissionApplication::updateOrCreate(
                ['applicant_profile_id' => $profile->id],
                [
                    'programme_offering_id' => $offering->id,
                    'application_number' => $appNumber,
                    'status' => $appData['status'],
                    'form_data' => [
                        'education' => "KCSE 2024, Mean Grade {$appData['grade']}.",
                        'gender' => $appUser->gender,
                        'county' => $profile->county,
                        'source_channel' => 'Website',
                    ],
                ]
            );

            // Create Application History
            $application->histories()->create([
                'from_status' => 'DRAFT',
                'to_status' => $appData['status'],
                'actor_user_id' => $createdStaff['admissions.officer@mema.ac.ke']->id ?? $appUser->id,
                'reason_code' => 'lifecycle_transition',
                'note' => "Status transitioned to {$appData['status']} during admissions processing cycle.",
                'created_at' => now()->subDays(10 - $idx),
            ]);

            // If ADMITTED or ENROLLED, create AdmissionOffer
            if (in_array($appData['status'], ['ADMITTED', 'ENROLLED', 'REVOKED'])) {
                $offerNumber = 'OFF-2026-'.str_pad((string)($idx + 1), 4, '0', STR_PAD_LEFT);
                AdmissionOffer::updateOrCreate(
                    ['admission_application_id' => $application->id],
                    [
                        'offer_number' => $offerNumber,
                        'checksum' => hash('sha256', $offerNumber),
                        'status' => ($appData['status'] === 'ENROLLED') ? 'ACCEPTED' : (($appData['status'] === 'REVOKED') ? 'REVOKED' : 'ISSUED'),
                        'issued_at' => now()->subDays(5),
                        'expires_at' => now()->addDays(14),
                        'verification_token' => Str::random(40),
                    ]
                );
            }

            // -----------------------------------------------------------------
            // 7. Converted Enrolled Students (10+ Students with Student Profile)
            // -----------------------------------------------------------------
            $admNumber = 'MEMA/BCS/2026/'.str_pad((string)($idx + 1), 3, '0', STR_PAD_LEFT);
            $student = Student::updateOrCreate(
                ['user_id' => $appUser->id],
                [
                    'admission_number' => $admNumber,
                    'course_id' => $offering->course_id,
                    'academic_session_id' => $session->id,
                ]
            );

            // -----------------------------------------------------------------
            // 8. Fee Transactions & M-Pesa Receipts (10+ Financial Records)
            // -----------------------------------------------------------------
            $txnCodes = ['QJH7823901', 'RKP9012384', 'SHL5610293', 'TLM1928374', 'UKN8291047', 'VKP3910284', 'WLM9018273', 'XKN1928475', 'YLM8291048', 'ZKP9102948'];
            $application->payments()->updateOrCreate(
                ['reference' => $txnCodes[$idx % count($txnCodes)]],
                [
                    'amount' => $offering->application_fee,
                    'currency' => 'KES',
                    'channel' => ['mpesa', 'paybill', 'pochi', 'till', 'card'][$idx % 5],
                    'status' => 'COMPLETED',
                    'idempotency_key' => (string) Str::uuid(),
                    'paid_at' => now()->subDays(8 - $idx),
                    'receipt_number' => 'REC-2026-'.str_pad((string)($idx + 1), 4, '0', STR_PAD_LEFT),
                    'provider_payload' => [
                        'phone' => '0113636154',
                        'account' => '0113636154',
                        'paybill' => '522 522',
                    ],
                ]
            );
        }

        // ---------------------------------------------------------------------
        // 9. PG Research Candidates (10+ Lifecycle Research Projects)
        // ---------------------------------------------------------------------
        $researchTopics = [
            'Deep Learning Architectures for East African Agro-Climatic Yield Forecasting',
            'Blockchain-Based Inter-Institutional Credit Transfer & Degree Verification',
            'Predictive Epidemiology of Vector-Borne Diseases in Tropical Lowlands',
            'Financial Inclusion Analytics: Micro-Credit Repayment Patterns via Mobile Wallets',
            'Zero-Trust Cryptographic Protocols for Cloud Electronic Medical Records',
            'Algorithmic Optimisation of Off-Grid Solar Micro-Grid Power Distribution',
            'Natural Language Processing for Low-Resource Bantu Linguistic Translation',
            'Regulatory Frameworks for AI Autonomous Decision Systems under Kenyan Law',
            'Autonomous Edge Computing for Precision Hydroponic Vertical Farming',
            'Cybersecurity Telemetry Detection against Distributed Denial-of-Service in Banking',
        ];

        foreach ($researchTopics as $rIdx => $topic) {
            $email = "researcher".($rIdx + 1)."@mema.ac.ke";
            $username = "phd_candidate_".($rIdx + 1);
            $candUser = User::where('email', $email)->orWhere('username', $username)->first() ?? new User();
            $candUser->fill([
                'name' => "Researcher ".($rIdx + 1),
                'first_name' => "Researcher",
                'last_name' => (string)($rIdx + 1),
                'email' => $email,
                'username' => $username,
                'password' => Hash::make('password'),
                'role' => 'student',
                'is_active' => true,
            ]);
            $candUser->save();

            $candStudent = Student::firstOrCreate(
                ['user_id' => $candUser->id],
                [
                    'admission_number' => 'PHD/2026/'.str_pad((string)($rIdx + 1), 3, '0', STR_PAD_LEFT),
                    'course_id' => $createdCourses['PHD-CS']->id ?? 1,
                    'academic_session_id' => $session->id,
                ]
            );

            $candidate = PgResearchCandidate::updateOrCreate(
                ['reg_no' => 'PHD/2026/'.str_pad((string)($rIdx + 1), 3, '0', STR_PAD_LEFT)],
                [
                    'student_id' => $candStudent->id,
                    'candidate_name' => $candUser->name,
                    'degree_level' => ($rIdx % 2 === 0) ? 'PHD' : 'MASTERS',
                    'programme_title' => ($rIdx % 2 === 0) ? 'PhD in Computer Science & AI' : 'MSc in Data Science',
                    'academic_year' => '2026/2027',
                    'coursework_units_total' => 16,
                    'coursework_units_passed' => 16,
                    'gpa' => 3.75,
                    'fee_balance' => 0.00,
                    'registration_status' => 'REGISTERED',
                    'eligibility_status' => 'ELIGIBLE',
                    'stage' => PgResearchCandidate::STAGES[$rIdx % count(PgResearchCandidate::STAGES)],
                    'thesis_title' => $topic,
                    'commenced_on' => now()->subMonths(18 - $rIdx)->toDateString(),
                    'expected_completion' => now()->addMonths(18 - $rIdx)->toDateString(),
                ]
            );

            $supervisor = \App\Models\PgResearch\PgSupervisor::updateOrCreate(
                ['staff_no' => 'STF/001'],
                [
                    'user_id' => $createdStaff['dean.research@mema.ac.ke']->id ?? 1,
                    'full_name' => 'Prof. Alice Wangui',
                    'academic_rank' => 'Professor',
                    'department' => 'Computing',
                    'specialization' => 'Artificial Intelligence & Systems',
                    'max_load' => 10,
                    'is_active' => true,
                ]
            );

            // Add Supervisor Allocation
            PgSupervisorAllocation::updateOrCreate(
                ['candidate_id' => $candidate->id],
                [
                    'supervisor_id' => $supervisor->id,
                    'role' => 'LEAD',
                    'assigned_on' => now()->subMonths(16 - $rIdx)->toDateString(),
                    'status' => 'ACTIVE',
                ]
            );

            // Add Plagiarism Scan
            PgPlagiarismScan::updateOrCreate(
                ['candidate_id' => $candidate->id],
                [
                    'document_type' => 'THESIS',
                    'similarity_index' => 5 + ($rIdx % 9),
                    'threshold' => 15.00,
                    'ai_index' => 2.00,
                    'ai_threshold' => 10.00,
                    'status' => 'APPROVED',
                    'report_reference' => "TURNITIN-2026-".str_pad((string)($rIdx + 1), 4, '0', STR_PAD_LEFT),
                    'scanned_at' => now()->subWeeks(3),
                ]
            );
        }

        // ---------------------------------------------------------------------
        // 10. Departmental Tasks (10+ Real Tasks)
        // ---------------------------------------------------------------------
        $tasksData = [
            ['title' => 'CUE Academic Curriculum Compliance Review 2026', 'priority' => 'HIGH', 'status' => 'IN_PROGRESS', 'due' => now()->addDays(14)],
            ['title' => 'Automated Timetable Generation for September 2026 Semester', 'priority' => 'HIGH', 'status' => 'PENDING', 'due' => now()->addDays(7)],
            ['title' => 'M-Pesa Safaricom Daraja 2.0 Webhook Certificate Renewal', 'priority' => 'HIGH', 'status' => 'COMPLETED', 'due' => now()->subDays(2)],
            ['title' => 'Virtual Campus Moodle LMS Load Balancer Benchmark', 'priority' => 'MEDIUM', 'status' => 'IN_PROGRESS', 'due' => now()->addDays(10)],
            ['title' => 'Postgraduate Doctoral Proposal Defense Panel Scheduling', 'priority' => 'MEDIUM', 'status' => 'PENDING', 'due' => now()->addDays(20)],
            ['title' => 'KDPA 2019 Statutory Bi-Annual Data Audit Report Preparation', 'priority' => 'HIGH', 'status' => 'IN_PROGRESS', 'due' => now()->addDays(5)],
            ['title' => 'KCSE Cluster Grade Threshold Optimization for Admission Offerings', 'priority' => 'LOW', 'status' => 'COMPLETED', 'due' => now()->subDays(10)],
            ['title' => 'Digital Library IEEE & ACM Journal Subscription Renewal', 'priority' => 'MEDIUM', 'status' => 'COMPLETED', 'due' => now()->subDays(5)],
            ['title' => 'Student ID Smart Card Batch Printing (September 2026 Cohort)', 'priority' => 'LOW', 'status' => 'PENDING', 'due' => now()->addDays(25)],
            ['title' => 'Recycle Bin 30-Day Permanent Purge Auditing & Legal Hold Check', 'priority' => 'MEDIUM', 'status' => 'COMPLETED', 'due' => now()->subDays(1)],
        ];

        foreach ($tasksData as $tIdx => $t) {
            InstitutionalTask::updateOrCreate(
                ['title' => $t['title']],
                [
                    'task_ref' => 'TSK-2026-'.str_pad((string)($tIdx + 1), 3, '0', STR_PAD_LEFT),
                    'assignee_user_id' => $createdStaff['registrar@mema.ac.ke']->id ?? null,
                    'created_by' => $createdStaff['vc@mema.ac.ke']->id ?? 1,
                    'priority' => $t['priority'],
                    'status' => $t['status'],
                    'due_at' => $t['due'],
                    'description' => "Institutional action item: {$t['title']}.",
                ]
            );
        }

        // ---------------------------------------------------------------------
        // 11. Recycle Bin & Data Governance Records (10+ Records)
        // ---------------------------------------------------------------------
        for ($dIdx = 1; $dIdx <= 10; $dIdx++) {
            DeletionRecord::updateOrCreate(
                ['record_id' => 'rec-'.($dIdx + 50)],
                [
                    'entity_type' => ['academic_offerings', 'courses', 'students', 'fee_invoices', 'documents'][$dIdx % 5],
                    'model_type' => 'App\\Models\\Course',
                    'deleted_by' => (string)($createdStaff['registrar@mema.ac.ke']->id ?? 1),
                    'deleted_by_role' => 'admin',
                    'deleted_at' => now()->subDays($dIdx * 2),
                    'purge_after' => now()->addDays(30 - ($dIdx * 2)),
                    'status' => ($dIdx === 1) ? 'RESTORED' : (($dIdx === 2) ? 'LEGAL_HOLD' : 'IN_BIN'),
                    'reason' => "Routine archival and data clean-up cycle.",
                    'original_location' => '/academic/offerings/archived',
                    'owner_type' => 'user',
                    'owner_id' => '1',
                    'snapshot' => ['code' => "ARC-{$dIdx}", 'name' => "Archived Item {$dIdx}"],
                ]
            );
        }
    }
}
