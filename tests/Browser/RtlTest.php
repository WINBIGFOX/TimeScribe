<?php

declare(strict_types=1);

use App\Events\LocaleChanged;
use App\Models\Project;
use App\Settings\GeneralSettings;
use Illuminate\Support\Facades\Event;
use Native\Desktop\Facades\App as NativeApp;

beforeEach(function (): void {
    NativeApp::shouldReceive('openAtLogin')->andReturn(false);
    Event::fake([LocaleChanged::class]);
    $settings = resolve(GeneralSettings::class);
    $settings->timezone = 'UTC';
    $settings->locale = 'en_US';
    $settings->save();
});

it('switches languages without duplicating the app or leaving stale select labels', function (string $locale, string $label, string $title, string $system): void {
    $page = visit('/settings/general/edit')->resize(1280, 800);
    $page->assertSee('General Settings')
        ->click('[data-slot="select-trigger"]:has-text("English (US)")')
        ->click('[role="option"]:has-text("'.$label.'")')
        ->assertSee($title)
        ->assertScript('document.documentElement.dir', 'rtl')
        ->assertScript('document.documentElement.lang', explode('_', $locale)[0])
        ->assertAttribute('[data-slot="sidebar"]', 'data-side', 'right')
        ->assertScript('document.querySelectorAll("[data-slot=page-header]").length', 1)
        ->assertScript('document.querySelectorAll("[data-slot=select-value]")[2].textContent.trim()', $system)
        ->assertNoJavaScriptErrors();

    $english = $locale === 'he_IL' ? 'אנגלית (ארצות הברית)' : 'الإنجليزية (الولايات المتحدة)';
    $page->click('[data-slot="select-trigger"]:has-text("'.$label.'")')
        ->click('[role="option"]:has-text("'.$english.'")')
        ->assertSee('General Settings')
        ->assertScript('document.documentElement.dir', 'ltr')
        ->assertAttribute('[data-slot="sidebar"]', 'data-side', 'left')
        ->assertScript('document.querySelectorAll("[data-slot=select-value]")[2].textContent.trim()', 'System')
        ->assertNoJavaScriptErrors();
})->with([
    ['he_IL', 'עברית', 'הגדרות כלליות', 'מערכת'],
    ['ar_SA', 'العربية', 'الإعدادات العامة', 'النظام'],
]);

it('keeps dates in navigation machine readable in Arabic', function (): void {
    $settings = resolve(GeneralSettings::class);
    $settings->locale = 'ar_EG';
    $settings->save();

    visit('/settings/general/edit')->assertSee('العربية');

    visit('/overview/week/2026-09-08')
        ->resize(1280, 800)
        ->assertSee('نظرة عامة أسبوعية')
        ->assertScript('Array.from(document.querySelectorAll("a[href]")).every(a => !/[٠-٩۰-۹]/.test(decodeURI(a.href)))')
        ->click('a[href$="/overview/week/2026-09-15"]')
        ->assertPathIs('/overview/week/2026-09-15')
        ->assertSee('نظرة عامة أسبوعية')
        ->assertSee('سبتمبر')
        ->assertNoJavaScriptErrors();
});

it('opens sheets on the trailing side and mirrors calendar keyboard navigation', function (string $locale): void {
    $settings = resolve(GeneralSettings::class);
    $settings->locale = $locale;
    $settings->save();

    $page = visit('/work-schedule/create')->resize(1280, 800);
    $page->assertScript('document.documentElement.dir', 'rtl')
        ->assertScript('document.querySelector("[data-slot=sheet-content]").getBoundingClientRect().left < 20');
    $page->script('async () => await Promise.all(document.querySelector("[data-slot=sheet-content]").getAnimations().map(animation => animation.finished))');
    $page->click('[data-slot="popover-trigger"]')
        ->assertAttribute('[data-slot="calendar"]', 'dir', 'rtl')
        ->assertScript('document.querySelector("[data-slot=calendar-prev-button]").getBoundingClientRect().x > document.querySelector("[data-slot=calendar-next-button]").getBoundingClientRect().x');
    $page->script('async () => await Promise.all(document.querySelector("[data-slot=popover-content]").getAnimations().map(animation => animation.finished))');

    $focusedDay = '[data-slot="calendar-cell-trigger"][tabindex="0"]';
    $date = new DateTimeImmutable($page->attribute($focusedDay, 'data-value'));
    $lastDay = $date->modify('last day of this month');
    $page->click('[data-slot="calendar-cell-trigger"][data-value="'.$lastDay->format('Y-m-d').'"]')
        ->keys($focusedDay, 'ArrowLeft')
        ->assertAttribute($focusedDay, 'data-value', $lastDay->modify('+1 day')->format('Y-m-d'))
        ->keys($focusedDay, 'ArrowRight')
        ->assertAttribute($focusedDay, 'data-value', $lastDay->format('Y-m-d'));
    $page->assertNoJavaScriptErrors();
})->with(['he_IL', 'ar_SA']);

it('isolates mixed project names and places number controls correctly', function (string $locale): void {
    $settings = resolve(GeneralSettings::class);
    $settings->locale = $locale;
    $settings->save();
    $name = 'TimeScribe / שלום / مرحبا (123)';
    Project::create(['name' => $name, 'color' => '#00c9db']);

    visit('/project')->resize(1280, 800)
        ->assertSee($name)
        ->assertScript('Array.from(document.querySelectorAll("bdi")).some(el => el.textContent.includes("TimeScribe / שלום / مرحبا (123)"))')
        ->click('a[href$="/project/create"]')
        ->assertScript('document.querySelector("[data-slot=decrement]").getBoundingClientRect().x > document.querySelector("[data-slot=increment]").getBoundingClientRect().x')
        ->click('[data-slot="increment"]')
        ->assertAttribute('input[role="spinbutton"]', 'aria-valuenow', '1')
        ->assertNoJavaScriptErrors();
})->with(['he_IL', 'ar_SA']);
