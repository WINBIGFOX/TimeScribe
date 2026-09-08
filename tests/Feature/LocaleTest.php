<?php

declare(strict_types=1);

use App\Events\LocaleChanged;
use App\Services\LocaleService;
use App\Settings\GeneralSettings;
use App\Settings\ProjectSettings;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Event;
use Inertia\Testing\AssertableInertia as Assert;
use Native\Desktop\Facades\App as NativeApp;

beforeEach(function (): void {
    $this->withoutVite();
    NativeApp::shouldReceive('openAtLogin')->andReturn(false);
    $settings = resolve(GeneralSettings::class);
    $settings->timezone = 'UTC';
    $settings->save();
});

afterEach(function (): void {
    Date::setLocale('en');
    date_default_timezone_set('UTC');
});

it('uses the same effective locale for the document and application', function (string $input, string $regional, string $language, string $direction): void {
    $settings = resolve(GeneralSettings::class);
    $settings->locale = $input;
    $settings->save();

    $this->get(route('welcome.index'))
        ->assertSuccessful()
        ->assertSee('dir="'.$direction.'"', false)
        ->assertSee('lang="'.str_replace('_', '-', $language).'"', false)
        ->assertInertia(fn (Assert $page): Assert => $page
            ->where('locale', $regional)
            ->where('js_locale', str_replace('_', '-', $regional))
            ->where('language', $language)
            ->where('direction', $direction)
        );

    expect(app()->getLocale())->toBe($language)
        ->and(Date::getLocale())->toBe($regional)
        ->and($settings->refresh()->locale)->toBe($regional);
})->with([
    ['he_IL', 'he_IL', 'he', 'rtl'],
    ['he-IL', 'he_IL', 'he', 'rtl'],
    ['he', 'he_IL', 'he', 'rtl'],
    ['ar_SA', 'ar_SA', 'ar', 'rtl'],
    ['ar-EG', 'ar_EG', 'ar', 'rtl'],
    ['ar', 'ar_AE', 'ar', 'rtl'],
    ['en_US', 'en_US', 'en', 'ltr'],
    ['pt_BR', 'pt_BR', 'pt_BR', 'ltr'],
    ['zh_CN', 'zh_CN', 'zh_CN', 'ltr'],
    ['es_ES', 'en_US', 'en', 'ltr'],
]);

it('uses the configured fallback consistently', function (): void {
    config(['app.fallback_locale' => 'he_IL']);
    $settings = resolve(GeneralSettings::class);
    $settings->locale = 'es_ES';
    $settings->save();

    new LocaleService;

    expect(app()->getLocale())->toBe('he')
        ->and(Date::getLocale())->toBe('he_IL')
        ->and($settings->refresh()->locale)->toBe('he_IL');
});

it('persists a language change before broadcasting and returns to the wizard', function (string $locale): void {
    $dispatched = false;
    Event::listen(LocaleChanged::class, function () use ($locale, &$dispatched): void {
        expect(resolve(GeneralSettings::class)->refresh()->locale)->toBe($locale);
        $dispatched = true;
    });

    $this->from(route('welcome.index'))
        ->patch(route('settings.general.updateLocale'), ['locale' => $locale])
        ->assertRedirect(route('welcome.index'));

    expect(resolve(GeneralSettings::class)->refresh()->locale)->toBe($locale)
        ->and(resolve(ProjectSettings::class)->refresh()->defaultCurrency)->toBeNull();
    expect($dispatched)->toBeTrue();

    $this->get(route('welcome.index'))->assertInertia(fn (Assert $page): Assert => $page
        ->where('direction', 'rtl')
        ->where('locale', $locale)
    );
})->with(['he_IL', 'ar_SA']);

it('switches back to left to right and preserves a configured project currency', function (): void {
    Event::fake([LocaleChanged::class]);
    $settings = resolve(GeneralSettings::class);
    $settings->locale = 'he_IL';
    $settings->save();
    $projectSettings = resolve(ProjectSettings::class);
    $projectSettings->defaultCurrency = 'ILS';
    $projectSettings->save();

    $this->patch(route('settings.general.update'), [
        'openAtLogin' => false,
        'theme' => $settings->theme,
        'showTimerOnUnlock' => $settings->showTimerOnUnlock,
        'holidayRegion' => $settings->holidayRegion,
        'locale' => 'en_US',
        'appActivityTracking' => $settings->appActivityTracking,
        'timezone' => 'UTC',
        'default_overview' => 'week',
    ])->assertRedirect(route('settings.general.edit'));

    expect($projectSettings->refresh()->defaultCurrency)->toBe('ILS');
    $this->get(route('welcome.index'))->assertInertia(fn (Assert $page): Assert => $page
        ->where('language', 'en')
        ->where('direction', 'ltr')
    );
});
