<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Timestamp;
use App\Models\WorkSchedule;
use App\Services\LocaleService;
use App\Services\ShortcutService;
use App\Services\TrayIconService;
use App\Services\WindowService;
use App\Settings\FlyTimerSettings;
use App\Settings\GeneralSettings;
use App\Settings\ShortcutSettings;
use Native\Desktop\Contracts\ProvidesPhpIni;
use Native\Desktop\Enums\SystemThemesEnum;
use Native\Desktop\Facades\Menu;
use Native\Desktop\Facades\MenuBar;
use Native\Desktop\Facades\Settings;
use Native\Desktop\Facades\System;
use Sentry\State\Scope;

use function Sentry\configureScope;

class NativeAppServiceProvider implements ProvidesPhpIni
{
    /**
     * Executed once the native application has been booted.
     * Use this method to open windows, register global shortcuts, etc.
     */
    public function boot(): void
    {
        new LocaleService;
        $settings = resolve(GeneralSettings::class);
        $theme = $settings->theme ?? SystemThemesEnum::SYSTEM->value;
        if ($theme !== SystemThemesEnum::SYSTEM->value) {
            System::theme(SystemThemesEnum::tryFrom($theme));
        }

        if (! $settings->id) {
            $settings->id = uuid_create();
            $settings->save();
        }

        configureScope(function (Scope $scope) use ($settings): void {
            $scope->setUser(['id' => $settings->id]);
        });

        $hasDbWorkSchedule = WorkSchedule::exists();
        $workSchedule = Settings::get('workdays');
        if ($workSchedule && ! $hasDbWorkSchedule) {
            $firstTimestamp = Timestamp::orderBy('started_at')->first();
            WorkSchedule::create([
                'sunday' => $workSchedule['sunday'] ?? 0,
                'monday' => $workSchedule['monday'] ?? 0,
                'tuesday' => $workSchedule['tuesday'] ?? 0,
                'wednesday' => $workSchedule['wednesday'] ?? 0,
                'thursday' => $workSchedule['thursday'] ?? 0,
                'friday' => $workSchedule['friday'] ?? 0,
                'saturday' => $workSchedule['saturday'] ?? 0,
                'valid_from' => $firstTimestamp ? $firstTimestamp->started_at->startOfDay() : today(),
            ]);
            Settings::forget('workdays');
            $settings->wizard_completed = true;
            $settings->save();
        } elseif ($hasDbWorkSchedule && ! $settings->wizard_completed) {
            $settings->wizard_completed = true;
            $settings->save();
        }

        if (! $settings->wizard_completed) {
            WindowService::openWelcome();
        } else {
            $flyTimerSettings = resolve(FlyTimerSettings::class);

            if ($flyTimerSettings->showWithStart) {
                WindowService::openFlyTimer();
            }
        }

        $shortcutService = resolve(ShortcutService::class);
        $shortcutSettings = resolve(ShortcutSettings::class);
        $shortcutService->refresh($shortcutSettings);

        Menu::create(
            Menu::app(),
            Menu::edit(),
            Menu::window(),
        );

        MenuBar::create()
            ->showDockIcon(false)
            ->route('menubar.index')
            ->width(300)
            ->height(280)
            ->resizable(false)
            ->showOnAllWorkspaces()
            ->icon(TrayIconService::getIcon())
            ->withContextMenu(
                Menu::make(
                    Menu::quit(),
                    Menu::separator(),
                    Menu::about(),
                )
            );
    }

    /**
     * Return an array of php.ini directives to be set.
     */
    public function phpIni(): array
    {
        return [
            'memory_limit' => '256M',
            'max_execution_time' => '0',
            'max_input_time' => '0',
        ];
    }
}
