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
        return [
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
                    'audit.log.view',
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
                    'institution.calendar.view', 'institution.calendar.manage',
                    'institution.grading-scale.view', 'institution.grading-scale.manage',
                    'curriculum.programme.view', 'curriculum.programme.manage',
                    'course.catalogue.view', 'course.offering.view', 'course.offering.manage',
                    'admission.application.view', 'admission.application.decide',
                    'student.record.view', 'student.record.update', 'student.record.view-sensitive',
                    'enrollment.registration.view', 'enrollment.registration.register-on-behalf',
                    'enrollment.registration.override',
                    'examination.marks.view', 'examination.marks.publish',
                    'graduation.clearance.view', 'graduation.clearance.clear',
                    'graduation.transcript.issue',
                    'analytics.dashboard.view',
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
                    'curriculum.programme.view', 'curriculum.programme.approve',
                    'course.catalogue.view', 'course.offering.view', 'course.offering.manage',
                    'course.offering.assign-lecturer',
                    'admission.application.view', 'admission.application.review',
                    'student.record.view',
                    'enrollment.registration.view', 'enrollment.registration.override',
                    'examination.marks.view', 'examination.marks.approve',
                    'graduation.clearance.view', 'graduation.clearance.clear',
                    'analytics.dashboard.view',
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
                    'curriculum.programme.view',
                    'course.catalogue.view', 'course.catalogue.manage',
                    'course.offering.view', 'course.offering.manage', 'course.offering.assign-lecturer',
                    'admission.application.view', 'admission.application.review',
                    'student.record.view',
                    'enrollment.registration.view',
                    'examination.marks.view', 'examination.marks.verify',
                    'graduation.clearance.view', 'graduation.clearance.clear',
                    'analytics.dashboard.view',
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
                    'student.record.view',
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
                    'admission.application.view',
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
    }
}
