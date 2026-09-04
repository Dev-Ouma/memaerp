<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ModuleRecord;
use App\Services\OperationalRecordService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class OperationalRecordController extends Controller
{
    public function __construct(private readonly OperationalRecordService $records) {}

    public function store(Request $request, string $module, string $kind): RedirectResponse
    {
        $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:80'],
        ]);
        $this->records->store($request, $module, $kind);

        return back()->with('success', 'Record saved to the database.');
    }

    public function updateStatus(Request $request, ModuleRecord $record): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'string', 'max:80'],
            'status_type' => ['nullable', 'string', 'max:40'],
        ]);
        $this->records->updateStatus($request, $record, $data['status']);

        return back()->with('success', 'Record status updated.');
    }
}
