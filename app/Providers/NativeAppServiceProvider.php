<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Str;
use Native\Laravel\Contracts\ProvidesPhpIni;
use Native\Laravel\Facades\Menu;
use Native\Laravel\Facades\MenuBar;
use Native\Laravel\Facades\Settings;

class NativeAppServiceProvider implements ProvidesPhpIni
{
    /**
     * Executed once the native application has been booted.
     * Use this method to open windows, register global shortcuts, etc.
     */
    public function boot(): void
    {
        if (! Settings::get('id')) {
            Settings::set('id', Str::uuid());
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
