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
                'devTools' => false,
            ])
            ->route($route)
            ->rememberState()
            ->maximizable(false)
            ->fullscreen(false)
            ->resizable(false)
            ->fullscreenable(false)
            ->backgroundColor($darkMode ? '#171717' : '#fafafa')
            ->showDevTools(false);

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

    public static function openFlyTimer(): void
    {
        self::closeFlyTimer();
        Window::open('fly-timer')
            ->webPreferences([
                'devTools' => false,
            ])
            ->height(70)
            ->width(200)
            ->route('fly-timer.index')
            ->alwaysOnTop()
            ->maximizable(false)
            ->fullscreenable(false)
            ->trafficLightPosition(0, -30)
            ->resizable(false)
            ->minimizable(false)
            ->showDevTools(false)
            ->hiddenInMissionControl()
            ->skipTaskbar()
            ->rememberState()
            ->invisibleFrameless()
            ->titleBarHidden()
            ->hideMenu();
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

    public static function closeFlyTimer(): void
    {
        Window::close('fly-timer');
    }
}
