<?php

declare(strict_types=1);

namespace App\Services;

use Native\Laravel\Facades\Window;
use Native\Laravel\Support\Environment;

class WindowService
{
    public static function openWelcome(): void
    {
        $window = Window::open('welcome')
            ->webPreferences([
                'devTools' => false,
            ])
            ->route('welcome.index')
            ->fullscreenable(false)
            ->showDevTools(false)
            ->alwaysOnTop()
            ->width(700)
            ->maximizable(false)
            ->minimizable(false)
            ->resizable(false);

        if (Environment::isWindows()) {
            $window->height(640)->hideMenu();
        } else {
            $window->height(600)->titleBarHidden();
        }
    }

    public static function openHome(bool $darkMode, string $route = 'home'): void
    {
        Window::get('home')->route($route);
        $window = Window::open('home')
            ->webPreferences([
                'devTools' => false,
            ])
            ->route($route)
            ->rememberState()
            ->maximizable(false)
            ->fullscreen(false)
            ->width(1070)
            ->resizable(false)
            ->fullscreenable(false)
            ->backgroundColor($darkMode ? '#171717' : '#fafafa')
            ->showDevTools(false);

        if (Environment::isWindows()) {
            $window->height(640)->hideMenu();
        } else {
            $window->height(600)->titleBarHidden();
        }
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
