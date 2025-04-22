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
            ->width(700)
            ->height(600)
            ->maximizable(false)
            ->minimizable(false)
            ->resizable(false);
    }

    public static function openHome(bool $darkMode, string $route = 'home'): void
    {
        Window::get('home')->route('settings.index');
        Window::open('home')
            ->webPreferences([
                'devTools' => false,
            ])
            ->route($route)
            ->rememberState()
            ->maximizable(false)
            ->fullscreen(false)
            ->width(1070)
            ->height(600)
            ->resizable(false)
            ->titleBarHidden()
            ->fullscreenable(false)
            ->backgroundColor($darkMode ? '#171717' : '#fafafa')
            ->showDevTools(false);
    }

    public static function closeWelcome(): void
    {
        Window::close('welcome');
    }

    public static function closeHome(): void
    {
        Window::close('home');
    }
}
