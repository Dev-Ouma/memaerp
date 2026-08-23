<?php

declare(strict_types=1);

namespace App\Modules\Iam\Support;

use App\Modules\Iam\Models\Role;
use App\Modules\Iam\Models\RoleAssignment;
use App\Platform\Support\Scope;

/**
 * The system roles every institution starts with, and the scope each is normally granted at.
 *
 * `default_scope` is guidance for the granting UI, not enforcement — a Head of Department is
 * granted at DEPARTMENT scope because that is what the title means, but the scope actually
 * applied lives on the individual {@see RoleAssignment}.
 *
 * Note what is NOT here: a role holding every permission. The nearest thing, `system-admin`,
 * deliberately excludes marks approval and payroll approval. Whoever administers the system must
 * not also be able to change a grade or authorise a payment without leaving a second person's
 * fingerprints on it — that is segregation of duties, and it only works if the seed respects it.
 */
final class RoleCatalogue
{
    /**
     * @return list<array{code: string, name: string, family: string, description: string, default_scope: string, permissions: list<string>}>
     */
    public static function all(): array
    {
        $roles = [
            [
                'code' => 'system-admin',
                'name' => 'System Administrator',
                'family' => Role::FAMILY_SYSTEM,
                'description' => 'Administers accounts, roles and configuration. Deliberately cannot approve marks or payroll.',
                'default_scope' => Scope::INSTITUTION,
                'permissions' => [
                    'iam.user.view', 'iam.user.create', 'iam.user.update', 'iam.user.suspend',
                    'iam.user.reset-password',
                    'iam.role.view', 'iam.role.create', 'iam.role.update', 'iam.role.assign',
                    'iam.impersonation.start',
                    'institution.structure.view', 'institution.structure.manage',
                    'institution.calendar.view', 'institution.calendar.manage',
                    'institution.grading-scale.view',
                    'curriculum.programme.view', 'course.catalogue.view',
                    'audit.log.view',
                    'lms.sync.view', 'lms.sync.manage',
                    'attendance.report.view',
                ],
            ],
            [
                'code' => 'auditor',
                'name' => 'Internal Auditor',
                'family' => Role::FAMILY_SYSTEM,
                'description' => 'Reads the audit trail across the institution. Holds no write permission anywhere.',
                'default_scope' => Scope::INSTITUTION,
                'permissions' => [
                    'audit.log.view', 'audit.log.export',
                    'finance.invoice.view', 'finance.payment.view',
                    'examination.marks.view',
                    'student.record.view',
                    'hr.payroll.view',
                    'analytics.dashboard.view',
                ],
            ],
            [
                'code' => 'registrar-academic',
                'name' => 'Academic Registrar',
                'family' => Role::FAMILY_ADMINISTRATIVE,
                'description' => 'Owns the academic record: admissions decisions, registration, results publication, transcripts.',
                'default_scope' => Scope::INSTITUTION,
                'permissions' => [
                    'institution.structure.view',
                    'institution.calendar.view', 'institution.calendar.manage',
                    'institution.grading-scale.view', 'institution.grading-scale.manage',
                    'curriculum.programme.view', 'curriculum.programme.manage',
                    'course.catalogue.view', 'course.catalogue.manage', 'course.catalogue.approve',
                    'course.offering.view', 'course.offering.manage', 'course.offering.assign-lecturer',
                    'admission.application.view', 'admission.application.review', 'admission.application.decide',
                    'admission.prospect.manage', 'admission.kuccps.import',
                    'student.record.view', 'student.record.update', 'student.record.view-sensitive',
                    'student.record.matriculate', 'student.record.status',
                    'enrollment.registration.view', 'enrollment.registration.register-on-behalf',
                    'enrollment.registration.override',
                    'examination.marks.view', 'examination.marks.publish',
                    'graduation.clearance.view', 'graduation.clearance.clear',
                    'graduation.transcript.issue',
                    'analytics.dashboard.view',
                    'advising.assignment.manage', 'advising.advisee.view',
                ],
            ],
            [
                'code' => 'dean',
                'name' => 'Dean of Faculty',
                'family' => Role::FAMILY_ACADEMIC,
                'description' => 'Academic leadership of a faculty. Approves marks arising within the faculty.',
                'default_scope' => Scope::FACULTY,
                'permissions' => [
                    'institution.structure.view', 'institution.calendar.view',
                    'curriculum.programme.view', 'curriculum.programme.manage',
                    'course.catalogue.view', 'course.catalogue.approve',
                    'course.offering.view', 'course.offering.manage',
                    'course.offering.assign-lecturer',
                    'admission.application.view', 'admission.application.review',
                    'student.record.view',
                    'enrollment.registration.view', 'enrollment.registration.override',
                    'examination.marks.view', 'examination.marks.approve',
                    'graduation.clearance.view', 'graduation.clearance.clear',
                    'analytics.dashboard.view',
                    'attendance.report.view', 'attendance.override.manage',
                    'advising.assignment.manage', 'advising.advisee.view', 'advising.notes.manage',
                ],
            ],
            [
                'code' => 'head-of-department',
                'name' => 'Head of Department',
                'family' => Role::FAMILY_ACADEMIC,
                'description' => 'Runs a department: teaching allocation, marks verification, departmental clearance.',
                'default_scope' => Scope::DEPARTMENT,
                'permissions' => [
                    'institution.structure.view', 'institution.calendar.view',
                    'curriculum.programme.view', 'curriculum.programme.manage',
                    'course.catalogue.view', 'course.catalogue.manage', 'course.catalogue.approve',
                    'course.offering.view', 'course.offering.manage', 'course.offering.assign-lecturer',
                    'admission.application.view', 'admission.application.review',
                    'student.record.view',
                    'enrollment.registration.view',
                    'examination.marks.view', 'examination.marks.verify',
                    'graduation.clearance.view', 'graduation.clearance.clear',
                    'analytics.dashboard.view',
                    'attendance.report.view', 'attendance.override.manage',
                    'advising.assignment.manage', 'advising.advisee.view', 'advising.notes.manage',
                ],
            ],
            [
                'code' => 'lecturer',
                'name' => 'Lecturer',
                'family' => Role::FAMILY_ACADEMIC,
                'description' => 'Teaches assigned offerings and enters marks for them. Cannot approve their own marks.',
                'default_scope' => Scope::SELF,
                'permissions' => [
                    'institution.calendar.view',
                    'course.catalogue.view', 'course.offering.view',
                    'student.record.view',
                    'enrollment.registration.view',
                    'examination.marks.view', 'examination.marks.enter',
                    'attendance.session.manage', 'attendance.report.view',
                    'advising.advisee.view', 'advising.notes.manage',
                ],
            ],
            [
                'code' => 'exam-officer',
                'name' => 'Examinations Officer',
                'family' => Role::FAMILY_ADMINISTRATIVE,
                'description' => 'Moderates marks batches between entry and verification.',
                'default_scope' => Scope::FACULTY,
                'permissions' => [
                    'institution.calendar.view',
                    'course.offering.view',
                    'student.record.view',
                    'examination.marks.view', 'examination.marks.moderate',
                ],
            ],
            [
                'code' => 'finance-officer',
                'name' => 'Finance Officer',
                'family' => Role::FAMILY_ADMINISTRATIVE,
                'description' => 'Issues invoices and records payments. Reversals and waivers need the Bursar.',
                'default_scope' => Scope::INSTITUTION,
                'permissions' => [
                    'student.record.view',
                    'finance.invoice.view', 'finance.invoice.issue',
                    'finance.payment.view', 'finance.payment.record',
                    'analytics.dashboard.view',
                ],
            ],
            [
                'code' => 'bursar',
                'name' => 'Bursar',
                'family' => Role::FAMILY_EXECUTIVE,
                'description' => 'Institutional finance authority: reversals, waiver approval, payroll approval.',
                'default_scope' => Scope::INSTITUTION,
                'permissions' => [
                    'student.record.view',
                    'finance.invoice.view', 'finance.invoice.issue',
                    'finance.payment.view', 'finance.payment.record', 'finance.payment.reverse',
                    'finance.waiver.approve',
                    'hr.payroll.view', 'hr.payroll.approve',
                    'procurement.requisition.approve', 'procurement.purchase-order.issue',
                    'analytics.dashboard.view', 'analytics.dashboard.view-executive',
                ],
            ],
            [
                'code' => 'hr-officer',
                'name' => 'Human Resources Officer',
                'family' => Role::FAMILY_ADMINISTRATIVE,
                'description' => 'Maintains employee records and processes payroll. Cannot approve the run they processed.',
                'default_scope' => Scope::INSTITUTION,
                'permissions' => [
                    'hr.employee.view', 'hr.employee.manage',
                    'hr.payroll.view', 'hr.payroll.process',
                ],
            ],
            [
                'code' => 'admissions-officer',
                'name' => 'Admissions Officer',
                'family' => Role::FAMILY_ADMINISTRATIVE,
                'description' => 'Processes applications up to the point of decision.',
                'default_scope' => Scope::INSTITUTION,
                'permissions' => [
                    'curriculum.programme.view', 'course.catalogue.view',
                    'admission.application.view', 'admission.application.review',
                    'admission.prospect.manage',
                    'student.record.view', 'student.record.matriculate',
                ],
            ],
            [
                'code' => 'student',
                'name' => 'Student',
                'family' => Role::FAMILY_STUDENT,
                'description' => 'Sees and acts on their own record only. Every grant is SELF-scoped.',
                'default_scope' => Scope::SELF,
                'permissions' => [
                    'institution.calendar.view',
                    'curriculum.programme.view', 'course.catalogue.view', 'course.offering.view',
                    'student.record.view',
                    'enrollment.registration.view', 'enrollment.registration.register',
                    'finance.invoice.view', 'finance.payment.view', 'finance.waiver.request',
                    'examination.marks.view',
                    'graduation.clearance.view',
                    'lms.launch.view',
                    'attendance.checkin.self', 'attendance.record.view-self',
                    'advising.progress.view-self', 'advising.session.request',
                ],
            ],
            [
                'code' => 'applicant',
                'name' => 'Applicant',
                'family' => Role::FAMILY_STUDENT,
                'description' => 'Pre-admission access: submit and track an application, nothing else.',
                'default_scope' => Scope::SELF,
                'permissions' => [
                    'curriculum.programme.view', 'course.catalogue.view',
                    'admission.application.view', 'admission.application.submit',
                ],
            ],
            [
                'code' => 'vice-chancellor',
                'name' => 'Vice-Chancellor',
                'family' => Role::FAMILY_EXECUTIVE,
                'description' => 'Institutional oversight. Broad read, deliberately narrow write.',
                'default_scope' => Scope::INSTITUTION,
                'permissions' => [
                    'institution.structure.view', 'institution.calendar.view',
                    'curriculum.programme.view', 'course.catalogue.view', 'course.offering.view',
                    'admission.application.view',
                    'student.record.view',
                    'enrollment.registration.view',
                    'finance.invoice.view', 'finance.payment.view',
                    'examination.marks.view',
                    'graduation.clearance.view',
                    'hr.employee.view', 'hr.payroll.view',
                    'research.grant.view',
                    'analytics.dashboard.view', 'analytics.dashboard.view-executive',
                ],
            ],
            [
                'code' => 'content-editor',
                'name' => 'Content Editor',
                'family' => Role::FAMILY_ADMINISTRATIVE,
                'description' => 'Drafts public website content. Publishing is a separate permission.',
                'default_scope' => Scope::INSTITUTION,
                'permissions' => [
                    'cms.content.view', 'cms.content.edit',
                ],
            ],
        ];

        return array_merge($roles, self::extendedDirectory());
    }

