<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\DestroyProjectRequest;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $projects = Project::with('timestamps')->get()->append('work_time');

        return Inertia::render('Project/Index', [
            'projects' => ProjectResource::collection($projects),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        return Inertia::modal('Project/Create', [
            'submit_route' => route('project.store'),
        ])->baseRoute('project.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProjectRequest $request)
    {
        $data = $request->validated();
        Project::create($data);

        return redirect()->route('project.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project)
    {
        return Inertia::modal('Project/Edit', [
            'submit_route' => route('project.update', $project),
            'project' => ProjectResource::make($project),
        ])->baseRoute('project.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreProjectRequest $request, Project $project)
    {
        $data = $request->validated();
        $project->update([
            'name' => $data['name'],
            'description' => $data['description'],
            'color' => $data['color'],
            'icon' => $data['icon'] ?? null,
            'hourly_rate' => $data['hourly_rate'] ?? null,
        ]);

        return redirect()->route('project.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DestroyProjectRequest $request, Project $project)
    {
        $request->validated();
        $project->delete();

        return redirect()->route('project.index');
    }
}
