<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Admission\AdminSetupDefinition;
use App\Models\Admission\AdminSetupVersion;
use App\Modules\Admission\Setups\SetupManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class AdminSetupController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeAdmin($request);
        $definitions = AdminSetupDefinition::query()->with(['versions' => fn ($q) => $q->latest('version')])
            ->when($request->q, fn ($q, $value) => $q->where(fn ($s) => $s->where('name', 'ilike', "%{$value}%")->orWhere('setup_key', 'ilike', "%{$value}%")))
            ->when($request->category, fn ($q, $value) => $q->where('category', $value))->orderBy('category')->orderBy('name')->paginate(15)->withQueryString();
        $categories = AdminSetupDefinition::query()->distinct()->orderBy('category')->pluck('category');

        return view('admissions.admin.setups.index', compact('definitions', 'categories'));
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

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()?->isAdmin(), 403);
    }
}
