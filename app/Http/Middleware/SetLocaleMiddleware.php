<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use LaravelLang\Locales\Facades\Locales;
use Native\Laravel\Facades\Settings;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $systemLocale = $request->server('HTTP_ACCEPT_LANGUAGE', config('app.fallback_locale'));
        $locale = Settings::get('locale', $systemLocale);

        $locale = $this->parseLocale($locale);
        $language = $this->getLanguageFromLocale($locale);

        Settings::set('locale', $locale);

        if (! Locales::isInstalled($language)) {
            $language = $this->getLanguageFromLocale(config('app.fallback_locale'));
            Settings::set('locale', $this->parseLocale(config('app.fallback_locale')));
        }

        App::setLocale($language);

        return $next($request);
    }

    private function getLanguageFromLocale(string $region): string
    {
        return substr($region, 0, 2);
    }

    private function parseLocale(string $locale): string
    {
        if (strlen($locale) === 2) {
            $locale = Locales::get($locale, true)->regional;
        }

        return str_replace('_', '-', $locale);
    }
}
