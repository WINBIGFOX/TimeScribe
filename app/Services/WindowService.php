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
            ->maximizable(false)
            ->resizable(false);

        if (Environment::isWindows()) {
            $window->height(634)->width(713)->hideMenu();
        } else {
            $window->height(600)->width(700)->titleBarHidden();
        }
    }

    public static function openHome(bool $darkMode, string $route = 'home'): void
    {
        Window::get('home')->route($route);
        $window = Window::open('home')
            ->webPreferences([
                'devTools' => true,
            ])
            ->route($route)
            ->rememberState()
            ->maximizable(false)
            ->fullscreen(false)
            ->resizable(false)
            ->fullscreenable(false)
            ->backgroundColor($darkMode ? '#171717' : '#fafafa')
            ->showDevTools(true);

        if (Environment::isWindows()) {
            $window->height(634)->width(1083)->hideMenu();
        } else {
            $window->height(600)->width(1070)->titleBarHidden();
        }
    }

    public static function openUpdater(bool $darkMode): void
    {
        $window = Window::open('updater')
            ->webPreferences([
                'devTools' => false,
            ])
            ->route('updater.index')
            ->rememberState()
            ->maximizable(false)
            ->minimizable(false)
            ->fullscreen(false)
            ->resizable(false)
            ->fullscreenable(false)
            ->backgroundColor($darkMode ? '#171717' : '#fafafa')
            ->showDevTools(false);

        if (Environment::isWindows()) {
            $window->height(424)->width(483)->hideMenu();
        } else {
            $window->height(390)->width(470)->titleBarHidden();
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

    public static function closeUpdater(): void
    {
        Window::close('updater');
    }
}
