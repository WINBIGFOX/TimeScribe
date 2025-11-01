<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\AppActivityResource;
use App\Models\ActivityHistory;
use App\Settings\GeneralSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Inertia\Inertia;

class AppActivityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, GeneralSettings $settings)
    {
        $firstActivity = ActivityHistory::oldest()->first();
        $lastActivity = ActivityHistory::latest()->first();

        $startDate = $lastActivity?->started_at->startOfWeek() ?? now()->startOfWeek();
        $endDate = $lastActivity?->ended_at->endOfWeek() ?? now()->endOfWeek();
        if ($request->has('startDate')) {
            $startDate = Date::parse($request->query('startDate'))->startOfDay();
        }
        if ($request->has('endDate')) {
            $endDate = Date::parse($request->query('endDate'))->endOfDay();
        }

        $appActivity = ActivityHistory::where('started_at', '>=', $startDate)
            ->where('ended_at', '<=', $endDate)
            ->orderBy('started_at', 'desc')
            ->get();

        $historyApp = $appActivity->mapToGroups(fn ($item): array => [
            $item->app_identifier => $item,
        ])->map(function ($item, $key): array {
            $first = $item->first();

            return [
                'name' => $first->app_name,
                'icon' => route('app-icon.show', ['appIconName' => $first->app_icon]),
                'identifier' => $key,
                'category' => $first->app_category?->label(),
                'color' => $first->color,
                'sum' => $item->sum('duration'),
                'count' => $item->count(),
                'items' => AppActivityResource::collection($item),
            ];
        })->sortByDesc('sum')->values();

        $historyCategory = $appActivity->mapToGroups(fn ($item): array => [
            ($item->app_category?->label() ?? __('app.unknown')) => $item,
        ])->map(fn ($item, $key): array => [
            'name' => $key,
            'color' => $item->first()->categoryColor,
            'identifier' => $item->first()->app_category?->value,
            'sum' => $item->sum('duration'),
            'count' => $item->count(),
            'items' => AppActivityResource::collection($item),
        ])->sortByDesc('sum')->values();

        return Inertia::render('AppActivity/Index', [
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
            'maxDate' => now()->endOfWeek()->format('Y-m-d'),
            'minDate' => $firstActivity?->created_at->format('Y-m-d') ?? $startDate->format('Y-m-d'),
            'historyApp' => $historyApp,
            'historyCategory' => $historyCategory,
            'active' => $settings->appActivityTracking ?? false,
        ]);
    }
}
