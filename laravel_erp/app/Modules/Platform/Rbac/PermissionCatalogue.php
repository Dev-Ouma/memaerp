<?php

declare(strict_types=1);

namespace App\Modules\Platform\Rbac;

/**
 * The single source of truth for what may be permitted and to whom.
 *
 * Two rules are enforced here rather than left to configuration, because they are institutional policy
 * and not deployment preference:
 *
 * 1. Deny by default. A permission that is not listed does not exist, and a role that is not granted a
 *    permission does not have it. There is no implicit inheritance and no super-role.
 * 2. Segregation of duties. Permissions marked `segregated` are excluded from `system_administrator`
 *    by construction — administering the platform is not the same as deciding who gets admitted or
 *    reading a declared disability. `assertSegregationOfDuties()` proves it, and a test calls it.
 */
final class PermissionCatalogue
{
    public const SCOPE_TYPES = [
        'institution', 'campus', 'faculty', 'department', 'programme', 'intake', 'offering', 'self',
    ];

    /**
     * code => [module, resource, action, classification, segregated, description]
     *
     * @return array<string, array{module: string, resource: string, action: string, classification: string, segregated: bool, description: string}>
     */
    public static function permissions(): array
    {
        $definitions = [
            // Configuration ------------------------------------------------------------------------
            ['admission.programme.view', 'internal', false, 'View programmes and offerings in the back office'],
            ['admission.programme.manage', 'internal', false, 'Create and amend programmes'],
            ['admission.intake.manage', 'internal', false, 'Open, close and configure intakes'],
            ['admission.offering.manage', 'internal', false, 'Configure programme offerings, capacity and requirements'],
            ['admission.offering.publish', 'internal', false, 'Publish or unpublish an offering to the public catalogue'],
            ['admission.fee_setup.manage', 'confidential', false, 'Define application fee setups and amounts'],

            // Applications -------------------------------------------------------------------------
            ['admission.application.view', 'confidential', false, 'View applications within the assigned scope'],
            ['admission.application.view_any', 'confidential', false, 'View applications across the whole institution'],
            ['admission.application.export', 'confidential', false, 'Export application data'],
            ['admission.application.reassign', 'internal', false, 'Move an application between departments or reviewers'],
            ['admission.application.authorise_duplicate', 'internal', false, 'Permit a second live application for the same offering'],
            ['admission.application.withdraw', 'confidential', false, 'Withdraw an application on the applicant\'s behalf'],
            ['admission.identity.view', 'restricted', true, 'Read unmasked identity numbers'],
            ['admission.support_needs.view', 'restricted', true, 'Read declared disability and support information'],

            // Documents ----------------------------------------------------------------------------
            ['admission.document.view', 'confidential', false, 'List application documents'],
            ['admission.document.download', 'confidential', false, 'Download an application document'],
            ['admission.document.verify', 'confidential', false, 'Record a document verification outcome'],
            ['admission.document.request', 'internal', false, 'Request additional or replacement documents'],

            // Payments -----------------------------------------------------------------------------
            ['admission.payment.view', 'confidential', false, 'View payment transactions and receipts'],
            ['admission.payment.record_manual', 'confidential', true, 'Record a bank or cashier payment'],
            ['admission.payment.waive', 'confidential', true, 'Waive the application fee'],
            ['admission.payment.reconcile', 'confidential', true, 'Run and resolve payment reconciliation'],
            ['admission.payment.refund', 'confidential', true, 'Record a refund or reversal'],

            // Review and decision ------------------------------------------------------------------
            ['admission.review.assign', 'internal', false, 'Assign applications to reviewers'],
            ['admission.review.perform', 'confidential', false, 'Score and comment on an assigned application'],
            ['admission.review.view', 'confidential', false, 'Read reviews recorded by others'],
            ['admission.eligibility.evaluate', 'internal', false, 'Run or re-run eligibility evaluation'],
            ['admission.shortlist.manage', 'confidential', false, 'Add to and remove from shortlists and waitlists'],
            ['admission.decision.recommend', 'confidential', false, 'Recommend admission or rejection'],
            ['admission.decision.approve', 'confidential', true, 'Approve a recommendation at an intermediate stage'],
            ['admission.decision.final', 'confidential', true, 'Record the final admission decision'],
            ['admission.decision.check', 'confidential', true, 'Counter-sign a decision batch (maker-checker)'],

            // Offers, letters and rolls ------------------------------------------------------------
            ['admission.offer.issue', 'confidential', false, 'Issue an admission offer'],
            ['admission.offer.revoke', 'confidential', true, 'Revoke an issued offer'],
            ['admission.offer.view', 'confidential', false, 'View offers'],
            ['admission.letter.generate', 'confidential', false, 'Generate or regenerate an admission letter'],
            ['admission.appeal.review', 'confidential', false, 'Review an admission appeal'],
            ['admission.deferral.approve', 'confidential', false, 'Approve a deferral request'],
            ['admission.roll.manage', 'confidential', false, 'Build and amend admission rolls'],
            ['admission.roll.publish', 'confidential', true, 'Publish and freeze an admission roll'],
            ['admission.conversion.execute', 'confidential', true, 'Convert accepted applicants into student records'],

            // Insight ------------------------------------------------------------------------------
            ['admission.analytics.view', 'internal', false, 'View admission dashboards'],
            ['admission.report.view', 'confidential', false, 'Run admission reports'],
            ['admission.report.export', 'confidential', false, 'Export admission reports'],

            // Curriculum ---------------------------------------------------------------------------
            ['curriculum.view', 'internal', false, 'View schools, departments, programmes and course units'],
            ['curriculum.manage', 'internal', false, 'Create, amend and archive curriculum structures'],

            // Staff & HR ---------------------------------------------------------------------------
            ['smhr.view', 'confidential', false, 'View staff directory, leave and HR dashboards'],
            ['smhr.staff.manage', 'confidential', false, 'Create and amend staff employment records'],
            ['smhr.leave.submit', 'internal', false, 'Submit leave applications on behalf of staff'],
            ['smhr.leave.approve', 'confidential', false, 'Approve or reject staff leave requests'],

            // Operational modules (module_records write path) --------------------------------------
            ['fees.manage', 'confidential', false, 'Create and amend fee structures, accounts and payables'],
            ['registration.manage', 'confidential', false, 'Manage registration periods, promotions and student registration records'],
            ['transfers.manage', 'confidential', false, 'Manage transfer windows, exemptions and credit transfers'],
            ['lms.manage', 'internal', false, 'Manage LMS course shells, assignments and sync ledgers'],
            ['graduation.manage', 'confidential', false, 'Manage graduation criteria, clearance and ceremony records'],
            ['imprest.manage', 'confidential', false, 'Manage imprest requisitions, surrenders and permissions'],
            ['student_affairs.manage', 'confidential', false, 'Manage work-study periods, positions and claims'],
            ['service_providers.manage', 'confidential', false, 'Manage vendors, bills and provider payments'],

            // Platform -----------------------------------------------------------------------------
            ['platform.user.manage', 'confidential', false, 'Create and deactivate user accounts'],
            ['platform.role.manage', 'confidential', false, 'Grant and revoke roles'],
            ['platform.audit.view', 'confidential', false, 'Read the audit trail'],
            ['platform.system.configure', 'internal', false, 'Change system configuration'],
            ['platform.integration.manage', 'confidential', false, 'Configure payment and messaging integrations'],
            ['platform.retention.execute', 'confidential', true, 'Execute data retention and erasure runs'],
        ];

        $catalogue = [];

        foreach ($definitions as [$code, $classification, $segregated, $description]) {
            [$module, $resource, $action] = array_pad(explode('.', $code, 3), 3, '');
            $catalogue[$code] = [
                'module' => $module,
                'resource' => $resource,
                'action' => $action,
                'classification' => $classification,
                'segregated' => $segregated,
                'description' => $description,
            ];
        }

        return $catalogue;
    }

