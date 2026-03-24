<?php

namespace App\Http\Controllers;

use App\Models\Bug;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BugController extends Controller
{
    public function index(Request $request)
    {
        $query = Bug::query();

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('scenario_type')) {
            $query->where('scenario_type', $request->scenario_type);
        }
        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(fn($sub) => $sub->where('title', 'like', "%$q%")
                                         ->orWhere('description', 'like', "%$q%"));
        }

        $bugs = $query->orderBy('sequence')->get();

        $summaryQuery = Bug::query();
        if ($request->filled('project_id')) {
            $summaryQuery->where('project_id', $request->project_id);
        }

        $summary = [
            'total'           => (clone $summaryQuery)->count(),
            'by_priority'     => (clone $summaryQuery)->selectRaw('priority, count(*) as count')->groupBy('priority')->pluck('count', 'priority'),
            'by_scenario_type'=> (clone $summaryQuery)->selectRaw('scenario_type, count(*) as count')->groupBy('scenario_type')->pluck('count', 'scenario_type'),
            'by_status'       => (clone $summaryQuery)->selectRaw('status, count(*) as count')->groupBy('status')->pluck('count', 'status'),
        ];

        return response()->json(['bugs' => $bugs, 'summary' => $summary]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id'       => 'required|exists:projects,id',
            'title'            => 'required|string|max:255',
            'subtitles'         => 'nullable|array',
            'subtitles.*.text'  => 'nullable|string|max:500',
            'subtitles.*.link'  => 'nullable|string|max:2048',
            'description'      => 'nullable|string',
            'priority'         => 'required|in:Critical,High,Medium,Low',
            'scenario_type'    => 'required|in:UI,Functionality,UI & Functionality',
            'status'              => 'required|in:Pending,Out of Scope,Ongoing,Completed',
            'developer_comment'   => 'nullable|string',
            'images.*'            => 'nullable|image|max:5120',
            'frontend_images.*'=> 'nullable|image|max:5120',
            'cms_images.*'     => 'nullable|image|max:5120',
        ]);

        $lastBug = Bug::where('project_id', $validated['project_id'])->orderBy('sequence', 'desc')->first();
        $validated['sequence'] = $lastBug ? $lastBug->sequence + 1 : 1;

        $validated['subtitles'] = array_values(array_filter($request->input('subtitles', []), fn($s) => !empty(trim($s['text'] ?? '')) || !empty(trim($s['link'] ?? ''))));

        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePaths[] = Storage::url($image->store('bug-images', 'public'));
            }
        }
        $validated['images'] = $imagePaths;

        $frontendPaths = [];
        if ($request->hasFile('frontend_images')) {
            foreach ($request->file('frontend_images') as $image) {
                $frontendPaths[] = Storage::url($image->store('bug-images', 'public'));
            }
        }
        $validated['frontend_images'] = $frontendPaths;

        $cmsPaths = [];
        if ($request->hasFile('cms_images')) {
            foreach ($request->file('cms_images') as $image) {
                $cmsPaths[] = Storage::url($image->store('bug-images', 'public'));
            }
        }
        $validated['cms_images'] = $cmsPaths;

        $bug = Bug::create($validated);
        return response()->json($bug, 201);
    }

    public function show(Bug $bug)
    {
        return response()->json($bug);
    }

    public function update(Request $request, Bug $bug)
    {
        $validated = $request->validate([
            'title'                    => 'sometimes|required|string|max:255',
            'subtitles'                => 'nullable|array',
            'subtitles.*.text'         => 'nullable|string|max:500',
            'subtitles.*.link'         => 'nullable|string|max:2048',
            'description'              => 'nullable|string',
            'priority'                 => 'sometimes|required|in:Critical,High,Medium,Low',
            'scenario_type'            => 'sometimes|required|in:UI,Functionality,UI & Functionality',
            'status'                   => 'sometimes|required|in:Pending,Out of Scope,Ongoing,Completed',
            'developer_comment'        => 'nullable|string',
            'dev_comments_json'        => 'nullable|string',
            'images.*'                 => 'nullable|image|max:5120',
            'existing_images'          => 'nullable|array',
            'frontend_images.*'        => 'nullable|image|max:5120',
            'existing_frontend_images' => 'nullable|array',
            'cms_images.*'             => 'nullable|image|max:5120',
            'existing_cms_images'      => 'nullable|array',
        ]);

        if ($request->has('subtitles')) {
            $validated['subtitles'] = array_values(array_filter($request->input('subtitles', []), fn($s) => !empty(trim($s['text'] ?? '')) || !empty(trim($s['link'] ?? ''))));
        }

        if ($request->has('dev_comments_json')) {
            $decoded = json_decode($request->input('dev_comments_json'), true);
            $validated['dev_comments'] = is_array($decoded) ? $decoded : [];
            unset($validated['dev_comments_json']);
        }

        $imagePaths = $request->input('existing_images', $bug->images ?? []);
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePaths[] = Storage::url($image->store('bug-images', 'public'));
            }
        }
        $validated['images'] = $imagePaths;
        unset($validated['existing_images']);

        $frontendPaths = $request->input('existing_frontend_images', $bug->frontend_images ?? []);
        if ($request->hasFile('frontend_images')) {
            foreach ($request->file('frontend_images') as $image) {
                $frontendPaths[] = Storage::url($image->store('bug-images', 'public'));
            }
        }
        $validated['frontend_images'] = $frontendPaths;
        unset($validated['existing_frontend_images']);

        $cmsPaths = $request->input('existing_cms_images', $bug->cms_images ?? []);
        if ($request->hasFile('cms_images')) {
            foreach ($request->file('cms_images') as $image) {
                $cmsPaths[] = Storage::url($image->store('bug-images', 'public'));
            }
        }
        $validated['cms_images'] = $cmsPaths;
        unset($validated['existing_cms_images']);

        $bug->update($validated);
        return response()->json($bug);
    }

    public function destroy(Bug $bug)
    {
        $bug->delete();
        return response()->json(['message' => 'Bug deleted successfully']);
    }

    public function summary(Request $request)
    {
        $query = Bug::query();
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        return response()->json([
            'total'           => (clone $query)->count(),
            'by_priority'     => (clone $query)->selectRaw('priority, count(*) as count')->groupBy('priority')->pluck('count', 'priority'),
            'by_scenario_type'=> (clone $query)->selectRaw('scenario_type, count(*) as count')->groupBy('scenario_type')->pluck('count', 'scenario_type'),
            'by_status'       => (clone $query)->selectRaw('status, count(*) as count')->groupBy('status')->pluck('count', 'status'),
        ]);
    }
}
