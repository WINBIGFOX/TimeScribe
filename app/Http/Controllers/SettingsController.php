<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSettingsRequest;
use Inertia\Inertia;
use Native\Laravel\Facades\Settings;

class SettingsController extends Controller
{
    /**
     * Show the form for editing the specified resource.
     */
    public function edit()
    {
        return Inertia::render('Settings/Edit', [
            'startOnLogin' => Settings::get('startOnLogin'),
            'workdays' => Settings::get('workdays'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreSettingsRequest $request)
    {
        $data = $request->validated();

        Settings::set('startOnLogin', $data['startOnLogin']);
        Settings::set('workdays', $data['workdays']);

        return redirect()->route('settings.edit');
    }
}
