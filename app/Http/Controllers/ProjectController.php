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
        $projects = Project::with(['children'])->get();

        return Inertia::render('Project/Index', [
            'projects' => ProjectResource::collection($projects),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $projects = Project::whereNull('parent_id')->get();
        $parentId = $request->input('parent_id');

        return Inertia::modal('Project/Create', [
            'submit_route' => route('project.store'),
            'parent_id' => $parentId,
            'projects' => ProjectResource::collection($projects),
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
        $projects = Project::whereNull('parent_id')->whereNot('id', $project->id)->get();

        return Inertia::modal('Project/Edit', [
            'submit_route' => route('project.update', $project),
            'project' => ProjectResource::make($project),
            'projects' => ProjectResource::collection($projects),
        ])->baseRoute('project.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreProjectRequest $request, Project $project)
    {
        $data = $request->validated();

        // Prevent circular references
        if (array_key_exists('parent_id', $data) && $data['parent_id'] === $project->id) {
            $data['parent_id'] = null;
        }

        $project->update($data);

        return redirect()->route('project.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DestroyProjectRequest $request, Project $project)
    {
        $request->validated();
        Project::where('parent_id', $project->id)->update(['parent_id' => null]);

        $project->delete();

        return redirect()->route('project.index');
    }
}
