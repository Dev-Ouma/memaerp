<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AcademicCourseUnit;
use App\Models\AcademicDepartment;
use App\Models\AcademicProgramme;
use App\Models\School;
use App\Services\RecycleBinService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

final class CurriculumController extends Controller
{
    /**
     * Header row of the Course Unit bulk upload template. Keys match the
     * request fields accepted by storeCourseUnit().
     *
     * @var list<string>
     */
    private const COURSE_UNIT_TEMPLATE_COLUMNS = [
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
    ];

    /**
     * Worked examples shipped inside the template: one core unit, one elective
     * with a prerequisite, one lab unit and one inactive (archived) unit.
     *
     * @var list<list<string>>
     */
    private const COURSE_UNIT_TEMPLATE_SAMPLES = [
        ['CSC 205', 'Data Communication and Computer Networks', 'Department of Computer Science & Software Engineering', '3', '35', '10', 'Core Unit', 'CSC 101', 'OSI and TCP/IP models, routing, switching and network security fundamentals.', 'Active'],
        ['MAT 210', 'Linear Algebra for Data Science', 'Department of Mathematics & Statistics', '3', '45', '0', 'Elective Track Unit', 'MAT 102', 'Vector spaces, matrix decomposition and applications to machine learning.', 'Active'],
        ['AGR 220', 'Soil Science Laboratory Practical', 'Department of Agricultural Technology & Food Systems', '2', '15', '30', 'Practical Lab Unit', 'None', 'Soil sampling, texture analysis and nutrient assays in the laboratory.', 'Active'],
        ['EDU 150', 'Foundations of Curriculum Design', 'Department of Educational Leadership & Curriculum', '3', '45', '0', 'University Common Unit', 'None', 'Retired common unit retained for transcript history.', 'Inactive'],
    ];

    /**
     * 1. Department (CRUD Master)
     */
    public function department(Request $request): View
    {
        $departments = AcademicDepartment::orderBy('id', 'asc')->get();
        $schools = School::orderBy('name', 'asc')->get();

        $stats = [
            'totalDepartments' => $departments->count(),
            'activeAcademicDepts' => $departments->where('status', 'Active')->count(),
            'serviceDepts' => 4,
            'totalAcademicStaff' => $departments->sum('staff_count') ?: 246,
        ];

        return view('curriculum.department', compact('stats', 'departments', 'schools'));
    }

    public function storeDepartment(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:30', 'unique:academic_departments,code'],
            'name' => ['required', 'string', 'max:190'],
            'school' => ['nullable', 'string', 'max:190'],
            'hod' => ['nullable', 'string', 'max:190'],
            'programmes_count' => ['nullable', 'integer', 'min:0'],
            'staff_count' => ['nullable', 'integer', 'min:0'],
            'email' => ['nullable', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:Active,Inactive'],
        ]);

