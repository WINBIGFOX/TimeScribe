<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\TimestampService;
use Illuminate\Support\Facades\Artisan;
use Inertia\Inertia;
use Native\Laravel\Facades\MenuBar;
use Native\Laravel\Facades\Window;

class MenubarController extends Controller
{
    public function index()
    {
        Artisan::call('menubar:refresh');

        TimestampService::ping();

        return Inertia::render('MenuBar', [
            'currentType' => TimestampService::getCurrentType(),
            'workTime' => TimestampService::getWorkTime(),
            'breakTime' => TimestampService::getBreakTime(),
        ]);
    }

    public function storeBreak()
    {
        TimestampService::startBreak();

        return redirect()->route('menubar.index');
    }

    public function storeWork()
    {
        TimestampService::startWork();

        return redirect()->route('menubar.index');
    }

    public function storeStop()
    {
        TimestampService::stop();

        MenuBar::label('');
        MenuBar::icon(public_path('IconTemplate@2x.png'));

        return redirect()->route('menubar.index');
    }

    public function openSetting(): void
    {
        Window::open('settings')
            ->rememberState()
            ->maximizable(false)
            ->minimizable(false)
            ->fullscreenable(false)
            ->route('settings.edit')
            ->showDevTools(false)
            ->width(400)
            ->minWidth(400)
            ->maxWidth(500)
            ->minHeight(600)
            ->height(600)
            ->maxHeight(800)
            ->titleBarHidden()
            ->resizable();
    }

    public function openOverview(): void
    {
        Window::open('overview')
            ->rememberState()
            ->maximizable(false)
            ->fullscreen(false)
            ->route('overview.index')
            ->width(850)
            ->height(415)
            ->titleBarHidden()
            ->resizable(false)
            ->fullscreenable(false)
            ->showDevTools(false);
    }
}
