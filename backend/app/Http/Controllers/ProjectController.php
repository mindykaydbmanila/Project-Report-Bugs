<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::withCount([
            'bugs',
            'bugs as pending_count'   => fn($q) => $q->where('status', 'Pending'),
            'bugs as ongoing_count'   => fn($q) => $q->where('status', 'Ongoing'),
            'bugs as completed_count' => fn($q) => $q->where('status', 'Completed'),
            'bugs as critical_count'  => fn($q) => $q->where('priority', 'Critical'),
        ])->orderBy('created_at', 'asc')->get();

        return response()->json($projects);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'color'       => 'nullable|string|max:20',
        ]);

        if ($request->user()) {
            $validated['owner_id'] = $request->user()->id;
        }

        $project = Project::create($validated);

        return response()->json($project->loadCount('bugs'), 201);
    }

    public function show(Project $project)
    {
        return response()->json($project->loadCount('bugs'));
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name'        => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'color'       => 'nullable|string|max:20',
        ]);

        $project->update($validated);

        return response()->json($project->loadCount('bugs'));
    }

    public function destroy(Project $project)
    {
        $project->delete();
        return response()->json(['message' => 'Project deleted']);
    }
}
