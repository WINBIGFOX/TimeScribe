<?php

namespace App\Http\Controllers;

use App\Services\TimestampService;
use Inertia\Inertia;
use Native\Laravel\Facades\MenuBar;
use Native\Laravel\Facades\Window;

class MenubarController extends Controller
{
    public function index()
    {
        \Artisan::call('menubar:refresh');

        TimestampService::ping();
        $currentType = TimestampService::getCurrentType();
        $workTime = TimestampService::getWorkTime();
        $breakTime = TimestampService::getBreakTime();

        return Inertia::render('MenuBar', [
            'currentType' => $currentType,
            'workTime' => $workTime,
            'breakTime' => $breakTime,
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
            ->title('Einstellungen')
            ->maximizable(false)
            ->minimizable(false)
            ->route('settings.index')
            ->hideDevTools()
            ->width(400)
            ->minWidth(400)
            ->maxWidth(500)
            ->minHeight(600)
            ->height(600)
            ->maxHeight(800)
            ->titleBarHidden()
            ->resizable();
    }
}