    /**
     * Enterprise directory roles required by MOD-00-01. These deliberately reuse the nearest
     * least-privilege permission bundle; institutions may create custom roles but may not mutate
     * these system definitions.
     *
     * @return list<array{code: string, name: string, family: string, description: string, default_scope: string, permissions: list<string>}>
     */
    private static function extendedDirectory(): array
    {
        $read = ['institution.structure.view', 'institution.calendar.view', 'analytics.dashboard.view'];
        $academic = array_merge($read, ['curriculum.programme.view', 'course.catalogue.view', 'course.offering.view', 'student.record.view', 'examination.marks.view']);
        $finance = array_merge($read, ['student.record.view', 'finance.invoice.view', 'finance.payment.view']);
        $student = ['institution.calendar.view', 'student.record.view', 'finance.invoice.view', 'finance.payment.view', 'examination.marks.view'];

        $definitions = [
            ['vc-designee', 'Vice Chancellor Designee', Role::FAMILY_EXECUTIVE, Scope::INSTITUTION, $academic],
            ['dvc-academic', 'Deputy Vice Chancellor - Academic & Student Affairs', Role::FAMILY_EXECUTIVE, Scope::INSTITUTION, $academic],
            ['dvc-finance', 'Deputy Vice Chancellor - Finance & Administration', Role::FAMILY_EXECUTIVE, Scope::INSTITUTION, $finance],
            ['dvc-research', 'Deputy Vice Chancellor - Research & Innovation', Role::FAMILY_EXECUTIVE, Scope::INSTITUTION, array_merge($read, ['research.grant.view'])],
            ['deputy-dean', 'Deputy Dean', Role::FAMILY_ACADEMIC, Scope::FACULTY, $academic],
            ['campus-director', 'Institute / Campus Director', Role::FAMILY_EXECUTIVE, Scope::CAMPUS, $read],
            ['deputy-registrar', 'Deputy Academic Registrar', Role::FAMILY_ADMINISTRATIVE, Scope::INSTITUTION, $academic],
            ['graduate-school-admin', 'Graduate School Administrator', Role::FAMILY_ADMINISTRATIVE, Scope::INSTITUTION, $academic],
            ['programme-coordinator', 'Programme Coordinator', Role::FAMILY_ACADEMIC, Scope::DEPARTMENT, $academic],
            ['academic-advisor', 'Academic Advisor / Mentor', Role::FAMILY_ACADEMIC, Scope::SELF, array_merge($academic, ['advising.advisee.view', 'advising.notes.manage'])],
            ['trainer', 'Skills & Workshop Trainer', Role::FAMILY_ACADEMIC, Scope::SELF, ['course.catalogue.view', 'course.offering.view', 'examination.marks.enter']],
            ['exam-admin', 'Examination Administrator', Role::FAMILY_ADMINISTRATIVE, Scope::INSTITUTION, ['student.record.view', 'examination.marks.view', 'examination.marks.moderate']],
            ['exam-coordinator', 'Faculty / Department Exam Coordinator', Role::FAMILY_ADMINISTRATIVE, Scope::FACULTY, ['student.record.view', 'examination.marks.view', 'examination.marks.verify']],
            ['exam-examiner', 'Internal / External Examiner', Role::FAMILY_ACADEMIC, Scope::SELF, ['course.offering.view', 'examination.marks.view', 'examination.marks.enter']],
            ['marks-processor', 'Central Marks Processing Officer', Role::FAMILY_ADMINISTRATIVE, Scope::INSTITUTION, ['student.record.view', 'examination.marks.view', 'examination.marks.moderate']],
            ['results-officer', 'Results Compilation & Transcripts Officer', Role::FAMILY_ADMINISTRATIVE, Scope::INSTITUTION, ['student.record.view', 'examination.marks.view', 'graduation.transcript.issue']],
            ['exam-board-secretary', 'Senate Examination Board Secretary', Role::FAMILY_ADMINISTRATIVE, Scope::INSTITUTION, ['examination.marks.view', 'examination.marks.publish']],
            ['student-finance-accountant', 'Student Finance Accountant', Role::FAMILY_ADMINISTRATIVE, Scope::INSTITUTION, $finance],
            ['payments-accountant', 'Payments & Disbursements Accountant', Role::FAMILY_ADMINISTRATIVE, Scope::INSTITUTION, array_merge($finance, ['finance.payment.record'])],
            ['budget-accountant', 'Budget & Planning Accountant', Role::FAMILY_ADMINISTRATIVE, Scope::INSTITUTION, $finance],
            ['budget-officer', 'Budget Control Officer', Role::FAMILY_ADMINISTRATIVE, Scope::INSTITUTION, $finance],
            ['finance-examiner', 'Internal Financial Auditor / Examiner', Role::FAMILY_SYSTEM, Scope::INSTITUTION, $finance],
            ['cashier', 'University Cashier / Point of Sale', Role::FAMILY_ADMINISTRATIVE, Scope::CAMPUS, ['student.record.view', 'finance.invoice.view', 'finance.payment.view', 'finance.payment.record']],
            ['procurement-manager', 'Head of Procurement & Supply Chain', Role::FAMILY_ADMINISTRATIVE, Scope::INSTITUTION, ['procurement.requisition.approve', 'procurement.purchase-order.issue']],
            ['procurement-officer', 'Procurement / Purchasing Officer', Role::FAMILY_ADMINISTRATIVE, Scope::INSTITUTION, ['procurement.requisition.create']],
            ['tender-committee', 'Tender & Evaluation Committee Member', Role::FAMILY_ADMINISTRATIVE, Scope::INSTITUTION, ['procurement.requisition.approve']],
            ['graduate', 'Graduating Candidate', Role::FAMILY_STUDENT, Scope::SELF, $student],
            ['alumni', 'University Alumni', Role::FAMILY_STUDENT, Scope::SELF, ['student.record.view', 'graduation.transcript.issue']],
            ['dean-of-students', 'Dean of Students', Role::FAMILY_EXECUTIVE, Scope::INSTITUTION, array_merge($read, ['student.record.view'])],
            ['student-affairs-officer', 'Student Affairs & Welfare Officer', Role::FAMILY_ADMINISTRATIVE, Scope::INSTITUTION, ['student.record.view']],
            ['accommodation-officer', 'Hostels & Accommodation Officer', Role::FAMILY_ADMINISTRATIVE, Scope::CAMPUS, ['student.record.view']],
            ['counselling-officer', 'Guidance & Counselling Officer', Role::FAMILY_ADMINISTRATIVE, Scope::INSTITUTION, ['student.record.view-sensitive']],
            ['librarian', 'University Librarian', Role::FAMILY_ADMINISTRATIVE, Scope::INSTITUTION, $read],
            ['assistant-librarian', 'Assistant Librarian / Cataloguer', Role::FAMILY_ADMINISTRATIVE, Scope::CAMPUS, $read],
            ['election-commissioner', 'University Election Commissioner', Role::FAMILY_ADMINISTRATIVE, Scope::INSTITUTION, $read],
            ['returning-officer', 'Student Union Election Returning Officer', Role::FAMILY_ADMINISTRATIVE, Scope::CAMPUS, $read],
            ['senate-member', 'Senate Committee Member', Role::FAMILY_EXECUTIVE, Scope::INSTITUTION, array_merge($academic, ['curriculum.programme.manage', 'curriculum.programme.approve', 'course.catalogue.manage', 'course.catalogue.approve', 'course.offering.manage', 'course.offering.assign-lecturer'])],
            ['pdc-coordinator', 'Professional Development Centre Coordinator', Role::FAMILY_ADMINISTRATIVE, Scope::INSTITUTION, $read],
            ['ict-security', 'ICT Security Officer', Role::FAMILY_SYSTEM, Scope::INSTITUTION, ['iam.user.view', 'iam.user.suspend', 'iam.role.view', 'audit.log.view', 'lms.sync.view', 'lms.sync.manage']],
            ['user-support', 'ICT Helpdesk & User Support', Role::FAMILY_SYSTEM, Scope::INSTITUTION, ['iam.user.view', 'iam.user.reset-password']],
        ];

        return array_values(array_map(
            static fn (array $definition): array => [
                'code' => $definition[0], 'name' => $definition[1], 'family' => $definition[2],
                'description' => $definition[1].' enterprise system role.',
                'default_scope' => $definition[3], 'permissions' => $definition[4],
            ],
            $definitions,
        ));
    }
}
