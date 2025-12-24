<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateShortcutSettingsRequest;
use App\Services\ShortcutService;
use App\Settings\ShortcutSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Inertia\Inertia;

class ShortcutController extends Controller
{
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ShortcutSettings $settings)
    {
        return Inertia::render('Settings/Shortcuts/Edit', [
            'startShortcut' => $settings->startShortcut,
            'stopShortcut' => $settings->stopShortcut,
            'pauseShortcut' => $settings->pauseShortcut,
            'overviewShortcut' => $settings->overviewShortcut,
            'projectPickerShortcut' => $settings->projectPickerShortcut,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateShortcutSettingsRequest $request, ShortcutSettings $settings, ShortcutService $shortcutService): Redirector|RedirectResponse
    {
        $data = $request->validated();

        $previousShortcuts = [
            $settings->startShortcut,
            $settings->stopShortcut,
            $settings->pauseShortcut,
            $settings->overviewShortcut,
            $settings->projectPickerShortcut,
        ];

        $settings->startShortcut = $data['startShortcut'] ?? null;
        $settings->stopShortcut = $data['stopShortcut'] ?? null;
        $settings->pauseShortcut = $data['pauseShortcut'] ?? null;
        $settings->overviewShortcut = $data['overviewShortcut'] ?? null;
        $settings->projectPickerShortcut = $data['projectPickerShortcut'] ?? null;
        $settings->save();

        $shortcutService->refresh($settings, $previousShortcuts);

        return to_route('settings.shortcuts.edit');
    }
}
