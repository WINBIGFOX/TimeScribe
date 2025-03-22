<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreWelcomeRequest;
use App\Services\WindowService;
use Inertia\Inertia;
use Native\Laravel\Facades\App;
use Native\Laravel\Facades\MenuBar;
use Native\Laravel\Facades\Settings;

class WelcomeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Welcome', [
            'workdays' => Settings::get('workdays'),
        ]);
    }

    public function update(StoreWelcomeRequest $request)
    {
        $data = $request->validated();
        if ($request->has('openAtLogin')) {
            App::openAtLogin($data['openAtLogin']);
        }
        if ($request->has('workdays')) {
            Settings::set('workdays', $data['workdays']);
        }
    }

    public function finish($openSettings = false)
    {
        Settings::set('showTimerOnUnlock', true);
        WindowService::closeWelcome();
        if ($openSettings) {
            WindowService::openSettings(false);
        } else {
            MenuBar::show();
        }
    }
}
