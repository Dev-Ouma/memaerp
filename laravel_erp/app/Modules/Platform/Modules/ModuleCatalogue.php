<?php

declare(strict_types=1);

namespace App\Modules\Platform\Modules;

/**
 * Canonical ERP module registry.
 *
 * Route groups, the module manager, maintenance lockdown, and the sidebar all read from here
 * so enabling or disabling a module is the same decision everywhere.
 */
final class ModuleCatalogue
{
    /**
     * @return array<string, array{
     *     name: string,
     *     icon: string,
     *     dependencies: list<string>,
     *     description: string,
     *     submodules: list<array{name: string, route: string|null}>,
     *     nav_group: string
     * }>
     */
    public static function all(): array
    {
        return [
            'curriculum' => [
                'name' => 'Curriculum & Programmes Catalogue',
                'icon' => 'library',
                'dependencies' => [],
                'description' => 'Define academic programmes, course units, credit loads and faculty ownership used by admissions, cohort and LMS.',
                'nav_group' => 'curriculum',
                'submodules' => [
                    ['name' => 'School', 'route' => 'curriculum.school'],
                    ['name' => 'Department', 'route' => 'curriculum.department'],
                    ['name' => 'Programme', 'route' => 'curriculum.programme'],
                    ['name' => 'Course Unit', 'route' => 'curriculum.course-unit'],
                ],
            ],
            'cohort' => [
                'name' => 'Cohort Setup & Academic Calendar',
                'icon' => 'calendar-range',
                'dependencies' => ['curriculum'],
                'description' => 'Academic years and cohort batches published onward to fees and registration.',
                'nav_group' => 'cohort',
                'submodules' => [
                    ['name' => 'Academic Year', 'route' => 'cohort.academic-year'],
                    ['name' => 'Cohort Creation', 'route' => 'cohort.cohort-creation'],
                    ['name' => 'Programme Mapping', 'route' => 'cohort.programme-cohort-mapping'],
                ],
            ],
            'admissions' => [
                'name' => 'Admissions & Intake Registry',
                'icon' => 'clipboard-check',
                'dependencies' => ['curriculum'],
                'description' => 'Application work queues, offers and conversion into student records.',
                'nav_group' => 'admissions',
                'submodules' => [
                    ['name' => 'Applications', 'route' => 'admissions.index'],
                    ['name' => 'Work Queues', 'route' => 'admissions.workspace.work-queues'],
                    ['name' => 'Offers', 'route' => 'admissions.workspace.offers'],
                ],
            ],
            'registration' => [
                'name' => 'Registration & Admissions Portal',
                'icon' => 'user-plus',
                'dependencies' => ['cohort', 'curriculum'],
                'description' => 'Course registration periods, nominal rolls and account provisioning from admitted students.',
                'nav_group' => 'registration',
                'submodules' => [
                    ['name' => 'User Registration', 'route' => 'registration.user-registration'],
                    ['name' => 'Course Registration', 'route' => 'registration.course-registration-periods'],
                ],
            ],
            'transfers' => [
                'name' => 'Student Transfers Registry',
                'icon' => 'arrow-left-right',
                'dependencies' => ['registration', 'curriculum'],
                'description' => 'Inter-programme transfers, deferrals and credit carry-forwards.',
                'nav_group' => 'transfers',
                'submodules' => [
                    ['name' => 'Transfer Applications', 'route' => 'transfers.index'],
                ],
            ],
            'lms' => [
                'name' => 'LMS Virtual Classrooms Portal',
                'icon' => 'book-open',
                'dependencies' => ['curriculum', 'registration'],
                'description' => 'Course shells and assessments bound to curriculum units and registered students.',
                'nav_group' => 'lms',
                'submodules' => [
                    ['name' => 'Course Shells', 'route' => 'lms.course-shells'],
                    ['name' => 'Assignments', 'route' => 'lms.assignments'],
                ],
            ],
            'examination' => [
                'name' => 'Examination & Grading Board',
                'icon' => 'file-pen',
                'dependencies' => ['registration'],
                'description' => 'Exam sittings, marks and transcripts that feed graduation.',
                'nav_group' => 'examination',
                'submodules' => [
                    ['name' => 'Exam Setup', 'route' => 'examination.index'],
                    ['name' => 'Results', 'route' => 'examination.summary-results'],
                ],
            ],
            'pg-research' => [
                'name' => 'PG Research & Graduate Studies',
                'icon' => 'book-marked',
                'dependencies' => ['registration', 'examination'],
                'description' => 'Research candidates, supervisors, theses and viva — independent of undergraduate exams.',
                'nav_group' => 'pg-research',
                'submodules' => [
                    ['name' => 'Research Register', 'route' => 'pg-research.index'],
                    ['name' => 'Supervisor Allocation', 'route' => 'pg-research.supervisor-allocation'],
                ],
            ],
            'student-affairs' => [
                'name' => 'Student Affairs & Work Study',
                'icon' => 'heart-handshake',
                'dependencies' => ['registration'],
                'description' => 'Work-study allocations against registered student records.',
                'nav_group' => 'student-affairs',
                'submodules' => [
                    ['name' => 'Work Study', 'route' => 'work-study.index'],
                ],
            ],
            'fees' => [
                'name' => 'Fees Billing & M-Pesa Integration',
                'icon' => 'credit-card',
                'dependencies' => ['cohort'],
                'description' => 'Invoices and receipts for students created from admissions conversion and cohort finance.',
                'nav_group' => 'fees',
                'submodules' => [
                    ['name' => 'Fee Payables', 'route' => 'fees.fee-payables'],
                    ['name' => 'Payments', 'route' => 'fees.pending-payments'],
                ],
            ],
            'graduation' => [
                'name' => 'Graduation & Alumni Registry',
                'icon' => 'award',
                'dependencies' => ['examination', 'fees'],
                'description' => 'Clearance and pass lists that require exam results and fee clearance.',
                'nav_group' => 'graduation',
                'submodules' => [
                    ['name' => 'Criteria', 'route' => 'graduation.criteria'],
                    ['name' => 'Alumni List', 'route' => 'graduation.alumni-list'],
                ],
            ],
            'smhr' => [
                'name' => 'SMHR — Staff HR & Payroll',
                'icon' => 'users',
                'dependencies' => [],
                'description' => 'Staff directory and payroll; college user accounts in task management bind to these profiles.',
                'nav_group' => 'smhr',
                'submodules' => [
                    ['name' => 'Staff Directory', 'route' => 'smhr.staff-directory'],
                    ['name' => 'Payroll', 'route' => 'smhr.payroll-register'],
                ],
            ],
            'imprest' => [
                'name' => 'Imprest Requisitions & Surrenders',
                'icon' => 'wallet-cards',
                'dependencies' => [],
                'description' => 'Petty-cash workflows for staff recorded in SMHR.',
                'nav_group' => 'imprest',
                'submodules' => [
                    ['name' => 'Requisitions', 'route' => 'imprest.index'],
                ],
            ],
            'service-providers' => [
                'name' => 'Service Providers & Procurement',
                'icon' => 'building-2',
                'dependencies' => [],
                'description' => 'Vendors and bills used by budgeting and imprest surrenders.',
                'nav_group' => 'service-providers',
                'submodules' => [
                    ['name' => 'Providers', 'route' => 'service-providers.index'],
                ],
            ],
            'budgeting' => [
                'name' => 'Budgeting & Capital Planning',
                'icon' => 'pie-chart',
                'dependencies' => [],
                'description' => 'Departmental budget proposals that constrain imprest and procurement.',
                'nav_group' => 'budgeting',
                'submodules' => [
                    ['name' => 'Budget Proposals', 'route' => 'budgeting.proposals'],
                ],
            ],
            'task-management' => [
                'name' => 'Task Management & College Users',
                'icon' => 'clipboard-check',
                'dependencies' => [],
                'description' => 'College user accounts, platform roles and operational task tickets used across every module.',
                'nav_group' => 'task-management',
                'submodules' => [
                    ['name' => 'College Users', 'route' => 'task-management.users'],
                    ['name' => 'Task Roles', 'route' => 'task-management.roles'],
                    ['name' => 'Task Manager', 'route' => 'task-management.task-manager'],
                ],
            ],
            'reports' => [
                'name' => 'Reports & Analytics Intelligence',
                'icon' => 'bar-chart-3',
                'dependencies' => [],
                'description' => 'Cross-module operational reports that read live admissions, fees and academic tables.',
                'nav_group' => 'reports',
                'submodules' => [
                    ['name' => 'Advanced Analytics', 'route' => 'reports.advanced-analytics'],
                    ['name' => 'User Details', 'route' => 'reports.user-details'],
                ],
            ],
            'recycle-bin' => [
                'name' => 'Recycle Bin & System Data Recovery',
                'icon' => 'trash-2',
                'dependencies' => [],
                'description' => 'Independent institutional recovery system to restore or purge soft-deleted schools, departments, programmes, units, and academic calendars.',
                'nav_group' => 'recycle-bin',
                'submodules' => [
                    ['name' => 'All Trashed Records', 'route' => 'admin.setups.recycle-bin.index'],
                ],
            ],
        ];
    }

    /** @return list<string> */
    public static function keys(): array
    {
        return array_keys(self::all());
    }

    public static function exists(string $key): bool
    {
        return array_key_exists($key, self::all());
    }

    public static function name(string $key): string
    {
        return self::all()[$key]['name'] ?? ucwords(str_replace('-', ' ', $key));
    }

    /** @return list<string> */
    public static function dependents(string $key): array
    {
        $dependents = [];
        foreach (self::all() as $candidate => $definition) {
            if (in_array($key, $definition['dependencies'], true)) {
                $dependents[] = $candidate;
            }
        }

        return $dependents;
    }
}
