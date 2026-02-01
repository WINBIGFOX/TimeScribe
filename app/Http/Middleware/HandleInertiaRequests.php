<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\TimestampService;
use App\Settings\GeneralSettings;
use Illuminate\Http\Request;
use Inertia\Middleware;

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
        $settings = resolve(GeneralSettings::class);

        return [
            ...parent::share($request),
            'js_locale' => str_replace('_', '-', $settings->locale ?? config('app.fallback_locale')),
            'locale' => $settings->locale ?? config('app.fallback_locale'),
            'timezone' => $settings->timezone ?? config('app.timezone'),
            'app_version' => config('nativephp.version'),
            'date' => now()->format('Y-m-d'),
            'recording' => (bool) TimestampService::getCurrentType(),
            'environment' => PHP_OS_FAMILY,
        ];
    }
}