        $department = AcademicDepartment::create([
            'code' => strtoupper(trim($validated['code'])),
            'name' => trim($validated['name']),
            'school' => $validated['school'] ? trim($validated['school']) : null,
            'hod' => $validated['hod'] ? trim($validated['hod']) : null,
            'programmes_count' => (int) ($validated['programmes_count'] ?? 0),
            'staff_count' => (int) ($validated['staff_count'] ?? 0),
            'email' => $validated['email'] ? strtolower(trim($validated['email'])) : null,
            'phone' => $validated['phone'] ? trim($validated['phone']) : null,
            'description' => $validated['description'] ? trim($validated['description']) : null,
            'status' => $validated['status'],
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Department '{$department->name}' created successfully.",
                'department' => $department,
            ]);
        }

        return redirect()->route('curriculum.department')->with('success', "Department '{$department->name}' created successfully.");
    }

    public function updateDepartment(Request $request, AcademicDepartment $department): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:30', 'unique:academic_departments,code,'.$department->id],
            'name' => ['required', 'string', 'max:190'],
            'school' => ['nullable', 'string', 'max:190'],
            'hod' => ['nullable', 'string', 'max:190'],
            'programmes_count' => ['nullable', 'integer', 'min:0'],
            'staff_count' => ['nullable', 'integer', 'min:0'],
            'email' => ['nullable', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:Active,Inactive'],
        ]);

        $department->update([
            'code' => strtoupper(trim($validated['code'])),
            'name' => trim($validated['name']),
            'school' => $validated['school'] ? trim($validated['school']) : null,
            'hod' => $validated['hod'] ? trim($validated['hod']) : null,
            'programmes_count' => (int) ($validated['programmes_count'] ?? 0),
            'staff_count' => (int) ($validated['staff_count'] ?? 0),
            'email' => $validated['email'] ? strtolower(trim($validated['email'])) : null,
            'phone' => $validated['phone'] ? trim($validated['phone']) : null,
            'description' => $validated['description'] ? trim($validated['description']) : null,
            'status' => $validated['status'],
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Department '{$department->name}' updated successfully.",
                'department' => $department,
            ]);
        }

        return redirect()->route('curriculum.department')->with('success', "Department '{$department->name}' updated successfully.");
    }

    public function destroyDepartment(Request $request, AcademicDepartment $department, RecycleBinService $recycleBin): RedirectResponse|JsonResponse
    {
        $validated = $request->validate(['deletion_reason' => ['required', 'string', 'min:10', 'max:500']]);
        $name = $department->name;
        $recycleBin->delete($department, $request->user(), 'department', $validated['deletion_reason'], route('curriculum.department'));

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Department '{$name}' moved to Recycle Bin.",
            ]);
        }

        return redirect()->route('curriculum.department')->with('success', "Department '{$name}' moved to Recycle Bin.");
    }

    /**
     * 2. Programme Curriculum
     */
    public function programmeCurriculum(Request $request): View
    {
        $programmes = AcademicProgramme::orderBy('code', 'asc')->get();

        $stats = [
            'activeCurricula' => 42,
            'cueAccredited' => 38,
            'underReview' => 4,
            'totalCreditHours' => 148,
        ];

        return view('curriculum.programme-curriculum', compact('stats', 'programmes'));
    }

    /**
     * 3. Instructor Mapping
     */
    public function instructorMapping(Request $request): View
    {
        $stats = [
            'totalAllocations' => 184,
            'assignedLecturers' => 126,
            'unallocatedCourseUnits' => 12,
            'avgTeachingLoad' => '3.4 Units / Semester',
        ];

        $mappings = [
            [
                'id' => 1,
                'unit_code' => 'CSC 311',
                'unit_title' => 'Design & Analysis of Algorithms',
                'primary_instructor' => 'Dr. Amina Hassan (Senior Lecturer)',
                'secondary_instructor' => 'Mr. Bob Lamech (Assistant Lecturer)',
                'department' => 'Computer Science',
                'enrolled_students' => 142,
                'status' => 'Fully Assigned',
            ],
            [
                'id' => 2,
                'unit_code' => 'MAT 204',
                'unit_title' => 'Linear Algebra & Matrix Methods',
                'primary_instructor' => 'Dr. Kikete Wabuya (Ag. Chair / Senior Lecturer)',
                'secondary_instructor' => 'Dr. Jeremiah Onunga (Lecturer)',
                'department' => 'Mathematics & Statistics',
                'enrolled_students' => 186,
                'status' => 'Fully Assigned',
            ],
            [
                'id' => 3,
                'unit_code' => 'ECO 402',
                'unit_title' => 'Applied Econometric Modelling',
                'primary_instructor' => 'Dr. Daniel Otieno (Senior Lecturer)',
                'secondary_instructor' => 'None',
                'department' => 'Economics',
                'enrolled_students' => 98,
                'status' => 'Fully Assigned',
            ],
            [
                'id' => 4,
                'unit_code' => 'CYB 801',
                'unit_title' => 'Advanced Cryptographic Protocols (Postgraduate)',
                'primary_instructor' => 'Prof. James Mwangi (Professor)',
                'secondary_instructor' => 'Dr. Amina Hassan',
                'department' => 'Computer Science',
                'enrolled_students' => 28,
                'status' => 'Fully Assigned',
            ],
        ];

        return view('curriculum.instructor-mapping', compact('stats', 'mappings'));
    }

    /**
     * 4. Cluster Subjects
     */
    public function clusterSubjects(Request $request): View
    {
        $stats = [
            'totalClusterGroups' => 8,
            'registeredClusterSubjects' => 32,
            'kuccpsAligned' => '100% KUCCPS Compliant',
            'lastUpdated' => '2026/2027 Cycle',
        ];

        $subjects = [
            [
                'id' => 1,
                'cluster_group' => 'Cluster 1: STEM / Engineering & Computing',
                'subject_code' => 'KCSE-MAT-ALT-A',
                'subject_name' => 'Mathematics Alternative A',
                'min_grade' => 'C+ (Plus)',
                'weight_multiplier' => '1.5x (Core Subject)',
                'status' => 'Active',
            ],
            [
                'id' => 2,
                'cluster_group' => 'Cluster 1: STEM / Engineering & Computing',
                'subject_code' => 'KCSE-PHY',
                'subject_name' => 'Physics',
                'min_grade' => 'C+ (Plus)',
                'weight_multiplier' => '1.5x (Core Subject)',
                'status' => 'Active',
            ],
            [
                'id' => 3,
                'cluster_group' => 'Cluster 2: Business, Commerce & Economics',
                'subject_code' => 'KCSE-ENG',
                'subject_name' => 'English / Kiswahili',
                'min_grade' => 'C (Plain)',
                'weight_multiplier' => '1.2x (Language Requirement)',
                'status' => 'Active',
            ],
            [
                'id' => 4,
                'cluster_group' => 'Cluster 2: Business, Commerce & Economics',
                'subject_code' => 'KCSE-BST',
                'subject_name' => 'Business Studies / Economics',
                'min_grade' => 'C+ (Plus)',
                'weight_multiplier' => '1.3x (Domain Elective)',
                'status' => 'Active',
            ],
        ];

        return view('curriculum.cluster-subjects', compact('stats', 'subjects'));
    }

    /**
     * 5. Program Cluster Mapping
     */
    public function programClusterMapping(Request $request): View
    {
        $stats = [
            'totalMappedProgrammes' => 36,
            'weightedCutoffRules' => 54,
            'affirmativeActionRules' => 12,
            'systemFormula' => 'Weighted Cluster Point (WCP) v3.2',
        ];

        $mappings = [
            [
                'id' => 1,
                'programme_name' => 'Bachelor of Science in Computer Science',
                'programme_code' => 'MEMA-BCS',
                'cluster_basket' => 'Mathematics A, Physics, Chemistry / Biology, English',
                'min_aggregate_grade' => 'C+ (Plus)',
                'cutoff_points' => '38.450 WCP',
                'status' => 'Active 2026/2027',
            ],
            [
                'id' => 2,
                'programme_name' => 'Bachelor of Data Science',
                'programme_code' => 'MEMA-BDS',
                'cluster_basket' => 'Mathematics A, Physics / Computer Studies, Any Science',
                'min_aggregate_grade' => 'C+ (Plus)',
                'cutoff_points' => '36.800 WCP',
                'status' => 'Active 2026/2027',
            ],
            [
                'id' => 3,
                'programme_name' => 'Bachelor of Business Information Technology',
                'programme_code' => 'MEMA-BBIT',
                'cluster_basket' => 'Mathematics A/B, English/Kiswahili, Business/Economics',
                'min_aggregate_grade' => 'C+ (Plus)',
                'cutoff_points' => '32.150 WCP',
                'status' => 'Active 2026/2027',
            ],
        ];

        return view('curriculum.program-cluster-mapping', compact('stats', 'mappings'));
    }

    /**
     * 6. Progression Criteria
     */
    public function progressionCriteria(Request $request): View
    {
        $stats = [
            'activeRulesets' => 14,
            'passGpaThreshold' => '2.00 (50% Pass Mark)',
            'maxSuppUnits' => '4 Units / Academic Year',
            'discontinuationThreshold' => '< 1.50 CGPA or 3x Repeat',
        ];

        $rules = [
            [
                'id' => 1,
                'rule_code' => 'PROG-UG-STD',
                'programme_level' => 'Undergraduate (Degree Programmes)',
                'pass_mark' => '40.0% (D+ Grade)',
                'min_credits_to_advance' => '36 Credits / Year (75% Load)',
                'supplementary_allowance' => 'Up to 4 failed units per year',
                'repeat_conditions' => 'Fail 5 to 7 units in an academic year',
                'discontinuation' => 'Fail > 7 units or 3rd consecutive failed year',
                'status' => 'Senate Approved',
            ],
            [
                'id' => 2,
                'rule_code' => 'PROG-PG-MSC',
                'programme_level' => 'Postgraduate Master Programmes',
                'pass_mark' => '50.0% (C Grade)',
                'min_credits_to_advance' => '100% Coursework Pass before Research Proposal',
                'supplementary_allowance' => 'Up to 2 failed units (1 re-sit sitting)',
                'repeat_conditions' => 'Fail supplementary exam in core unit',
                'discontinuation' => 'Fail research thesis defense upon 2nd resubmission',
                'status' => 'Senate Approved',
            ],
            [
                'id' => 3,
                'rule_code' => 'PROG-PG-PHD',
                'programme_level' => 'Doctor of Philosophy (PhD)',
                'pass_mark' => '60.0% (B Grade Coursework)',
                'min_credits_to_advance' => 'Comprehensive Exam Pass + Proposal Defence Pass',
                'supplementary_allowance' => '1 Re-take of Comprehensive Examination',
                'repeat_conditions' => 'Major revision of proposal within 6 months',
                'discontinuation' => 'Fail viva voce examination upon re-defence',
                'status' => 'Senate Approved',
            ],
        ];

        return view('curriculum.progression-criteria', compact('stats', 'rules'));
    }

    /**
     * 7. Specialisation
     */
    public function specialisation(Request $request): View
    {
        $stats = [
            'totalSpecialisations' => 28,
            'activeTracks' => 24,
            'undergraduateTracks' => 14,
            'postgraduateTracks' => 14,
        ];

        $specialisations = [
            [
                'id' => 1,
                'track_code' => 'SPEC-CS-AI',
                'track_name' => 'Artificial Intelligence & Machine Learning Track',
                'parent_programme' => 'Bachelor of Science in Computer Science',
                'start_semester' => 'Year 3, Semester 1',
                'specialised_units_count' => 8,
                'status' => 'Active',
            ],
            [
                'id' => 2,
                'track_code' => 'SPEC-CS-CYB',
                'track_name' => 'Cybersecurity & Digital Forensics Track',
                'parent_programme' => 'Bachelor of Science in Computer Science',
                'start_semester' => 'Year 3, Semester 1',
                'specialised_units_count' => 8,
                'status' => 'Active',
            ],
            [
                'id' => 3,
                'track_code' => 'SPEC-BBA-FIN',
                'track_name' => 'Corporate Finance & Investment Banking',
                'parent_programme' => 'Bachelor of Business Administration',
                'start_semester' => 'Year 3, Semester 1',
                'specialised_units_count' => 6,
                'status' => 'Active',
            ],
            [
                'id' => 4,
                'track_code' => 'SPEC-BBA-MKT',
                'track_name' => 'Digital Marketing & Growth Analytics',
                'parent_programme' => 'Bachelor of Business Administration',
                'start_semester' => 'Year 3, Semester 1',
                'specialised_units_count' => 6,
                'status' => 'Active',
            ],
        ];

        return view('curriculum.specialisation', compact('stats', 'specialisations'));
    }

    /**
     * 8. Short Course Creation
     */
    public function shortCourseCreation(Request $request): View
    {
        $stats = [
            'activeShortCourses' => 34,
            'microCredentials' => 18,
            'enrolledProfessionals' => 840,
            'completionRate' => '88.5%',
        ];

        $courses = [
            [
                'id' => 1,
                'course_code' => 'MEMA-SC-001',
                'course_title' => 'Executive Certificate in Generative AI for Business Leaders',
                'duration_weeks' => '6 Weeks (Self-Paced / Online)',
                'cpd_points' => '15 CPD Units',
                'tuition_fee' => 'KES 35,000',
                'facilitator' => 'Dr. Amina Hassan',
                'status' => 'Open for Enrolment',
            ],
            [
                'id' => 2,
                'course_code' => 'MEMA-SC-002',
                'course_title' => 'Data Protection & Privacy Compliance (Kenya DPA 2019)',
                'duration_weeks' => '4 Weeks (Live Masterclasses)',
                'cpd_points' => '10 CPD Units',
                'tuition_fee' => 'KES 25,000',
                'facilitator' => 'Prof. James Mwangi',
                'status' => 'Open for Enrolment',
            ],
            [
                'id' => 3,
                'course_code' => 'MEMA-SC-003',
                'course_title' => 'Public Sector Imprest & Financial Accounting Standards',
                'duration_weeks' => '3 Weeks (Online Hybrid)',
                'cpd_points' => '8 CPD Units',
                'tuition_fee' => 'KES 18,500',
                'facilitator' => 'Dr. Daniel Otieno',
                'status' => 'Open for Enrolment',
            ],
        ];

        return view('curriculum.short-course-creation', compact('stats', 'courses'));
    }

    /**
     * 9. Student Specialization Mapping
     */
    public function studentSpecializationMapping(Request $request): View
    {
        $stats = [
            'totalMappedScholars' => 412,
            'pendingTrackApproval' => 28,
            'aiTrackDistribution' => '44.2%',
            'cyberTrackDistribution' => '35.8%',
        ];

        $mappings = [
            [
                'id' => 1,
                'student_name' => 'Jasper Kiprop Koech',
                'reg_no' => 'BCS/2024/0088',
                'programme' => 'BSc Computer Science',
                'academic_year' => 'Year 3 (Semester 1)',
                'declared_specialisation' => 'Cybersecurity & Digital Forensics Track',
                'academic_advisor' => 'Dr. Amina Hassan',
                'status' => 'Approved & Enrolled',
            ],
            [
                'id' => 2,
                'student_name' => 'Bob Lamech Otieno',
                'reg_no' => 'BCS/2024/0104',
                'programme' => 'BSc Computer Science',
                'academic_year' => 'Year 3 (Semester 1)',
                'declared_specialisation' => 'Artificial Intelligence & Machine Learning Track',
                'academic_advisor' => 'Dr. Jeremiah Onunga',
                'status' => 'Approved & Enrolled',
            ],
            [
                'id' => 3,
                'student_name' => 'Jairus Otana',
                'reg_no' => 'BBA/2024/0312',
                'programme' => 'Bachelor of Business Administration',
                'academic_year' => 'Year 3 (Semester 1)',
                'declared_specialisation' => 'Corporate Finance & Investment Banking',
                'academic_advisor' => 'Dr. Daniel Otieno',
                'status' => 'Pending HOD Approval',
            ],
        ];

        return view('curriculum.student-specialization-mapping', compact('stats', 'mappings'));
    }

    /**
     * 10. School (CRUD Master)
     */
    public function school(Request $request): View
    {
        $schools = School::orderBy('id', 'asc')->get();

        $stats = [
            'totalSchools' => $schools->count(),
            'activeCampuses' => 1, // Virtual Open University
            'deanPositionsFilled' => $schools->whereNotNull('dean')->filter(fn ($s) => ! empty(trim((string) $s->dean)))->count(),
            'totalEnrolledStudents' => 3480,
            'totalDepartments' => $schools->sum('departments_count'),
            'totalProgrammes' => $schools->sum('programmes_count'),
        ];

        return view('curriculum.school', compact('stats', 'schools'));
    }

    public function storeSchool(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:30', 'unique:schools,code'],
            'name' => ['required', 'string', 'max:190'],
            'dean' => ['nullable', 'string', 'max:190'],
            'departments_count' => ['nullable', 'integer', 'min:0'],
            'programmes_count' => ['nullable', 'integer', 'min:0'],
            'email' => ['nullable', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:50'],
            'building' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:Active,Inactive'],
        ]);

        $school = School::create([
            'code' => strtoupper(trim($validated['code'])),
            'name' => trim($validated['name']),
            'dean' => $validated['dean'] ? trim($validated['dean']) : null,
            'departments_count' => (int) ($validated['departments_count'] ?? 0),
            'programmes_count' => (int) ($validated['programmes_count'] ?? 0),
            'email' => $validated['email'] ? strtolower(trim($validated['email'])) : null,
            'phone' => $validated['phone'] ? trim($validated['phone']) : null,
            'building' => $validated['building'] ? trim($validated['building']) : null,
            'description' => $validated['description'] ? trim($validated['description']) : null,
            'status' => $validated['status'],
        ]);

        try {
            $institution = \Illuminate\Support\Facades\DB::table('institutions')->first();
            if ($institution) {
                \Illuminate\Support\Facades\DB::table('faculties')->updateOrInsert(
                    ['institution_id' => $institution->id, 'code' => $school->code],
                    [
                        'id' => (string) \Illuminate\Support\Str::uuid(),
                        'name' => $school->name,
                        'is_active' => $school->status === 'Active',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        } catch (\Throwable) {
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "School '{$school->name}' created successfully.",
                'school' => $school,
            ]);
        }

        return redirect()->route('curriculum.school')->with('success', "School '{$school->name}' created successfully.");
    }

    public function updateSchool(Request $request, School $school): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:30', 'unique:schools,code,'.$school->id],
            'name' => ['required', 'string', 'max:190'],
            'dean' => ['nullable', 'string', 'max:190'],
            'departments_count' => ['nullable', 'integer', 'min:0'],
            'programmes_count' => ['nullable', 'integer', 'min:0'],
            'email' => ['nullable', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:50'],
            'building' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:Active,Inactive'],
        ]);

        $oldCode = $school->code;

        $school->update([
            'code' => strtoupper(trim($validated['code'])),
            'name' => trim($validated['name']),
            'dean' => $validated['dean'] ? trim($validated['dean']) : null,
            'departments_count' => (int) ($validated['departments_count'] ?? 0),
            'programmes_count' => (int) ($validated['programmes_count'] ?? 0),
            'email' => $validated['email'] ? strtolower(trim($validated['email'])) : null,
            'phone' => $validated['phone'] ? trim($validated['phone']) : null,
            'building' => $validated['building'] ? trim($validated['building']) : null,
            'description' => $validated['description'] ? trim($validated['description']) : null,
            'status' => $validated['status'],
        ]);

        try {
            $institution = \Illuminate\Support\Facades\DB::table('institutions')->first();
            if ($institution) {
                \Illuminate\Support\Facades\DB::table('faculties')->where('code', $oldCode)->update([
                    'code' => $school->code,
                    'name' => $school->name,
                    'is_active' => $school->status === 'Active',
                    'updated_at' => now(),
                ]);
            }
        } catch (\Throwable) {
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "School '{$school->name}' updated successfully.",
                'school' => $school,
            ]);
        }

        return redirect()->route('curriculum.school')->with('success', "School '{$school->name}' updated successfully.");
    }

    public function destroySchool(Request $request, School $school, RecycleBinService $recycleBin): RedirectResponse|JsonResponse
    {
        $validated = $request->validate(['deletion_reason' => ['required', 'string', 'min:10', 'max:500']]);
        $name = $school->name;
        $recycleBin->delete($school, $request->user(), 'school', $validated['deletion_reason'], route('curriculum.school'));

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "School '{$name}' deleted successfully.",
            ]);
        }

        return redirect()->route('curriculum.school')->with('success', "School '{$name}' deleted successfully.");
    }

    /**
     * 11. Program Type
     */
    public function programType(Request $request): View
    {
        $stats = [
            'totalProgramTypes' => 6,
            'knqaLevels' => 'KNQA Levels 5 through 10',
            'modularFlexibility' => 'Trimester & Competency Based',
            'status' => 'Active',
        ];

        $types = [
            [
                'id' => 1,
                'type_code' => 'PT-DEG-UG',
                'type_name' => 'Bachelor\'s Degree (Undergraduate)',
                'knqa_level' => 'KNQA Level 7',
                'min_duration_years' => 4,
                'standard_credit_hours' => 160,
                'status' => 'Active',
            ],
            [
                'id' => 2,
                'type_code' => 'PT-DEG-PG-MSC',
                'type_name' => 'Master\'s Degree (Postgraduate)',
                'knqa_level' => 'KNQA Level 8',
                'min_duration_years' => 2,
                'standard_credit_hours' => 60,
                'status' => 'Active',
            ],
            [
                'id' => 3,
                'type_code' => 'PT-DEG-PG-PHD',
                'type_name' => 'Doctor of Philosophy (PhD)',
                'knqa_level' => 'KNQA Level 9',
                'min_duration_years' => 3,
                'standard_credit_hours' => 120,
                'status' => 'Active',
            ],
            [
                'id' => 4,
                'type_code' => 'PT-DIP-HE',
                'type_name' => 'Higher Diploma / Diploma',
                'knqa_level' => 'KNQA Level 6',
                'min_duration_years' => 2,
                'standard_credit_hours' => 90,
                'status' => 'Active',
            ],
            [
                'id' => 5,
                'type_code' => 'PT-CERT-EXEC',
                'type_name' => 'Executive Certificate & Micro-Credentials',
                'knqa_level' => 'KNQA Level 5',
                'min_duration_years' => 0.5,
                'standard_credit_hours' => 20,
                'status' => 'Active',
            ],
        ];

        return view('curriculum.program-type', compact('stats', 'types'));
    }

    /**
     * 12. Programme (CRUD Master)
     */
    public function programme(Request $request): View
    {
        $programmes = AcademicProgramme::orderBy('id', 'asc')->get();
        $departments = AcademicDepartment::orderBy('name', 'asc')->get();
        $schools = School::orderBy('name', 'asc')->get();

        $stats = [
            'totalProgrammes' => $programmes->count(),
            'undergraduate' => $programmes->where('level', 'Undergraduate')->count(),
            'postgraduate' => $programmes->whereIn('level', ['Postgraduate', 'Doctoral'])->count(),
            'diplomaCertificate' => $programmes->whereIn('level', ['Diploma', 'Certificate'])->count(),
        ];

        return view('curriculum.programme', compact('stats', 'programmes', 'departments', 'schools'));
    }

    public function storeProgramme(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:40', 'unique:academic_programmes,code'],
            'title' => ['required', 'string', 'max:190'],
            'school' => ['nullable', 'string', 'max:190'],
            'department' => ['nullable', 'string', 'max:190'],
            'award' => ['nullable', 'string', 'max:190'],
            'cue_code' => ['nullable', 'string', 'max:60'],
            'level' => ['required', 'string', 'max:60'],
            'duration_semesters' => ['nullable', 'integer', 'min:1'],
            'total_credits' => ['nullable', 'integer', 'min:1'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:Active,Inactive'],
        ]);

        $programme = AcademicProgramme::create([
            'code' => strtoupper(trim($validated['code'])),
            'title' => trim($validated['title']),
            'school' => $validated['school'] ? trim($validated['school']) : null,
            'department' => $validated['department'] ? trim($validated['department']) : null,
            'award' => $validated['award'] ? trim($validated['award']) : null,
            'cue_code' => $validated['cue_code'] ? trim($validated['cue_code']) : null,
            'level' => $validated['level'],
            'duration_semesters' => (int) ($validated['duration_semesters'] ?? 8),
            'total_credits' => (int) ($validated['total_credits'] ?? 140),
            'description' => $validated['description'] ? trim($validated['description']) : null,
            'status' => $validated['status'],
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Programme '{$programme->title}' created successfully.",
                'programme' => $programme,
            ]);
        }

        return redirect()->route('curriculum.programme')->with('success', "Programme '{$programme->title}' created successfully.");
    }

    public function updateProgramme(Request $request, AcademicProgramme $programme): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:40', 'unique:academic_programmes,code,'.$programme->id],
            'title' => ['required', 'string', 'max:190'],
            'school' => ['nullable', 'string', 'max:190'],
            'department' => ['nullable', 'string', 'max:190'],
            'award' => ['nullable', 'string', 'max:190'],
            'cue_code' => ['nullable', 'string', 'max:60'],
            'level' => ['required', 'string', 'max:60'],
            'duration_semesters' => ['nullable', 'integer', 'min:1'],
            'total_credits' => ['nullable', 'integer', 'min:1'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:Active,Inactive'],
        ]);

        $programme->update([
            'code' => strtoupper(trim($validated['code'])),
            'title' => trim($validated['title']),
            'school' => $validated['school'] ? trim($validated['school']) : null,
            'department' => $validated['department'] ? trim($validated['department']) : null,
            'award' => $validated['award'] ? trim($validated['award']) : null,
            'cue_code' => $validated['cue_code'] ? trim($validated['cue_code']) : null,
            'level' => $validated['level'],
            'duration_semesters' => (int) ($validated['duration_semesters'] ?? 8),
            'total_credits' => (int) ($validated['total_credits'] ?? 140),
            'description' => $validated['description'] ? trim($validated['description']) : null,
            'status' => $validated['status'],
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Programme '{$programme->title}' updated successfully.",
                'programme' => $programme,
            ]);
        }

        return redirect()->route('curriculum.programme')->with('success', "Programme '{$programme->title}' updated successfully.");
    }

    public function destroyProgramme(Request $request, AcademicProgramme $programme, RecycleBinService $recycleBin): RedirectResponse|JsonResponse
    {
        $validated = $request->validate(['deletion_reason' => ['required', 'string', 'min:10', 'max:500']]);
        $title = $programme->title;
        $recycleBin->delete($programme, $request->user(), 'programme', $validated['deletion_reason'], route('curriculum.programme'));

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Programme '{$title}' moved to Recycle Bin.",
            ]);
        }

        return redirect()->route('curriculum.programme')->with('success', "Programme '{$title}' moved to Recycle Bin.");
    }

    /**
     * 13. Course Unit (CRUD Master)
     */
    public function courseUnit(Request $request): View
    {
        $units = AcademicCourseUnit::orderBy('id', 'asc')->get();
        $departments = AcademicDepartment::orderBy('name', 'asc')->get();

        $stats = [
            'totalUnits' => $units->count(),
            'coreUnits' => $units->where('classification', 'Core Unit')->count(),
            'electiveUnits' => $units->where('classification', '!=', 'Core Unit')->count(),
            'practicalLabUnits' => $units->where('practical_hours', '>', 0)->count(),
        ];

        return view('curriculum.course-unit', compact('stats', 'units', 'departments'));
    }

    public function storeCourseUnit(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'unit_code' => ['required', 'string', 'max:30', 'unique:academic_course_units,unit_code'],
            'unit_title' => ['required', 'string', 'max:190'],
            'department' => ['nullable', 'string', 'max:190'],
            'credit_hours' => ['required', 'integer', 'min:1'],
            'lecture_hours' => ['nullable', 'integer', 'min:0'],
            'practical_hours' => ['nullable', 'integer', 'min:0'],
            'classification' => ['required', 'string', 'max:60'],
            'prerequisites' => ['nullable', 'string', 'max:190'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:Active,Inactive'],
        ]);

        $unit = AcademicCourseUnit::create([
            'unit_code' => strtoupper(trim($validated['unit_code'])),
            'unit_title' => trim($validated['unit_title']),
            'department' => $validated['department'] ? trim($validated['department']) : null,
            'credit_hours' => (int) $validated['credit_hours'],
            'lecture_hours' => (int) ($validated['lecture_hours'] ?? 35),
            'practical_hours' => (int) ($validated['practical_hours'] ?? 0),
            'classification' => $validated['classification'],
            'prerequisites' => $validated['prerequisites'] ? trim($validated['prerequisites']) : 'None',
            'description' => $validated['description'] ? trim($validated['description']) : null,
            'status' => $validated['status'],
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Course Unit '{$unit->unit_code} - {$unit->unit_title}' created successfully.",
                'unit' => $unit,
            ]);
        }

        return redirect()->route('curriculum.course-unit')->with('success', "Course Unit '{$unit->unit_code} - {$unit->unit_title}' created successfully.");
    }

    public function updateCourseUnit(Request $request, AcademicCourseUnit $courseUnit): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'unit_code' => ['required', 'string', 'max:30', 'unique:academic_course_units,unit_code,'.$courseUnit->id],
            'unit_title' => ['required', 'string', 'max:190'],
            'department' => ['nullable', 'string', 'max:190'],
            'credit_hours' => ['required', 'integer', 'min:1'],
            'lecture_hours' => ['nullable', 'integer', 'min:0'],
            'practical_hours' => ['nullable', 'integer', 'min:0'],
            'classification' => ['required', 'string', 'max:60'],
            'prerequisites' => ['nullable', 'string', 'max:190'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:Active,Inactive'],
        ]);

        $courseUnit->update([
            'unit_code' => strtoupper(trim($validated['unit_code'])),
            'unit_title' => trim($validated['unit_title']),
            'department' => $validated['department'] ? trim($validated['department']) : null,
            'credit_hours' => (int) $validated['credit_hours'],
            'lecture_hours' => (int) ($validated['lecture_hours'] ?? 35),
            'practical_hours' => (int) ($validated['practical_hours'] ?? 0),
            'classification' => $validated['classification'],
            'prerequisites' => $validated['prerequisites'] ? trim($validated['prerequisites']) : 'None',
            'description' => $validated['description'] ? trim($validated['description']) : null,
            'status' => $validated['status'],
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Course Unit '{$courseUnit->unit_code}' updated successfully.",
                'unit' => $courseUnit,
            ]);
        }

        return redirect()->route('curriculum.course-unit')->with('success', "Course Unit '{$courseUnit->unit_code}' updated successfully.");
    }

    public function destroyCourseUnit(Request $request, AcademicCourseUnit $courseUnit, RecycleBinService $recycleBin): RedirectResponse|JsonResponse
    {
        $validated = $request->validate(['deletion_reason' => ['required', 'string', 'min:10', 'max:500']]);
        $code = $courseUnit->unit_code;
        $recycleBin->delete($courseUnit, $request->user(), 'course_unit', $validated['deletion_reason'], route('curriculum.course-unit'));

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Course Unit '{$code}' moved to Recycle Bin.",
            ]);
        }

        return redirect()->route('curriculum.course-unit')->with('success', "Course Unit '{$code}' moved to Recycle Bin.");
    }

    /**
     * 13a. Course Unit Bulk Upload Template (CSV)
     *
     * Column order and value vocabulary mirror storeCourseUnit() validation so a
     * completed template can be ingested without any further mapping.
     */
    public function courseUnitTemplate(Request $request): Response
    {
        $stream = fopen('php://temp', 'r+');
        // Escape character is passed explicitly: PHP 8.4+ deprecates relying on the default.
        fputcsv($stream, self::COURSE_UNIT_TEMPLATE_COLUMNS, ',', '"', '');
        foreach (self::COURSE_UNIT_TEMPLATE_SAMPLES as $sample) {
            fputcsv($stream, $sample, ',', '"', '');
        }
        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="mema-course-units-template.csv"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }
}
