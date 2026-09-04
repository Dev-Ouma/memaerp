<?php

declare(strict_types=1);

namespace App\Modules\Platform\Modules;

use App\Models\ModuleState;
use App\Models\User;
use App\Modules\Platform\Audit\AuditRecorder;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

/**
 * Persists module toggles, enforces catalogue dependencies, and reports integrity.
 */
final class ModuleRegistry
{
    public function __construct(private readonly AuditRecorder $audit) {}

    /**
     * Ensure every catalogue key has a module_states row.
     */
    public function syncCatalogue(): void
    {
        foreach (ModuleCatalogue::keys() as $key) {
            ModuleState::query()->firstOrCreate(
                ['module_key' => $key],
                ['is_active' => true],
            );
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function cards(): array
    {
        $this->syncCatalogue();
        $states = ModuleState::allStates();
        $cards = [];

        foreach (ModuleCatalogue::all() as $key => $definition) {
            $submodules = [];
            foreach ($definition['submodules'] as $sub) {
                $route = $sub['route'];
                $submodules[] = [
                    'name' => $sub['name'],
                    'url' => is_string($route) && Route::has($route) ? route($route) : null,
                ];
            }

            $dependencyNames = array_map(
                static fn (string $dep): string => ModuleCatalogue::name($dep),
                $definition['dependencies'],
            );

            $cards[] = [
                'key' => $key,
                'name' => $definition['name'],
                'icon' => $definition['icon'],
                'description' => $definition['description'],
                'dependencies' => $dependencyNames === [] ? 'None' : implode(', ', $dependencyNames),
                'dependency_keys' => $definition['dependencies'],
                'dependents' => ModuleCatalogue::dependents($key),
                'submodules' => $submodules,
                'is_active' => $states[$key] ?? true,
            ];
        }

        return $cards;
    }

    public function toggle(string $key, bool $active, User $actor, bool $force = false): ModuleState
    {
        if (! ModuleCatalogue::exists($key)) {
            throw ValidationException::withMessages([
                'module_key' => 'Unknown module key.',
            ]);
        }

        $this->syncCatalogue();

        if (! $force) {
            if ($active) {
                $inactiveParents = array_values(array_filter(
                    ModuleCatalogue::all()[$key]['dependencies'],
                    static fn (string $dep): bool => ! ModuleState::isActive($dep),
                ));
                if ($inactiveParents !== []) {
                    throw ValidationException::withMessages([
                        'module_key' => 'Enable '.implode(', ', array_map(
                            static fn (string $dep): string => ModuleCatalogue::name($dep),
                            $inactiveParents,
                        )).' before activating this module.',
                    ]);
                }
            } else {
                $activeDependents = array_values(array_filter(
                    ModuleCatalogue::dependents($key),
                    static fn (string $dep): bool => ModuleState::isActive($dep),
                ));
                if ($activeDependents !== []) {
                    throw ValidationException::withMessages([
                        'module_key' => 'Disable '.implode(', ', array_map(
                            static fn (string $dep): string => ModuleCatalogue::name($dep),
                            $activeDependents,
                        )).' before deactivating this module, or retry with force.',
                    ]);
                }
            }
        }

        $before = ModuleState::isActive($key);
        $row = ModuleState::setActive($key, $active, $actor->id);

        $this->audit->record('platform.module_toggled', [
            'actor_user_id' => $actor->id,
            'actor_role' => $actor->activeRole(),
            'subject_type' => ModuleState::class,
            'subject_id' => (string) $row->id,
            'before' => ['module_key' => $key, 'is_active' => $before],
            'after' => ['module_key' => $key, 'is_active' => $row->is_active],
        ]);

        return $row;
    }

    /**
     * @return list<array{key: string, is_active: bool}>
     */
    public function enableAll(User $actor): array
    {
        $this->syncCatalogue();
        $result = [];

        foreach ($this->activationOrder() as $key) {
            $row = ModuleState::setActive($key, true, $actor->id);
            $result[] = ['key' => $key, 'is_active' => $row->is_active];
        }

        $this->audit->record('platform.modules_enabled_all', [
            'actor_user_id' => $actor->id,
            'actor_role' => $actor->activeRole(),
            'subject_type' => ModuleState::class,
            'after' => ['count' => count($result)],
        ]);

        return $result;
    }

    /**
     * @return array{ok: bool, checks: list<array{name: string, ok: bool, detail: string}>}
     */
    public function integrity(): array
    {
        $this->syncCatalogue();
        $checks = [];
        $states = ModuleState::allStates();

        $missing = array_values(array_diff(ModuleCatalogue::keys(), array_keys($states)));
        $checks[] = [
            'name' => 'Catalogue rows',
            'ok' => $missing === [],
            'detail' => $missing === []
                ? count(ModuleCatalogue::keys()).' module keys are persisted.'
                : 'Missing rows: '.implode(', ', $missing),
        ];

        $unknown = array_values(array_diff(array_keys($states), ModuleCatalogue::keys()));
        $checks[] = [
            'name' => 'Unknown keys',
            'ok' => $unknown === [],
            'detail' => $unknown === []
                ? 'No extra module_states keys.'
                : 'Unknown keys: '.implode(', ', $unknown),
        ];

        $brokenRoutes = [];
        foreach (ModuleCatalogue::all() as $definition) {
            foreach ($definition['submodules'] as $sub) {
                $route = $sub['route'] ?? null;
                if (is_string($route) && $route !== '' && ! Route::has($route)) {
                    $brokenRoutes[] = $route;
                }
            }
        }
        $checks[] = [
            'name' => 'Submodule routes',
            'ok' => $brokenRoutes === [],
            'detail' => $brokenRoutes === []
                ? 'Every catalogue submodule points at a registered route.'
                : 'Missing routes: '.implode(', ', $brokenRoutes),
        ];

        $orphanDependents = [];
        foreach (ModuleCatalogue::all() as $key => $definition) {
            if (($states[$key] ?? true) === true) {
                continue;
            }
            foreach (ModuleCatalogue::dependents($key) as $dependent) {
                if (($states[$dependent] ?? true) === true) {
                    $orphanDependents[] = $dependent.' depends on disabled '.$key;
                }
            }
        }
        $checks[] = [
            'name' => 'Dependency graph',
            'ok' => $orphanDependents === [],
            'detail' => $orphanDependents === []
                ? 'No active module depends on a disabled parent.'
                : implode('; ', $orphanDependents),
        ];

        $ok = ! in_array(false, array_column($checks, 'ok'), true);

        return ['ok' => $ok, 'checks' => $checks];
    }

    /**
     * Parents before children so Enable All never trips dependency order.
     *
     * @return list<string>
     */
    private function activationOrder(): array
    {
        $ordered = [];
        $remaining = ModuleCatalogue::keys();

        while ($remaining !== []) {
            $progress = false;
            foreach ($remaining as $index => $key) {
                $parents = ModuleCatalogue::all()[$key]['dependencies'];
                if (array_diff($parents, $ordered) === []) {
                    $ordered[] = $key;
                    unset($remaining[$index]);
                    $progress = true;
                }
            }
            $remaining = array_values($remaining);
            if (! $progress) {
                foreach ($remaining as $key) {
                    $ordered[] = $key;
                }
                break;
            }
        }

        return $ordered;
    }
}
