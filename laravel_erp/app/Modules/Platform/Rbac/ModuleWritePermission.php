<?php

declare(strict_types=1);

namespace App\Modules\Platform\Rbac;

/**
 * Maps operational module slugs (module_records.module) to catalogue write permissions.
 */
final class ModuleWritePermission
{
    /** @return list<string> */
    public static function knownModules(): array
    {
        return array_keys(self::map());
    }

    public static function forModule(string $module): string
    {
        $permission = self::map()[$module] ?? null;
        if ($permission === null) {
            throw new \InvalidArgumentException("No write permission is defined for module [{$module}].");
        }

        return $permission;
    }

    /** @return array<string, string> */
    private static function map(): array
    {
        return [
            'fees' => 'fees.manage',
            'registration' => 'registration.manage',
            'transfers' => 'transfers.manage',
            'lms' => 'lms.manage',
            'graduation' => 'graduation.manage',
            'imprest' => 'imprest.manage',
            'student-affairs' => 'student_affairs.manage',
            'service-providers' => 'service_providers.manage',
            'smhr' => 'smhr.staff.manage',
            'curriculum' => 'curriculum.manage',
        ];
    }
}
