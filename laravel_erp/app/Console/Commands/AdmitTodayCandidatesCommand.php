<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\AcademicSession;
use App\Models\AdmissionApplication;
use App\Models\AdmissionIntake;
use App\Models\AdmissionOffer;
use App\Models\ApplicantProfile;
use App\Models\ApplicationDocument;
use App\Models\ApplicationPaymentAttempt;
use App\Models\ApplicationVersion;
use App\Models\Attendance;
use App\Models\AttendanceRecord;
use App\Models\Course;
use App\Models\ProgrammeOffering;
use App\Models\Staff;
use App\Models\Student;
use App\Models\StudentResult;
use App\Models\Subject;
use App\Models\User;
use App\Services\StudentConversionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class AdmitTodayCandidatesCommand extends Command
{
    protected $signature = 'admissions:admit-today {--count=20 : Number of candidates to admit end to end}';

    protected $description = 'Process and admit candidates through the full end-to-end admission and matriculation lifecycle';

    public function handle(StudentConversionService $conversionService): int
    {
        $count = (int) $this->option('count');
        $this->info("Starting end-to-end admission and enrollment for {$count} candidates today (".now()->format('Y-m-d').')...');

        // Ensure Intake exists
        $intake = AdmissionIntake::firstOrCreate(
            ['code' => 'SEP2026'],
            [
                'name' => 'September 2026 Intake',
                'opens_at' => now()->startOfYear(),
                'closes_at' => now()->addMonths(4),
                'acceptance_deadline' => now()->addMonths(5),
                'is_published' => true,
            ]
        );

        // Ensure Core Courses exist
        $coursesData = [
            ['code' => 'DIT', 'name' => 'Diploma in Information Technology'],
            ['code' => 'CS', 'name' => 'Bachelor of Science in Computer Science'],
            ['code' => 'BBA', 'name' => 'Bachelor of Business Administration'],
            ['code' => 'NURS', 'name' => 'Bachelor of Science in Nursing'],
            ['code' => 'ENG', 'name' => 'Diploma in Electrical Engineering'],
        ];

        $courses = collect();
        foreach ($coursesData as $c) {
            $course = Course::firstOrCreate(
                ['code' => $c['code']],
                ['name' => $c['name'], 'next_student_serial' => 1]
            );
            $courses->push($course);

            ProgrammeOffering::firstOrCreate(
                ['course_id' => $course->id, 'admission_intake_id' => $intake->id],
                [
                    'application_fee' => 1500.00,
                    'capacity' => 100,
                    'status' => 'OPEN',
                ]
            );
        }

        // Ensure Academic Session exists
        $session = AcademicSession::firstOrCreate(
            ['start_date' => '2025-09-01', 'end_date' => '2026-08-31']
        );

        // Candidate data fixtures
        $candidates = [
            ['first' => 'Kevin', 'last' => 'Otieno', 'gender' => 'M', 'county' => 'Kisumu', 'phone' => '0711100001', 'course' => 'CS'],
            ['first' => 'Faith', 'last' => 'Chepkemoi', 'gender' => 'F', 'county' => 'Nakuru', 'phone' => '0711100002', 'course' => 'NURS'],
            ['first' => 'Dennis', 'last' => 'Kipchumba', 'gender' => 'M', 'county' => 'Uasin Gishu', 'phone' => '0711100003', 'course' => 'DIT'],
            ['first' => 'Brenda', 'last' => 'Wanjiku', 'gender' => 'F', 'county' => 'Kiambu', 'phone' => '0711100004', 'course' => 'BBA'],
            ['first' => 'Samuel', 'last' => 'Mwangi', 'gender' => 'M', 'county' => 'Nyeri', 'phone' => '0711100005', 'course' => 'ENG'],
            ['first' => 'Mercy', 'last' => 'Achieng', 'gender' => 'F', 'county' => 'Siaya', 'phone' => '0711100006', 'course' => 'NURS'],
            ['first' => 'Victor', 'last' => 'Omondi', 'gender' => 'M', 'county' => 'Homa Bay', 'phone' => '0711100007', 'course' => 'CS'],
            ['first' => 'Joyce', 'last' => 'Muthoni', 'gender' => 'F', 'county' => 'Murang\'a', 'phone' => '0711100008', 'course' => 'BBA'],
            ['first' => 'Brian', 'last' => 'Kiptoo', 'gender' => 'M', 'county' => 'Kericho', 'phone' => '0711100009', 'course' => 'DIT'],
            ['first' => 'Sharon', 'last' => 'Nekesa', 'gender' => 'F', 'county' => 'Bungoma', 'phone' => '0711100010', 'course' => 'NURS'],
            ['first' => 'Collins', 'last' => 'Wekesa', 'gender' => 'M', 'county' => 'Kakamega', 'phone' => '0711100011', 'course' => 'ENG'],
            ['first' => 'Esther', 'last' => 'Wambui', 'gender' => 'F', 'county' => 'Nyandarua', 'phone' => '0711100012', 'course' => 'BBA'],
            ['first' => 'Emmanuel', 'last' => 'Mutua', 'gender' => 'M', 'county' => 'Machakos', 'phone' => '0711100013', 'course' => 'CS'],
            ['first' => 'Beatrice', 'last' => 'Kavata', 'gender' => 'F', 'county' => 'Kitui', 'phone' => '0711100014', 'course' => 'DIT'],
            ['first' => 'Daniel', 'last' => 'Kariuki', 'gender' => 'M', 'county' => 'Embu', 'phone' => '0711100015', 'course' => 'ENG'],
            ['first' => 'Cynthia', 'last' => 'Nyaboke', 'gender' => 'F', 'county' => 'Kisii', 'phone' => '0711100016', 'course' => 'NURS'],
            ['first' => 'Felix', 'last' => 'Koech', 'gender' => 'M', 'county' => 'Bomet', 'phone' => '0711100017', 'course' => 'BBA'],
            ['first' => 'Lilian', 'last' => 'Akinyi', 'gender' => 'F', 'county' => 'Migori', 'phone' => '0711100018', 'course' => 'CS'],
            ['first' => 'Geoffrey', 'last' => 'Mutiso', 'gender' => 'M', 'county' => 'Makueni', 'phone' => '0711100019', 'course' => 'ENG'],
            ['first' => 'Ann', 'last' => 'Wangari', 'gender' => 'F', 'county' => 'Nairobi', 'phone' => '0711100020', 'course' => 'DIT'],
        ];

        // Ensure sample parent user exists for linkages
        $parentUser = User::firstOrCreate(
            ['email' => 'parent@mema.ac.ke'],
            [
                'name' => 'Mary Kamau',
                'first_name' => 'Mary',
                'last_name' => 'Kamau',
                'password' => Hash::make('password'),
                'role' => 'parent',
                'is_active' => true,
            ]
        );

        // Ensure a teacher/staff profile exists for assignments
        $teacherUser = User::firstOrCreate(
            ['email' => 'teacher@mema.ac.ke'],
            [
                'name' => 'Daniel Otieno',
                'first_name' => 'Daniel',
                'last_name' => 'Otieno',
                'password' => Hash::make('password'),
                'role' => 'staff',
                'is_active' => true,
            ]
        );
        $staff = Staff::firstOrCreate(['user_id' => $teacherUser->id], ['course_id' => $courses->first()->id]);

        $admittedCount = 0;
        $bar = $this->output->createProgressBar(min($count, count($candidates)));
        $bar->start();

        foreach (array_slice($candidates, 0, $count) as $index => $c) {
            DB::transaction(function () use ($c, $index, $courses, $intake, $session, $parentUser, $staff, $conversionService, &$admittedCount) {
                $email = strtolower($c['first'].'.'.$c['last'].($index + 1).'@applicant.mema.ac.ke');
                $course = $courses->firstWhere('code', $c['course']) ?: $courses->first();
                $offering = ProgrammeOffering::where('course_id', $course->id)->where('admission_intake_id', $intake->id)->firstOrFail();

                // 1. Create User
                $user = User::updateOrCreate(
                    ['email' => $email],
                    [
                        'name' => "{$c['first']} {$c['last']}",
                        'first_name' => $c['first'],
                        'last_name' => $c['last'],
                        'gender' => $c['gender'],
                        'phone_number' => $c['phone'],
                        'password' => Hash::make('password'),
                        'role' => 'applicant',
                        'is_active' => true,
                    ]
                );

                // 2. Create Profile
                $applicantNumber = sprintf('APP-%s-%04d', now()->format('Y'), 1000 + $index + 1);
                $profile = ApplicantProfile::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'applicant_number' => $applicantNumber,
                        'county' => $c['county'],
                        'nationality' => 'Kenyan',
                        'identity_type' => 'national_id',
                        'identity_number' => (string) (38000000 + $index + 1),
                        'date_of_birth' => now()->subYears(20)->toDateString(),
                        'source_channel' => $index % 2 === 0 ? 'Website' : 'KUCCPS Direct Placement',
                        'qr_token' => 'qr-'.Str::uuid(),
                    ]
                );

                // 3. Create Draft Application
                $appNumber = sprintf('MEMA/%s/%s/%04d', now()->format('Y'), $course->code, 100 + $index + 1);
                $application = AdmissionApplication::updateOrCreate(
                    ['applicant_profile_id' => $profile->id, 'programme_offering_id' => $offering->id],
                    [
                        'application_number' => $appNumber,
                        'study_mode' => 'Full-time',
                        'status' => 'DRAFT',
                        'completion_percent' => 100,
                        'declarations_accepted' => true,
                    ]
                );

                // 4. Create Verified Documents
                foreach (['NATIONAL_ID' => 'national_id.pdf', 'ACADEMIC_TRANSCRIPT' => 'kcse_certificate.pdf', 'PASSPORT_PHOTO' => 'passport_photo.jpg'] as $docType => $docName) {
                    ApplicationDocument::updateOrCreate(
                        ['admission_application_id' => $application->id, 'document_type' => $docType],
                        [
                            'original_name' => $docName,
                            'storage_path' => "admissions/{$application->id}/{$docName}",
                            'mime_type' => str_ends_with($docName, '.pdf') ? 'application/pdf' : 'image/jpeg',
                            'size_bytes' => 1048576,
                            'sha256' => hash('sha256', $docName.$application->id),
                            'verification_status' => 'VERIFIED',
                        ]
                    );
                }

                // 5. Create Payment Attempt (PAID)
                $payment = ApplicationPaymentAttempt::updateOrCreate(
                    ['admission_application_id' => $application->id, 'reference' => "PAY-MEMA-{$application->id}"],
                    [
                        'channel' => 'MPESA_EXPRESS',
                        'amount' => 1500.00,
                        'currency' => 'KES',
                        'status' => 'PAID',
                        'idempotency_key' => 'idemp-'.Str::uuid(),
                        'paid_at' => now(),
                        'receipt_number' => sprintf('REC-%s-%04d', now()->format('Ymd'), $index + 1),
                    ]
                );

                // 6. Create Snapshot Version
                $snapshot = [
                    'application' => ['study_mode' => 'Full-time', 'county' => $c['county']],
                    'applicant' => $profile->toArray(),
                    'offering' => $offering->load('course')->toArray(),
                    'declarations_accepted' => true,
                    'payment_reference' => $payment->reference,
                ];

                $version = ApplicationVersion::create([
                    'admission_application_id' => $application->id,
                    'version' => 1,
                    'snapshot' => $snapshot,
                    'checksum' => hash('sha256', json_encode($snapshot)),
                    'created_at' => now(),
                ]);

                // 7. Progress Application to ADMITTED
                $application->forceFill([
                    'status' => 'ADMITTED',
                    'payment_status' => 'PAID',
                    'submitted_version_id' => $version->id,
                    'current_version' => 1,
                    'submitted_at' => now(),
                    'submission_receipt_number' => sprintf('SUB-%s-%04d', now()->format('Ymd'), $index + 1),
                    'last_activity_at' => now(),
                ])->save();

                // 8. Create Admission Offer
                $offerNumber = sprintf('OFF-%s-%s-%04d', now()->format('Y'), $course->code, 500 + $index + 1);
                $offer = AdmissionOffer::updateOrCreate(
                    ['admission_application_id' => $application->id],
                    [
                        'offer_number' => $offerNumber,
                        'verification_token' => 'verif-'.Str::uuid(),
                        'checksum' => hash('sha256', $offerNumber.now()->toDateString()),
                        'status' => 'ISSUED',
                        'issued_at' => now(),
                        'expires_at' => now()->addDays(21),
                    ]
                );

                // 9. Accept Offer & Convert to Student
                $application->forceFill(['status' => 'READY_TO_ENROL'])->save();
                $conversion = $conversionService->convert($application);

                $student = Student::where('user_id', $user->id)->first();
                if ($student) {
                    $student->forceFill([
                        'course_id' => $course->id,
                        'academic_session_id' => $session->id,
                    ])->save();

                    // Link parent for the first 3 students as sample multi-child guardian data
                    if ($index < 3) {
                        $parentUser->children()->syncWithoutDetaching([
                            $student->id => ['relationship' => $index === 0 ? 'Mother' : 'Guardian', 'is_primary' => true],
                        ]);
                    }

                    // Create subject and initial academic assessment result
                    $subject = Subject::firstOrCreate(
                        ['course_id' => $course->id, 'code' => $course->code.'-101'],
                        ['name' => 'Foundations of '.$course->name, 'staff_id' => $staff->id]
                    );

                    StudentResult::updateOrCreate(
                        ['student_id' => $student->id, 'subject_id' => $subject->id],
                        ['test_score' => rand(28, 38), 'exam_score' => rand(48, 58)]
                    );

                    // Create attendance record
                    $attendance = Attendance::firstOrCreate(
                        ['subject_id' => $subject->id, 'date' => now()->toDateString()],
                        ['academic_session_id' => $session->id]
                    );

                    AttendanceRecord::updateOrCreate(
                        ['attendance_id' => $attendance->id, 'student_id' => $student->id],
                        ['present' => true]
                    );
                }

                $admittedCount++;
            });

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(
            ['Candidate Name', 'Course', 'Application No', 'Offer No', 'Assigned Reg No', 'Status'],
            AdmissionApplication::with(['applicant.user', 'offering.course', 'offer'])
                ->latest()
                ->take($count)
                ->get()
                ->map(fn ($app) => [
                    $app->applicant?->user?->name,
                    $app->offering?->course?->code,
                    $app->application_number,
                    $app->offer?->offer_number,
                    Student::where('user_id', $app->applicant?->user_id)->value('admission_number') ?? 'Enrolled',
                    'COMPLETED / ENROLLED',
                ])
        );

        $this->info("Successfully admitted and enrolled {$admittedCount} candidates end-to-end today!");

        return self::SUCCESS;
    }
}
