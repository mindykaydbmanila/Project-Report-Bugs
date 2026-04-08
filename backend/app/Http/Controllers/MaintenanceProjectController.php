<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceProject;
use Illuminate\Http\Request;

class MaintenanceProjectController extends Controller
{
    public function index()
    {
        $projects = MaintenanceProject::withCount([
            'tickets',
            'tickets as pending_count'     => fn ($q) => $q->where('status', 'Pending'),
            'tickets as in_progress_count' => fn ($q) => $q->where('status', 'In Progress'),
            'tickets as on_hold_count'     => fn ($q) => $q->where('status', 'On Hold'),
            'tickets as completed_count'   => fn ($q) => $q->where('status', 'Completed'),
            'tickets as cancelled_count'   => fn ($q) => $q->where('status', 'Cancelled'),
        ])->orderBy('name')->get();

        return response()->json($projects);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'description'    => 'nullable|string',
            'color'          => 'nullable|string|max:20',
            'is_active'      => 'nullable|boolean',
            'contract_start' => 'nullable|date',
            'contract_end'   => 'nullable|date',
        ]);

        $data['color']     = $data['color'] ?? '#10b981';
        $data['is_active'] = $data['is_active'] ?? true;
        $data['contract_start'] = $data['contract_start'] ?: null;
        $data['contract_end']   = $data['contract_end'] ?: null;
        $project = MaintenanceProject::create($data);

        return response()->json(
            MaintenanceProject::withCount([
                'tickets',
                'tickets as pending_count'     => fn ($q) => $q->where('status', 'Pending'),
                'tickets as in_progress_count' => fn ($q) => $q->where('status', 'In Progress'),
                'tickets as on_hold_count'     => fn ($q) => $q->where('status', 'On Hold'),
                'tickets as completed_count'   => fn ($q) => $q->where('status', 'Completed'),
                'tickets as cancelled_count'   => fn ($q) => $q->where('status', 'Cancelled'),
            ])->find($project->id),
            201
        );
    }

    public function show(MaintenanceProject $maintenanceProject)
    {
        return response()->json(
            MaintenanceProject::withCount([
                'tickets',
                'tickets as pending_count'     => fn ($q) => $q->where('status', 'Pending'),
                'tickets as in_progress_count' => fn ($q) => $q->where('status', 'In Progress'),
                'tickets as on_hold_count'     => fn ($q) => $q->where('status', 'On Hold'),
                'tickets as completed_count'   => fn ($q) => $q->where('status', 'Completed'),
                'tickets as cancelled_count'   => fn ($q) => $q->where('status', 'Cancelled'),
            ])->find($maintenanceProject->id)
        );
    }

    public function update(Request $request, MaintenanceProject $maintenanceProject)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'description'    => 'nullable|string',
            'color'          => 'nullable|string|max:20',
            'is_active'      => 'nullable|boolean',
            'contract_start' => 'nullable|date',
            'contract_end'   => 'nullable|date',
        ]);

        $data['contract_start'] = $data['contract_start'] ?: null;
        $data['contract_end']   = $data['contract_end'] ?: null;

        $maintenanceProject->update($data);

        return response()->json(
            MaintenanceProject::withCount([
                'tickets',
                'tickets as pending_count'     => fn ($q) => $q->where('status', 'Pending'),
                'tickets as in_progress_count' => fn ($q) => $q->where('status', 'In Progress'),
                'tickets as on_hold_count'     => fn ($q) => $q->where('status', 'On Hold'),
                'tickets as completed_count'   => fn ($q) => $q->where('status', 'Completed'),
                'tickets as cancelled_count'   => fn ($q) => $q->where('status', 'Cancelled'),
            ])->find($maintenanceProject->id)
        );
    }

    public function destroy(MaintenanceProject $maintenanceProject)
    {
        $maintenanceProject->delete();
        return response()->json(null, 204);
    }
}
