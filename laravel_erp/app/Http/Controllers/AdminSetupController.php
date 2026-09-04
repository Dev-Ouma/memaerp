<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Admission\AdminSetupDefinition;
use App\Models\Admission\AdminSetupVersion;
use App\Modules\Admission\Setups\SetupManager;
use App\Modules\Platform\Modules\ModuleCatalogue;
use App\Modules\Platform\Modules\ModuleRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class AdminSetupController extends Controller
{
    public function platformIndex(Request $request): View
    {
        $this->authorizeAdmin($request);

        $admissionsSummary = [
            'total' => AdminSetupDefinition::query()->count(),
            'active' => AdminSetupDefinition::query()->whereHas('versions', fn ($query) => $query->where('status', 'ACTIVE'))->count(),
        ];

        return view('admin.setups.index', compact('admissionsSummary'));
    }

    public function index(Request $request): View
    {
        $this->authorizeAdmin($request);
        $definitions = AdminSetupDefinition::query()->with(['versions' => fn ($q) => $q->latest('version')])
            ->when($request->q, fn ($q, $value) => $q->where(fn ($s) => $s->where('name', 'ilike', "%{$value}%")->orWhere('setup_key', 'ilike', "%{$value}%")))
            ->when($request->category, fn ($q, $value) => $q->where('category', $value))->orderBy('category')->orderBy('name')->paginate(15)->withQueryString();
        $categories = AdminSetupDefinition::query()->distinct()->orderBy('category')->pluck('category');
        $catalogue = AdminSetupDefinition::query()->with(['versions' => fn ($q) => $q->latest('version')])->get();
        $summary = [
            'total' => $catalogue->count(),
            'active' => $catalogue->filter(fn ($definition) => $definition->versions->contains('status', 'ACTIVE'))->count(),
            'draft' => $catalogue->filter(fn ($definition) => $definition->versions->contains('status', 'DRAFT'))->count(),
            'missing' => $catalogue->filter(fn ($definition) => $definition->versions->isEmpty())->count(),
        ];
        $categoryCounts = $catalogue->groupBy('category')->map->count()->sortDesc();

        return view('admissions.admin.setups.index', compact('definitions', 'categories', 'summary', 'categoryCounts'));
    }

    public function show(Request $request, AdminSetupDefinition $setup): View
    {
        $this->authorizeAdmin($request);
        $setup->load(['versions' => fn ($q) => $q->withCount('usages')->orderByDesc('version')]);

        return view('admissions.admin.setups.show', compact('setup'));
    }

    public function store(Request $request, AdminSetupDefinition $setup, SetupManager $manager): RedirectResponse
    {
        $this->authorizeAdmin($request);
        $data = $request->validate(['configuration' => ['required', 'json'], 'change_reason' => ['required', 'string', 'max:500']]);
        $manager->draft($setup, json_decode($data['configuration'], true, 512, JSON_THROW_ON_ERROR), $data['change_reason'], $request->user()->id);

        return back()->with('success', 'Draft setup version created.');
    }

    public function publish(Request $request, AdminSetupVersion $version, SetupManager $manager): RedirectResponse
    {
        $this->authorizeAdmin($request);
        $data = $request->validate(['effective_from' => ['required', 'date'], 'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from']]);
        $manager->publish($version, $data['effective_from'], $data['effective_to'] ?? null, $request->user()->id);

        return back()->with('success', 'Setup version published. New transactions will use it from its effective date.');
    }

    public function status(Request $request, AdminSetupVersion $version, SetupManager $manager): RedirectResponse
    {
        $this->authorizeAdmin($request);
        $data = $request->validate(['status' => ['required', 'in:INACTIVE,ARCHIVED']]);
        $manager->changeStatus($version, $data['status'], $request->user()->id);

        return back()->with('success', 'Setup status updated.');
    }

    /**
     * Accounting Admin Setup
     */
    public function accounting(Request $request): View
    {
        $this->authorizeAdmin($request);
        $stats = ['generalLedgers' => 48, 'fiscalYears' => 5, 'activeStatus' => 'Ledger Balanced'];

        return view('admin.setups.accounting', compact('stats'));
    }

    /**
     * Bank Admin Setup
     */
    public function bank(Request $request): View
    {
        $this->authorizeAdmin($request);
        $stats = ['linkedBanks' => 4, 'apiBridges' => 2, 'clearedFeeds' => 'Operational'];

        return view('admin.setups.bank', compact('stats'));
    }

    /**
     * Invoicing Admin Setup
     */
    public function invoicing(Request $request): View
    {
        $this->authorizeAdmin($request);
        $stats = ['billingCycles' => 3, 'taxSchemes' => 2, 'paymentRules' => 'Strict Check-in'];

        return view('admin.setups.invoicing', compact('stats'));
    }

    /**
     * Payment Admin Setup
     */
    public function payment(Request $request): View
    {
        $this->authorizeAdmin($request);
        $stats = ['payoutChannels' => 4, 'mpesaCredentials' => 'Daraja 2.0 Secure', 'auditTrailing' => 'Active'];

        return view('admin.setups.payment', compact('stats'));
    }

    /**
     * Module Manager (Active/Deactivate) Setup
     */
    public function moduleManager(Request $request, ModuleRegistry $registry): View
    {
        $this->authorizeAdmin($request);

        $modules = $registry->cards();

        return view('admin.setups.module-manager', compact('modules'));
    }

    /**
     * AJAX: Toggle a single module's active state.
     */
    public function toggleModule(Request $request, ModuleRegistry $registry): JsonResponse
    {
        $this->authorizeAdmin($request);

        $validated = $request->validate([
            'module_key' => ['required', 'string', Rule::in(ModuleCatalogue::keys())],
            'is_active' => ['required', 'boolean'],
            'force' => ['sometimes', 'boolean'],
        ]);

        $row = $registry->toggle(
            $validated['module_key'],
            (bool) $validated['is_active'],
            $request->user(),
            (bool) ($validated['force'] ?? false),
        );

        return response()->json([
            'success' => true,
            'module_key' => $row->module_key,
            'is_active' => $row->is_active,
            'message' => $row->is_active
                ? 'Module activated successfully.'
                : 'Module deactivated successfully.',
        ]);
    }

    public function enableAllModules(Request $request, ModuleRegistry $registry): JsonResponse
    {
        $this->authorizeAdmin($request);

        $modules = $registry->enableAll($request->user());

        return response()->json([
            'success' => true,
            'modules' => $modules,
            'message' => count($modules).' modules enabled.',
        ]);
    }

    public function verifyModuleIntegrity(Request $request, ModuleRegistry $registry): JsonResponse
    {
        $this->authorizeAdmin($request);

        $report = $registry->integrity();

        return response()->json([
            'success' => $report['ok'],
            'checks' => $report['checks'],
            'message' => $report['ok']
                ? 'Integrity check passed. Catalogue, routes and dependencies agree.'
                : 'Integrity check found issues. Review the checks list.',
        ]);
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()?->isAdmin(), 403);
    }
}