    /**
     * @return array<string, array{name: string, description: string, default_scope_type: string, permissions: list<string>}>
     */
    public static function roles(): array
    {
        $reviewer = [
            'admission.application.view', 'admission.document.view', 'admission.document.download',
            'admission.review.perform', 'admission.review.view', 'admission.eligibility.evaluate',
            'admission.programme.view', 'admission.analytics.view',
        ];

        return [
            'applicant' => [
                'name' => 'Applicant',
                'description' => 'A prospective student acting on their own application only.',
                'default_scope_type' => 'self',
                // Applicants act through self-service endpoints that assert ownership directly; they
                // hold no back-office permission at all.
                'permissions' => [],
            ],
            'admissions_officer' => [
                'name' => 'Admissions Officer',
                'description' => 'Day-to-day processing: intake desk, document verification, correspondence.',
                'default_scope_type' => 'institution',
                'permissions' => [
                    'admission.programme.view', 'admission.application.view', 'admission.application.view_any',
                    'admission.application.reassign', 'admission.application.withdraw',
                    'admission.document.view', 'admission.document.download', 'admission.document.verify',
                    'admission.document.request', 'admission.payment.view', 'admission.review.assign',
                    'admission.review.view', 'admission.eligibility.evaluate', 'admission.shortlist.manage',
                    'admission.decision.recommend', 'admission.offer.view', 'admission.letter.generate',
                    'admission.roll.manage', 'admission.analytics.view', 'admission.report.view',
                    'admission.report.export', 'admission.application.export',
                ],
            ],
            'admissions_reviewer' => [
                'name' => 'Admissions Reviewer',
                'description' => 'Academic reviewer scoring applications assigned to them.',
                'default_scope_type' => 'department',
                'permissions' => $reviewer,
            ],
            'head_of_department' => [
                'name' => 'Head of Department',
                'description' => 'Departmental approver between recommendation and final decision.',
                'default_scope_type' => 'department',
                'permissions' => array_merge($reviewer, [
                    'admission.review.assign', 'admission.shortlist.manage', 'admission.decision.recommend',
                    'admission.decision.approve', 'admission.offer.view', 'admission.report.view',
                ]),
            ],
            'registrar' => [
                'name' => 'Registrar',
                'description' => 'Holds final admission authority and publishes admission rolls.',
                'default_scope_type' => 'institution',
                'permissions' => [
                    'admission.programme.view', 'admission.application.view', 'admission.application.view_any',
                    'admission.document.view', 'admission.document.download', 'admission.review.view',
                    'admission.shortlist.manage', 'admission.decision.recommend', 'admission.decision.approve',
                    'admission.decision.final', 'admission.decision.check', 'admission.offer.issue',
                    'admission.offer.revoke', 'admission.offer.view', 'admission.letter.generate',
                    'admission.appeal.review', 'admission.deferral.approve', 'admission.roll.manage',
                    'admission.roll.publish', 'admission.conversion.execute', 'admission.analytics.view',
                    'admission.report.view', 'admission.report.export', 'admission.application.export',
                    'admission.identity.view', 'admission.application.authorise_duplicate',
                    'registration.manage', 'graduation.manage', 'transfers.manage',
                ],
            ],
            'finance_officer' => [
                'name' => 'Finance Officer',
                'description' => 'Owns application fees and institutional fee ledgers, waivers and reconciliation.',
                'default_scope_type' => 'institution',
                'permissions' => [
                    'admission.application.view', 'admission.payment.view', 'admission.payment.record_manual',
                    'admission.payment.waive', 'admission.payment.reconcile', 'admission.payment.refund',
                    'admission.fee_setup.manage', 'admission.analytics.view', 'admission.report.view',
                    'admission.report.export', 'fees.manage', 'imprest.manage', 'service_providers.manage',
                ],
            ],
            'admissions_manager' => [
                'name' => 'Admissions Manager',
                'description' => 'Configures intakes and offerings and oversees the pipeline.',
                'default_scope_type' => 'institution',
                'permissions' => [
                    'admission.programme.view', 'admission.programme.manage', 'admission.intake.manage',
                    'admission.offering.manage', 'admission.offering.publish', 'admission.application.view',
                    'admission.application.view_any', 'admission.application.reassign',
                    'admission.application.authorise_duplicate', 'admission.document.view',
                    'admission.document.request', 'admission.payment.view', 'admission.review.assign',
                    'admission.review.view', 'admission.eligibility.evaluate', 'admission.shortlist.manage',
                    'admission.decision.recommend', 'admission.decision.approve', 'admission.offer.view',
                    'admission.roll.manage', 'admission.analytics.view', 'admission.report.view',
                    'admission.report.export', 'admission.application.export',
                ],
            ],
            'system_administrator' => [
                'name' => 'System Administrator',
                'description' => 'Operates the platform. Holds no admission-decision or restricted-data authority.',
                'default_scope_type' => 'institution',
                'permissions' => [
                    'platform.user.manage', 'platform.role.manage', 'platform.audit.view',
                    'platform.system.configure', 'platform.integration.manage',
                    'admission.programme.view', 'admission.application.view', 'admission.application.view_any',
                    'admission.offer.view', 'admission.analytics.view', 'admission.report.view',
                    'curriculum.view', 'curriculum.manage',
                    'smhr.view', 'smhr.staff.manage', 'smhr.leave.submit', 'smhr.leave.approve',
                    'fees.manage', 'registration.manage', 'transfers.manage', 'lms.manage',
                    'graduation.manage', 'imprest.manage', 'student_affairs.manage', 'service_providers.manage',
                ],
            ],
            'curriculum_manager' => [
                'name' => 'Curriculum Manager',
                'description' => 'Maintains schools, departments, programmes and course units.',
                'default_scope_type' => 'institution',
                'permissions' => [
                    'curriculum.view', 'curriculum.manage',
                ],
            ],
            'hr_officer' => [
                'name' => 'HR Officer',
                'description' => 'Owns staff records and leave approvals in SMHR.',
                'default_scope_type' => 'institution',
                'permissions' => [
                    'smhr.view', 'smhr.staff.manage', 'smhr.leave.submit', 'smhr.leave.approve',
                    'platform.user.manage',
                ],
            ],
            'registration_officer' => [
                'name' => 'Registration Officer',
                'description' => 'Manages registration periods, promotions and student registration records.',
                'default_scope_type' => 'institution',
                'permissions' => ['registration.manage'],
            ],
            'transfers_officer' => [
                'name' => 'Transfers Officer',
                'description' => 'Manages transfer windows, exemptions and credit transfers.',
                'default_scope_type' => 'institution',
                'permissions' => ['transfers.manage'],
            ],
            'lms_manager' => [
                'name' => 'LMS Manager',
                'description' => 'Manages LMS course shells, assignments and sync ledgers.',
                'default_scope_type' => 'institution',
                'permissions' => ['lms.manage'],
            ],
            'graduation_officer' => [
                'name' => 'Graduation Officer',
                'description' => 'Manages graduation criteria, clearance and ceremony records.',
                'default_scope_type' => 'institution',
                'permissions' => ['graduation.manage'],
            ],
            'student_affairs_officer' => [
                'name' => 'Student Affairs Officer',
                'description' => 'Manages work-study periods, positions, allocations and claims.',
                'default_scope_type' => 'institution',
                'permissions' => ['student_affairs.manage'],
            ],
            'auditor' => [
                'name' => 'Auditor',
                'description' => 'Read-only oversight across the module.',
                'default_scope_type' => 'institution',
                'permissions' => [
                    'platform.audit.view', 'admission.application.view', 'admission.application.view_any',
                    'admission.payment.view', 'admission.review.view', 'admission.offer.view',
                    'admission.analytics.view', 'admission.report.view',
                    'curriculum.view', 'smhr.view',
                ],
            ],
            'data_protection_officer' => [
                'name' => 'Data Protection Officer',
                'description' => 'Handles subject-access, retention and restricted support information.',
                'default_scope_type' => 'institution',
                'permissions' => [
                    'platform.audit.view', 'platform.retention.execute', 'admission.application.view',
                    'admission.application.view_any', 'admission.identity.view', 'admission.support_needs.view',
                    'admission.document.view', 'admission.document.download', 'admission.report.view',
                    'smhr.view',
                ],
            ],
        ];
    }

    /** @return list<string> */
    public static function segregatedPermissions(): array
    {
        return array_keys(array_filter(self::permissions(), static fn (array $p): bool => $p['segregated']));
    }

    /**
     * Fails loudly when a role bundle references an unknown permission, or when the administrator role
     * has acquired an authority it must never hold. Called by the seeder and by a unit test, so a
     * careless edit to the bundles above cannot ship.
     *
     * @return list<string> violations, empty when the catalogue is sound
     */
    public static function violations(): array
    {
        $permissions = self::permissions();
        $violations = [];

        foreach (self::roles() as $code => $role) {
            foreach ($role['permissions'] as $permission) {
                if (! isset($permissions[$permission])) {
                    $violations[] = "Role {$code} references unknown permission {$permission}.";
                }
            }

            if (! in_array($role['default_scope_type'], self::SCOPE_TYPES, true)) {
                $violations[] = "Role {$code} has unknown default scope {$role['default_scope_type']}.";
            }
        }

        foreach (['system_administrator'] as $code) {
            foreach (array_intersect(self::roles()[$code]['permissions'], self::segregatedPermissions()) as $permission) {
                $violations[] = "Segregation of duties: {$code} must not hold {$permission}.";
            }
        }

        return $violations;
    }
}
