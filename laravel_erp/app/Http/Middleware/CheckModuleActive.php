<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\ModuleState;
use App\Models\SystemMaintenanceConfig;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CheckModuleActive
 *
 * Apply to any route group with: ->middleware('module:registration')
 * When the module is disabled, non-admin users get a styled 503 page.
 * Admins always pass through so they can re-enable modules.
 */
final class CheckModuleActive
{
    public function handle(Request $request, Closure $next, string $moduleKey): Response
    {
        // Admins can always access everything
        if ($request->user()?->isAdmin()) {
            return $next($request);
        }

        // Check if module is locked down in system maintenance configs
        $config = SystemMaintenanceConfig::first();
        if ($config && is_array($config->locked_modules) && in_array($moduleKey, $config->locked_modules, true)) {
            return response()->view('errors.module-disabled', [
                'moduleKey' => $moduleKey,
                'moduleName' => $this->humanName($moduleKey).' (Locked down for system maintenance)',
            ], 503);
        }

        if (! ModuleState::isActive($moduleKey)) {
            return response()->view('errors.module-disabled', [
                'moduleKey' => $moduleKey,
                'moduleName' => $this->humanName($moduleKey),
            ], 503);
        }

        return $next($request);
    }

    private function humanName(string $key): string
    {
        $map = [
            'smhr' => 'SMHR — Staff HR & Payroll',
            'transfers' => 'Student Transfers Registry',
            'pg-research' => 'PG Research & Graduate Studies',
            'curriculum' => 'Curriculum Setup',
            'student-affairs' => 'Student Affairs & Work Study',
            'imprest' => 'Imprest Management',
            'cohort' => 'Cohort Setup',
            'registration' => 'Registration & Admissions',
            'lms' => 'LMS Virtual Classrooms',
            'examination' => 'Examination & Grading Board',
            'fees' => 'Fees Billing & M-Pesa',
            'graduation' => 'Graduation & Alumni',
            'task-management' => 'Task Management',
            'admissions' => 'Admissions & Intake Registry',
            'reports' => 'Reports & Analytics',
            'service-providers' => 'Service Providers & Procurement',
            'budgeting' => 'Budgeting & Planning',
        ];

        return $map[$key] ?? ucwords(str_replace('-', ' ', $key));
    }
}
