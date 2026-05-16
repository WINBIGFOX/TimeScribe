<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Settings\GeneralSettings;
use Closure;
use Illuminate\Auth\GenericUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Laravel\Nightwatch\Facades\Nightwatch;

class SetContextMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $settings = resolve(GeneralSettings::class);
        $locale = Date::getLocale();
        $version = config('nativephp.version');
        $username = $locale.'_'.$version.'_'.substr($settings->id, -7, 7);

        Auth::guard('web')->setUser(new GenericUser([
            'id' => $settings->id,
            'name' => $username,
        ]));

        Nightwatch::user(fn () => [
            'username' => $username,
            'version' => $version,
        ]);

        return $next($request);
    }
}
