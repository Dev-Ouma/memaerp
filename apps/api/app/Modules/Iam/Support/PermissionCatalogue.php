<?php

declare(strict_types=1);

namespace App\Modules\Iam\Support;

/**
 * The complete list of capabilities the software recognises.
 *
 * This lives in CODE, not in the database as editable data, and is seeded from here. The reason
 * is that a permission name is referenced by policies, controllers and tests — if an
 * administrator could rename or delete `examination.marks.approve` through a UI, they would
 * silently disable the check that protects it, and nothing would fail loudly.
 *
 * What administrators DO configure is which roles hold which permissions, and over what scope.
 *
 * `sensitive` marks capabilities whose use is worth alerting on rather than merely recording:
 * grade approval, fee waivers, impersonation, permission changes themselves.
 */
final class PermissionCatalogue
{
    /**
     * @return list<array{name: string, module: string, resource: string, action: string, description: string, is_sensitive: bool}>
     */
    public static function all(): array
    {
        $permissions = [];

        foreach (self::definitions() as $module => $resources) {
            foreach ($resources as $resource => $actions) {
                foreach ($actions as $action => $definition) {
                    [$description, $sensitive] = $definition;

                    $permissions[] = [
                        'name' => "{$module}.{$resource}.{$action}",
                        'module' => $module,
                        'resource' => $resource,
                        'action' => $action,
                        'description' => $description,
                        'is_sensitive' => $sensitive,
                    ];
                }
            }
        }

        return $permissions;
    }

    /**
     * @return array<string, array<string, array<string, array{0: string, 1: bool}>>>
     */
    private static function definitions(): array
    {
        return [
            'iam' => [
                'user' => [
                    'view' => ['View user accounts', false],
                    'create' => ['Create user accounts', true],
                    'update' => ['Modify user accounts', true],
                    'suspend' => ['Suspend or reactivate an account', true],
                    'reset-password' => ['Force a password reset', true],
                ],
                'role' => [
                    'view' => ['View roles and their permissions', false],
                    'create' => ['Create a role', true],
                    'update' => ['Change a role\'s permissions', true],
                    'assign' => ['Grant or revoke a role over a scope', true],
                ],
                'impersonation' => [
                    'start' => ['Sign in as another user for support purposes', true],
                ],
            ],

            'institution' => [
                'structure' => [
                    'view' => ['View campuses, faculties and departments', false],
                    'manage' => ['Create or modify the organisational structure', true],
                ],
                'calendar' => [
                    'view' => ['View academic years and terms', false],
                    'manage' => ['Define academic years, terms and their windows', true],
                ],
                'grading-scale' => [
                    'view' => ['View grading scales', false],
                    'manage' => ['Publish a new effective-dated grading scale', true],
                ],
            ],

            'curriculum' => [
                'programme' => [
                    'view' => ['View programmes', false],
                    'manage' => ['Create or amend programmes', false],
                    'approve' => ['Approve a curriculum version for use', true],
                ],
            ],

            'course' => [
                'catalogue' => [
                    'view' => ['View the course catalogue', false],
                    'manage' => ['Create or amend courses', false],
                ],
                'offering' => [
                    'view' => ['View course offerings for a term', false],
                    'manage' => ['Open offerings and set capacity', false],
                    'assign-lecturer' => ['Assign teaching staff to an offering', false],
                ],
            ],

            'admission' => [
                'application' => [
                    'view' => ['View applications', false],
                    'review' => ['Score and recommend on an application', false],
                    'decide' => ['Issue or decline an offer', true],
                ],
            ],

            'student' => [
                'record' => [
                    'view' => ['View student records', false],
                    'update' => ['Amend a student record', false],
                    'view-sensitive' => ['View government identifiers and protected fields', true],
                ],
            ],

            'enrollment' => [
                'registration' => [
                    'view' => ['View registrations', false],
                    'register' => ['Register for a term', false],
                    'register-on-behalf' => ['Register a student on their behalf', true],
                    'override' => ['Override a prerequisite or capacity block', true],
                ],
            ],

            'finance' => [
                'invoice' => [
                    'view' => ['View invoices and balances', false],
                    'issue' => ['Issue an invoice', false],
                ],
                'payment' => [
                    'view' => ['View payments and receipts', false],
                    'record' => ['Record a payment received', true],
                    'reverse' => ['Reverse a posted payment', true],
                ],
                'waiver' => [
                    'request' => ['Request a fee waiver', false],
                    'approve' => ['Approve a fee waiver', true],
                ],
            ],

            'examination' => [
                'marks' => [
                    'view' => ['View marks', false],
                    'enter' => ['Enter continuous assessment and exam marks', false],
                    'moderate' => ['Moderate a submitted marks batch', true],
                    'verify' => ['Verify a moderated marks batch', true],
                    'approve' => ['Approve marks for publication', true],
                    'publish' => ['Publish approved results to students', true],
                    'amend-published' => ['Amend a published result', true],
                ],
            ],

            'graduation' => [
                'clearance' => [
                    'view' => ['View graduation clearance status', false],
                    'clear' => ['Clear a student in your area', false],
                ],
                'transcript' => [
                    'issue' => ['Issue an official transcript', true],
                ],
            ],

            'hr' => [
                'employee' => [
                    'view' => ['View employee records', false],
                    'manage' => ['Create or amend employee records', true],
                ],
                'payroll' => [
                    'view' => ['View payroll runs', true],
                    'process' => ['Process a payroll run', true],
                    'approve' => ['Approve a payroll run for payment', true],
                ],
            ],

            'procurement' => [
                'requisition' => [
                    'create' => ['Raise a requisition', false],
                    'approve' => ['Approve a requisition', true],
                ],
                'purchase-order' => [
                    'issue' => ['Issue a purchase order', true],
                ],
            ],

            'research' => [
                'grant' => [
                    'view' => ['View research grants', false],
                    'manage' => ['Create or amend grant records', false],
                ],
            ],

            'audit' => [
                'log' => [
                    'view' => ['Read the audit trail', true],
                    'export' => ['Export audit records for an investigation', true],
                ],
            ],

            'cms' => [
                'content' => [
                    'view' => ['View website content', false],
                    'edit' => ['Draft and edit website content', false],
                    'publish' => ['Publish content to the public site', true],
                ],
            ],

            'analytics' => [
                'dashboard' => [
                    'view' => ['View institutional dashboards', false],
                    'view-executive' => ['View executive-level dashboards', true],
                ],
            ],
        ];
    }
}
