<?php

declare(strict_types=1);

namespace App\Services;

use App\Settings\GeneralSettings;
use App\Settings\ProjectSettings;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Date;
use LaravelLang\Locales\Facades\Locales;
use Native\Desktop\Facades\System;
use PrinsFrank\Standards\Country\CountryAlpha2;

class LocaleService
{
    const array LOCALE_MAPPING = [
        'da_DK' => 'da',
        'de_DE' => 'de',
        'en_US' => 'en',
        'en_GB' => 'en',
        'fr_CA' => 'fr',
        'fr_FR' => 'fr',
        'it_IT' => 'it',
    ];

    private readonly GeneralSettings $settings;

    private readonly ProjectSettings $projectSettings;

    public function __construct()
    {
        $this->settings = resolve(GeneralSettings::class);
        $this->projectSettings = resolve(ProjectSettings::class);
        $this->setupTimezone();
        $this->setupLocale();
        $this->setupCurrency();
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

        if ($this->settings->locale !== $locale) {
            $this->settings->locale = $locale;
            $this->settings->save();
        }

        $language = $this->getLanguageLocale($locale);
        if (! Locales::isInstalled($language)) {
            $this->settings->locale = $this->parseLocale(config('app.fallback_locale'));
            $this->settings->save();
        }

        App::setLocale($language);
        Date::setLocale($locale);
    }

    private function setupCurrency(): void
    {
        try {
            if ($this->projectSettings->defaultCurrency === null) {
                $country = explode('_', $this->settings->locale)[1];
                $countryInfo = CountryAlpha2::from($country);
                $this->projectSettings->defaultCurrency = $countryInfo->getCurrenciesAlpha3()[0]->value;
                $this->projectSettings->save();
            }
        } catch (\Throwable) {
            //
        }
    }

    private function detectSystemLocale(): string
    {
        if (app()->runningInConsole()) {
            // Für Console Commands, versuche die System-Locale zu ermitteln
            $sysLocale = setlocale(LC_ALL, 0);
            if (preg_match('/^([a-zA-Z]{2}_[A-Z]{2})/', $sysLocale, $matches)) {
                return $matches[1];
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

    private function getLanguageLocale(string $locale): string
    {
        if (array_key_exists($locale, self::LOCALE_MAPPING)) {
            return self::LOCALE_MAPPING[$locale];
        }

        return $locale;
    }

    private function parseLocale(string $locale): string
    {
        if (strlen($locale) === 2) {
            return Locales::get($locale, true)->regional;
        }

        return str_replace('-', '_', $locale);
    }
}
