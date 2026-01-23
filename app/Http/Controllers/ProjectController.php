<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\TimestampTypeEnum;
use App\Http\Requests\DestroyProjectRequest;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Services\TimestampService;
use App\Settings\ProjectSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Inertia\Inertia;
use PrinsFrank\Standards\Currency\CurrencyAlpha3;
use PrinsFrank\Standards\Currency\CurrencySymbol;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(ProjectSettings $projectSettings)
    {
        $projects = Project::with('timestampItems')->withTrashed()->scopes('sortedByLatestTimestamp')->get()->append(['work_time', 'billable_amount']);

        return Inertia::render('Project/Index', [
            'projects' => fn () => ProjectResource::collection($projects),
            'current_project_id' => fn (): ?int => $projectSettings->currentProject,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request, ProjectSettings $projectSettings)
    {
        return Inertia::modal('Project/Create', [
            'submit_route' => route('project.store'),
            'currencies' => collect(CurrencyAlpha3::cases())->mapWithKeys(fn (CurrencyAlpha3 $currency): array => [$currency->value => $currency->value.($currency->getSymbol() instanceof CurrencySymbol ? ' ('.$currency->getSymbol()->value.')' : '')])->sortKeys(),
            'default_currencies' => $projectSettings->defaultCurrency,
        ])->baseRoute('project.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProjectRequest $request, ProjectSettings $projectSettings): Redirector|RedirectResponse
    {
        $data = $request->validated();
        Project::create($data);

        if ($request->has('currency')) {
            $projectSettings->defaultCurrency = $data['currency'];
            $projectSettings->save();
        }

        return to_route('project.index');
    }

    public function show(Project $project)
    {
        $project->load('timestampItems.project')->append(['billable_amount']);

        return Inertia::modal('Project/Show', [
            'project' => ProjectResource::make($project),
            'currencies' => collect(CurrencyAlpha3::cases())->mapWithKeys(fn (CurrencyAlpha3 $currency): array => [$currency->value => $currency->value.($currency->getSymbol() instanceof CurrencySymbol ? ' ('.$currency->getSymbol()->value.')' : '')])->sortKeys(),
        ])->baseRoute('project.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project)
    {
        return Inertia::modal('Project/Edit', [
            'submit_route' => route('project.update', $project),
            'project' => ProjectResource::make($project),
            'currencies' => collect(CurrencyAlpha3::cases())->mapWithKeys(fn (CurrencyAlpha3 $currency): array => [$currency->value => $currency->value.($currency->getSymbol() instanceof CurrencySymbol ? ' ('.$currency->getSymbol()->value.')' : '')])->sortKeys(),
        ])->baseRoute('project.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreProjectRequest $request, Project $project, ProjectSettings $projectSettings): Redirector|RedirectResponse
    {
        $data = $request->validated();
        $project->update([
            'name' => $data['name'],
            'description' => $data['description'],
            'color' => $data['color'],
            'icon' => $data['icon'] ?? null,
            'hourly_rate' => $data['hourly_rate'] ?? null,
            'currency' => $data['currency'] ?? null,
        ]);

        if ($request->has('currency')) {
            $projectSettings->defaultCurrency = $data['currency'];
            $projectSettings->save();
        }

        return to_route('project.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DestroyProjectRequest $request, Project $project, ProjectSettings $projectSettings): Redirector|RedirectResponse
    {
        $request->validated();

        if ($projectSettings->currentProject === $project->id) {
            $projectSettings->currentProject = null;
            $projectSettings->save();
            if (TimestampService::getCurrentType() === TimestampTypeEnum::WORK) {
                TimestampService::stop();
            }
        }

        $project->delete();

        return to_route('project.index');
    }

    public function restore(Project $project): Redirector|RedirectResponse
    {
        $project->restore();

        return to_route('project.index');
    }
}
