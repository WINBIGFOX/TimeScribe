<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Timestamp;
use App\Models\WorkSchedule;
use App\Services\WindowService;
use Illuminate\Support\Str;
use Native\Laravel\Contracts\ProvidesPhpIni;
use Native\Laravel\Enums\SystemThemesEnum;
use Native\Laravel\Facades\Menu;
use Native\Laravel\Facades\MenuBar;
use Native\Laravel\Facades\Settings;
use Native\Laravel\Facades\System;

class NativeAppServiceProvider implements ProvidesPhpIni
{
    /**
     * Executed once the native application has been booted.
     * Use this method to open windows, register global shortcuts, etc.
     */
    public function boot(): void
    {
        $theme = Settings::get('theme', SystemThemesEnum::SYSTEM->value);
        if ($theme !== SystemThemesEnum::SYSTEM->value) {
            System::theme(SystemThemesEnum::tryFrom($theme));
        }

        if (! Settings::get('id')) {
            Settings::set('id', Str::uuid());
        }

        $workSchedule = Settings::get('workdays');
        if ($workSchedule && ! WorkSchedule::exists()) {
            $firstTimestamp = Timestamp::orderBy('started_at')->first();
            WorkSchedule::create([
                'sunday' => $workSchedule['sunday'] ?? 0,
                'monday' => $workSchedule['monday'] ?? 0,
                'tuesday' => $workSchedule['tuesday'] ?? 0,
                'wednesday' => $workSchedule['wednesday'] ?? 0,
                'thursday' => $workSchedule['thursday'] ?? 0,
                'friday' => $workSchedule['friday'] ?? 0,
                'saturday' => $workSchedule['saturday'] ?? 0,
                'valid_from' => $firstTimestamp ? $firstTimestamp->started_at->startOfDay() : now()->startOfDay(),
            ]);
            Settings::forget('workdays');
            Settings::set('wizard_completed', true);
        }

        if (! Settings::get('wizard_completed')) {
            WindowService::openWelcome();
        }

        MenuBar::create()
            ->showDockIcon(false)
            ->route('menubar.index')
            ->width(300)
            ->height(250)
            ->resizable(false)
            ->withContextMenu(
                Menu::make(
                    Menu::quit()->label('Beenden'),
                    Menu::separator(),
                    Menu::about()->label('Über '.config('app.name')),
                )
            );
    }

    /**
     * Return an array of php.ini directives to be set.
     */
    public function phpIni(): array
    {
        return [
        ];
    }
}
