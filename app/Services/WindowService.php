<?php

declare(strict_types=1);

namespace App\Services;

use Native\Laravel\Facades\Window;

class WindowService
{
    public static function openWelcome(): void
    {
        Window::open('welcome')
            ->webPreferences([
                'devTools' => false,
            ])
            ->route('welcome.index')
            ->fullscreenable(false)
            ->showDevTools(false)
            ->alwaysOnTop()
            ->titleBarHidden()
            ->hideDevTools()
            ->width(700)
            ->height(600)
            ->maximizable(false)
            ->minimizable(false)
            ->resizable(false);
    }

    public static function openSettings(bool $darkMode): void
    {
        Window::open('settings')
            ->webPreferences([
                'devTools' => false,
            ])
            ->route('settings.edit')
            ->rememberState()
            ->maximizable(false)
            ->minimizable(false)
            ->fullscreenable(false)
            ->showDevTools(false)
            ->width(680)
            ->minWidth(680)
            ->maxWidth(680)
            ->minHeight(650)
            ->height(650)
            ->maxHeight(800)
            ->titleBarHidden()
            ->backgroundColor($darkMode ? '#020817' : '#ffffff')
            ->resizable();
    }

    public static function openOverview(bool $darkMode): void
    {
        Window::open('overview')
            ->webPreferences([
                'devTools' => false,
            ])
            ->route('overview.index')
            ->rememberState()
            ->maximizable(false)
            ->fullscreen(false)
            ->width(850)
            ->height(415)
            ->titleBarHidden()
            ->resizable(false)
            ->fullscreenable(false)
            ->backgroundColor($darkMode ? '#020817' : '#ffffff')
            ->showDevTools(false);
    }

    public static function openAbsence(bool $darkMode): void
    {
        Window::open('absence')
            ->webPreferences([
                'devTools' => false,
            ])
            ->route('absence.index')
            ->rememberState()
            ->maximizable(false)
            ->fullscreen(false)
            ->width(1100)
            ->minWidth(1100)
            ->minHeight(600)
            ->height(800)
            ->titleBarHidden()
            ->fullscreenable(false)
            ->backgroundColor($darkMode ? '#020817' : '#ffffff')
            ->showDevTools(false);
    }

    public static function openDayEdit(string $date, bool $darkMode): void
    {
        Window::open('day-edit')
            ->webPreferences([
                'devTools' => false,
            ])
            ->rememberState()
            ->maximizable(false)
            ->fullscreen(false)
            ->route('day.edit', ['date' => $date])
            ->width(850)
            ->height(415)
            ->minWidth(700)
            ->titleBarHidden()
            ->resizable(true)
            ->backgroundColor($darkMode ? '#020817' : '#ffffff')
            ->fullscreenable(false)
            ->showDevTools(false);
    }

    public static function closeDayEdit(): void
    {
        Window::close('day-edit');
    }

    public static function closeWelcome(): void
    {
        Window::close('welcome');
    }

    public static function closeSettings(): void
    {
        Window::close('settings');
    }

    public static function closeOverview(): void
    {
        Window::close('overview');
    }

    public static function closeAbsence(): void
    {
        Window::close('absence');
    }
}
