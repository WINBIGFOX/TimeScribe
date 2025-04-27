<?php

declare(strict_types=1);

namespace App\Services;

use App\Settings\GeneralSettings;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use LaravelLang\Locales\Facades\Locales;
use Native\Laravel\Facades\System;

class LocaleService
{
    private readonly GeneralSettings $settings;

    public function __construct()
    {
        $this->settings = app(GeneralSettings::class);
        $this->setupTimezone();
        $this->setupLocale();
    }

    private function setupTimezone(): void
    {
        if (! $this->settings->timezone) {
            $this->settings->timezone = config('app.timezone');
            $systemTimezone = System::timezone();
            if (in_array($systemTimezone, \DateTimeZone::listIdentifiers())) {
                $this->settings->timezone = $systemTimezone;
            }
            $this->settings->save();
        }

        config(['app.timezone' => $this->settings->timezone]);
        date_default_timezone_set($this->settings->timezone);
    }

    private function setupLocale(): void
    {
        $systemLocale = $this->detectSystemLocale();
        $locale = $this->settings->locale ?? $systemLocale;

        $locale = $this->parseLocale($locale);
        $language = $this->getLanguageFromLocale($locale);

        if ($this->settings->locale !== $locale) {
            $this->settings->locale = $locale;
            $this->settings->save();
        }

        if (! Locales::isInstalled($language)) {
            $language = $this->getLanguageFromLocale(config('app.fallback_locale'));
            $this->settings->locale = $this->parseLocale(config('app.fallback_locale'));
            $this->settings->save();
        }

        App::setLocale($language);
        Carbon::setLocale(str_replace('-', '_', $locale));
    }

    private function detectSystemLocale(): string
    {
        if (app()->runningInConsole()) {
            // Für Console Commands, versuche die System-Locale zu ermitteln
            $sysLocale = setlocale(LC_ALL, 0);
            if (preg_match('/^([a-zA-Z]{2}_[A-Z]{2})/', $sysLocale, $matches)) {
                return str_replace('_', '-', $matches[1]);
            }

            return config('app.fallback_locale');
        }

        // Für HTTP Requests
        $locale = request()->server('HTTP_ACCEPT_LANGUAGE', config('app.fallback_locale'));
        if (preg_match('/^([a-zA]{2}[-_][A-Z]{2})/', $locale, $matches)) {
            return $matches[0];
        }

        return config('app.fallback_locale');
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
