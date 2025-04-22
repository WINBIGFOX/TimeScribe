<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\TimestampService;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Native\Laravel\Facades\Settings;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    #[\Override]
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    #[\Override]
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'locale' => Settings::get('locale', config('app.fallback_locale')),
            'app_version' => config('nativephp.version'),
            'date' => now()->format('d.m.Y'),
            'recording' => (bool) TimestampService::getCurrentType(),
        ];
    }
}
